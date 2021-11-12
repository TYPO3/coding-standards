module.exports = async ({github, context, core, exec}) => {
  // Nested functions, core.startGroup() MUST NOT be used

  function debug(...data) {
    if (!core.isDebug()) {
      return
    }

    console.log(...data)
  }

  async function getCoreFileContent(
    path
  ) {
    const response = await github.rest.repos.getContent({
      owner: 'TYPO3',
      repo: 'typo3',
      path: path,
    })

    debug(response)

    if (response.status !== 200) {
      throw new Error(`Get content failed (${response.status}).`)
    }

    core.info(`${path} downloaded from TYPO3 Core.`)

    return Buffer.from(response.data.content, 'base64').toString('utf8')
  }

  function getRules(
    content,
    regex
  ) {
    const coreRulesMatches = regex.exec(content)

    debug(`PHP Coding Standards Fixer rules extracted:\n\n${coreRulesMatches[1]}`)

    return coreRulesMatches[1]
  }

  function replaceRules(
    path,
    regex,
    coreRules
  ) {
    const fs = require('fs')
    const currentContent = fs.readFileSync(path, 'utf8')

    const newContent = currentContent.replace(regex, '$1' + coreRules + '$2')

    if (currentContent === newContent) {
      core.info('PHP Coding Standards Fixer rules have not changed.')
      return false
    }

    fs.writeFileSync(path, newContent)
    core.notice('PHP Coding Standards Fixer rules have changed.')

    return true
  }

  function replaceFile(
    path,
    newContent
  ) {
    var fs = require('fs')
    var currentContent = fs.readFileSync(path, 'utf8')

    if (currentContent === newContent) {
      core.info(`${path} has not changed.`)
      return false
    }

    fs.writeFileSync(path, newContent)
    core.notice(`${path} has changed.`)

    return true
  }

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

  async function commitChange(
    message
  ) {
    safeExec(`git commit -a -m "${message}"`)
  }

  async function pushChanges(
    branch
  ) {
    safeExec(`git push -f origin ${branch}`)
  }

  async function getPendingPullRequest(
    branch
  ) {
    const response = await github.rest.pulls.list({
      owner: context.repo.owner,
      repo: context.repo.repo,
      head: `${context.repo.owner}:${branch}`,
    })

    debug(response)
    core.debug(response)

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

  async function createPullRequest(
    branch
  ) {
    const response = await github.rest.pulls.create({
      owner: context.repo.owner,
      repo: context.repo.repo,
      title: "[TASK] Sync files with the latest TYPO3 Core version",
      head: branch,
      base: await getDefaultBranch(),
      body: `Test body.`,
    })

    debug(response)

    if (response.status !== 201) {
      throw new Error(`Create pull request failed (${response.status}).`)
    }

    core.notice(`Pull request ${response.data.number} created.`)

    return response.data.number
  }


  // Top level functions, core.startGroup() MUST be used

  function dumpContext() {
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

  async function setupRepository(
    branch
  ) {
    core.startGroup(`Setup repository`)

    try {
      safeExec(`git config user.name github-actions`)
      safeExec(`git config user.email github-actions@github.com`)
      safeExec(`git branch ${branch}`)
      safeExec(`git switch ${branch}`)
    } finally {
      core.endGroup()
    }
  }

  async function syncPhpCsFixerRules() {
    core.startGroup('Sync PHP Coding Standards Fixer rules with the latest TYPO3 Core version')

    try {
      const coreCsFixerConfig = await getCoreFileContent('Build/php-cs-fixer.php')
      const coreCsFixerRules = getRules(coreCsFixerConfig, /setRules\(\[[\n\r]([\s\S]+) {4}\]\)[\n\r]/g)
      const localFile = 'src/CsFixerConfig.php'
      const rulesReplaced = replaceRules(localFile, /([\s\S]+\$typo3Rules = \[[\n\r])[^\]][^;]*( {4}\];[\s\S]+)/g, coreCsFixerRules)

      if (rulesReplaced) {
        commitChange(`[TASK] Sync ${localFile} with the latest TYPO3 Core version`)
      }

      return rulesReplaced
    } finally {
      core.endGroup()
    }
  }

  async function syncEditorconfig() {
    core.startGroup('Sync editorconfig with the latest TYPO3 Core version')

    try {
      const coreEditorconfig = await getCoreFileContent('.editorconfig')
      const localFile = '.editorconfig'
      const fileReplaced = replaceFile(localFile, coreEditorconfig)

      if (fileReplaced) {
        commitChange(`[TASK] Sync ${localFile} with the latest TYPO3 Core version`)
      }

      return fileReplaced
    } finally {
      core.endGroup()
    }
  }

  async function handleChanges(
    hasChanges,
    branch
  ) {
    core.startGroup(`Handle changes`)

    try {
      if (!hasChanges) {
        core.info('No changes found.')
        return 0
      }

      pushChanges(branch)

      const pullRequestNo = await getPendingPullRequest(branch)

      if (pullRequestNo !== 0) {
        return pullRequestNo
      }

      return await createPullRequest(branch)
    } finally {
      core.endGroup()
    }
  }

  try {
    dumpContext()

    const branch = 'task/core-update'
    setupRepository(branch)

    const phpCsFixer = await syncPhpCsFixerRules()
    core.setOutput('php-cs-fixer', phpCsFixer)

    const editorconfig = await syncEditorconfig()
    core.setOutput('editorconfig', editorconfig)

    const pullRequestNo = await handleChanges(phpCsFixer || editorconfig, branch)

    core.setOutput('pull-request', pullRequestNo)
  } catch (err) {
    core.setFailed(`Action failed with error ${err}`)
  }
}
