/**
 * Core synchronization action.
 *
 * @param {@actions/github/GitHub} github
 * @param {@actions/github/Context} context
 * @param {@actions/core} core
 * @param {@actions/exec} exec
 * @param {string} version
 * @param {string} step
 * @returns {void}
 */
module.exports = async ({github, context, core, exec}, version, step) => {
  /**
   * Log a debug message.
   *
   * core.debug does not recursively resolve all objects so instead we use the
   * console.log which behalfs like expected.
   *
   * @param {...any} data
   * @returns {void}
   */
  async function debug(
    ...data
  ) {
    if (!core.isDebug()) {
      return
    }

    console.log(...data)
  }

  /**
   * Executes a command and throws in case of a failure.
   *
   * @param {string} commandLine
   * @param {string[]} args
   * @param {ExecOptions} options
   * @returns {void}
   */
  async function safeExec(
    commandLine,
    args,
    options
  ) {
    const exitCode = await exec.exec(commandLine, args, options)

    if (exitCode > 0) {
      throw new Error(`"${commandLine}" terminated with exit code ${exitCode}.`)
    }
  }

  /**
   * Commits all changes with the given message.
   *
   * @param {string} message
   * @returns {void}
   */
  async function commitChange(
    message
  ) {
    await safeExec(`git commit -a -m "${message}"`)
  }

  /**
   * Lookups a pending pull request and returns its number or 0 if
   * not found.
   *
   * @param {string} branch
   * @returns {number}
   */
  async function getPendingPullRequest(
    branch
  ) {
    const response = await github.rest.pulls.list({
      owner: context.repo.owner,
      repo: context.repo.repo,
      head: `${context.repo.owner}:${branch}`,
    })

    debug(response)

    if (response.status !== 200) {
      throw new Error(`List pull requests failed (${response.status}).`)
    }

    var pullRequestNo = 0

    if (response.data.length > 0) {
      pullRequestNo = response.data[0].number
      core.notice(`Pending pull request ${pullRequestNo} found.`)
    }

    return pullRequestNo
  }

  /**
   * Returns the default branch of the repository.
   *
   * @returns {string}
   */
  async function getDefaultBranch() {
    if (context.payload.repository.default_branch !== undefined) {
      return context.payload.repository.default_branch
    }

    const response = await github.rest.repos.get({
      owner: context.repo.owner,
      repo: context.repo.repo,
    })

    debug(response)

    if (response.status !== 200) {
      throw new Error(`Get repository failed (${response.status}).`)
    }

    return response.data.default_branch
  }

  /**
   * Creates a pull request for the given branch and returns its number.
   *
   * @param {string} branch
   * @param {string} title
   * @returns {number}
   */
  async function createPullRequest(
    branch,
    title
  ) {
    const defaultBranch = await getDefaultBranch()

    debug(context.repo.owner)
    debug(context.repo.repo)
    debug(branch)
    debug(version)
    debug(step)
    debug(defaultBranch)

    const response = await github.rest.pulls.create({
      owner: context.repo.owner,
      repo: context.repo.repo,
      title: title,
      head: branch,
      base: defaultBranch,
      body: ``,
      maintainer_can_modify: true,
      draft: true,
    })

    debug(response)

    if (response.status !== 201) {
      throw new Error(`Create pull request failed (${response.status}).`)
    }

    core.notice(`Pull request ${response.data.number} created.`)

    return response.data.number
  }

  /**
   * Dumps the context if debug mode is enabled.
   *
   * @returns {void}
   */
  async function dumpContext() {
    if (!core.isDebug()) {
      return
    }

    core.startGroup(`Dump context attributes`)

    try {
      console.log(context)
    } finally {
      core.endGroup()
    }
  }

  /**
   * Setups the repository to be able to commit and switches to the
   * given branch.
   *
   * @param {string} branch
   * @returns {void}
   */
  async function setupRepository(
    branch
  ) {
    core.startGroup(`Setup repository`)

    try {
      await safeExec(`git config user.name github-actions`)
      await safeExec(`git config user.email github-actions@github.com`)
      await safeExec(`git branch ${branch}`)
      await safeExec(`git switch ${branch}`)
    } finally {
      core.endGroup()
    }
  }

  /**
   * Create release or version commit.
   *
   * @param {string} version
   * @param {string} step
   * @returns {string}
   */
   async function createCommit(
    version,
    step
   ) {
    core.startGroup('Create release commit')

    try {
      await safeExec(`composer set-version ${version}`)

      let commitMessage = ''
      if (step === 'version') {
        commitMessage = `[TASK] Set TYPO3 Coding Standards version to ${version}`
      } else {
        commitMessage = `[RELEASE] Release of TYPO3 Coding Standards ${version}`
      }

      await commitChange(commitMessage)

      return commitMessage
    } finally {
      core.endGroup()
    }
  }

  /**
   * Push changes to the provided branch.
   *
   * @param {string} branch
   * @returns {void}
   */
  async function pushChanges(
    branch
  ) {
    core.startGroup(`Push changes`)

    try {
      await safeExec(`git push -f origin ${branch}`)
    } finally {
      core.endGroup()
    }
  }

  /**
   * Create a pull request or update an existing one.
   *
   * @param {string} branch
   * @param {string} title
   * @returns {number}
   */
  async function createOrUpdatePullRequest(
    branch,
    title
  ) {
    core.startGroup(`Create pull request`)

    try {
      let pullRequestNo = await getPendingPullRequest(branch)

      if (pullRequestNo !== 0) {
        return pullRequestNo
      }

      pullRequestNo = await createPullRequest(branch, title)

      return pullRequestNo
    } finally {
      core.endGroup()
    }
  }

  try {
    dumpContext()

    let pullRequestBranch = ''
    if (step === 'version') {
      pullRequestBranch = 'release/version'
    } else {
      pullRequestBranch = 'release/release'
    }

    await setupRepository(pullRequestBranch)

    const commitMessage = await createCommit(version, step)

    let pullRequestNo = 0
    await pushChanges(pullRequestBranch)
    pullRequestNo = await createOrUpdatePullRequest(pullRequestBranch, commitMessage)

    core.setOutput('pull-request', pullRequestNo)
  } catch (err) {
    core.setFailed(`Action failed with error ${err}`)
  }
}
