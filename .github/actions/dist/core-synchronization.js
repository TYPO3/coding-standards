/**
 * Core synchronization action.
 *
 * @param {@actions/github/GitHub} github
 * @param {@actions/github/Context} context
 * @param {@actions/core} core
 * @param {@actions/exec} exec
 * @param {string} typo3CoreOwner
 * @param {string} typo3CoreRepo
 * @returns {void}
 */
module.exports = async ({github, context, core, exec}, pullRequestBranch, typo3CoreOwner, typo3CoreRepo) => {
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
   * Download a file from the core.
   *
   * @param {string} path
   * @returns {string}
   */
  async function getCoreFileContent(
    path
  ) {
    const response = await github.rest.repos.getContent({
      owner: typo3CoreOwner,
      repo: typo3CoreRepo,
      path: path,
    })

    debug(response)

    if (response.status !== 200) {
      throw new Error(`Get content failed (${response.status}).`)
    }

    core.info(`${path} downloaded from TYPO3 Core.`)

    return Buffer.from(response.data.content, 'base64').toString('utf8')
  }

  /**
   * Extract rules from core's php-cs-fixer config.
   *
   * @param {string} content
   * @param {RegExp} regex
   * @returns {string}
   */
  async function getRules(
    content,
    regex
  ) {
    const coreRulesMatches = regex.exec(content)

    if (coreRulesMatches === null) {
      debug(content)
      throw new Error('Error while extracting rules.')
    }

    debug(`PHP Coding Standards Fixer rules extracted:\n\n${coreRulesMatches[1]}`)

    return coreRulesMatches[1]
  }

  /**
   * Replaces the rules in a local file by the provided ones.
   *
   * @param {string} path
   * @param {RegExp} regex
   * @param {string} coreRules
   * @returns {boolean}
   */
  async function replaceRules(
    path,
    regex,
    coreRules
  ) {
    const fs = require('fs')
    const currentContent = await fs.readFileSync(path, 'utf8')

    if (regex.exec(currentContent) === null) {
      debug(currentContent)
      throw new Error('Error while replacing rules.')
    }

    const newContent = currentContent.replace(regex, '$1' + coreRules + '$2')

    if (currentContent === newContent) {
      core.info('PHP Coding Standards Fixer rules have not changed.')
      return false
    }

    await fs.writeFileSync(path, newContent)
    core.notice('PHP Coding Standards Fixer rules have changed.')

    return true
  }

  /**
   * Replaces the content of a file with a new one.
   *
   * @param {string} path
   * @param {string} newContent
   * @returns {boolean}
   */
  async function replaceFile(
    path,
    newContent
  ) {
    var fs = require('fs')
    var currentContent = await fs.readFileSync(path, 'utf8')

    if (currentContent === newContent) {
      core.info(`${path} has not changed.`)
      return false
    }

    await fs.writeFileSync(path, newContent)
    core.notice(`${path} has changed.`)

    return true
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
   * @returns {number}
   */
  async function createPullRequest(
    branch
  ) {
    const response = await github.rest.pulls.create({
      owner: context.repo.owner,
      repo: context.repo.repo,
      title: "[TASK] Sync files with the latest TYPO3 Core version",
      head: branch,
      base: getDefaultBranch(),
      body: `Test body.`,
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
   * Synchronizes the php-cs-fixer rules with the core.
   *
   * @returns {boolean}
   */
  async function syncPhpCsFixerRules() {
    core.startGroup('Sync PHP Coding Standards Fixer rules with the latest TYPO3 Core version')

    try {
      const coreCsFixerConfig = await getCoreFileContent('Build/php-cs-fixer.php')
      const coreCsFixerRules = await getRules(coreCsFixerConfig, /setRules\(\[[\n\r]([\s\S]+) {4}\]\)[\n\r]/g)
      const localFile = 'src/CsFixerConfig.php'
      const rulesReplaced = await replaceRules(localFile, /([\s\S]+\$typo3Rules = \[[\n\r])[^\]][^;]*( {4}\];[\s\S]+)/g, coreCsFixerRules)

      if (rulesReplaced) {
        await commitChange(`[TASK] Sync ${localFile} with the latest TYPO3 Core version`)
      }

      return rulesReplaced
    } finally {
      core.endGroup()
    }
  }

  /**
   * Synchronizes the editorconfig with the core.
   *
   * @returns {boolean}
   */
  async function syncEditorconfig() {
    core.startGroup('Sync editorconfig with the latest TYPO3 Core version')

    try {
      const coreEditorconfig = await getCoreFileContent('.editorconfig')
      const localFile = '.editorconfig'
      const fileReplaced = await replaceFile(localFile, coreEditorconfig)

      if (fileReplaced) {
        await commitChange(`[TASK] Sync ${localFile} with the latest TYPO3 Core version`)
      }

      return fileReplaced
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
   * @returns {number}
   */
   async function createOrUpdatePullRequest(
    branch
  ) {
    core.startGroup(`Create pull request`)

    try {
      let pullRequestNo = await getPendingPullRequest(branch)

      if (pullRequestNo !== 0) {
        return pullRequestNo
      }

      pullRequestNo = await createPullRequest(branch)

      return pullRequestNo
    } finally {
      core.endGroup()
    }
  }

  try {
    dumpContext()

    await setupRepository(pullRequestBranch)

    const phpCsFixer = await syncPhpCsFixerRules()
    const editorconfig = await syncEditorconfig()

    let pullRequestNo = 0
    if (phpCsFixer || editorconfig) {
      await pushChanges(pullRequestBranch)
      pullRequestNo = await createOrUpdatePullRequest(pullRequestBranch)
    }

    core.setOutput('php-cs-fixer', phpCsFixer)
    core.setOutput('editorconfig', editorconfig)
    core.setOutput('pull-request', pullRequestNo)
  } catch (err) {
    core.setFailed(`Action failed with error ${err}`)
  }
}
