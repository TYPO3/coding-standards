# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

[//]: # (start of releases)

## [v0.5.5](https://github.com/TYPO3/coding-standards/releases/tag/v0.5.5) - 2022-08-12

Removed breaking CS-Fixer rules.

### What's Changed Since 0.5.4

#### 🐞 Bug Fixes

* [BUGFIX] Remove modernize_strpos by @gilbertsoft in <https://github.com/TYPO3/coding-standards/pull/49>

#### 🧰 Maintenance

* [TASK] Expose default cs-fixer configuration by @gilbertsoft in <https://github.com/TYPO3/coding-standards/pull/47>
* [TASK] Remove binary from coverage by @gilbertsoft in <https://github.com/TYPO3/coding-standards/pull/48>
* [TASK] Reduce PHPStan tests by @gilbertsoft in <https://github.com/TYPO3/coding-standards/pull/50>
* [TASK] Enable treatPhpDocTypesAsCertain by @gilbertsoft in <https://github.com/TYPO3/coding-standards/pull/51>

**Full Changelog**: <https://github.com/TYPO3/coding-standards/compare/v0.5.4...v0.5.5>

## [v0.5.4](https://github.com/TYPO3/coding-standards/releases/tag/v0.5.4) - 2022-08-09

New CS-Fixer rules.

### What's Changed Since 0.5.3

#### 🐞 Bug Fixes

* [BUGFIX] Return self to properly support code completion by @gilbertsoft in <https://github.com/TYPO3/coding-standards/pull/36>

#### 🧰 Maintenance

* [TASK] Sync files with the latest TYPO3 Core version by @github-actions in <https://github.com/TYPO3/coding-standards/pull/34>
* [TASK] Allow any composer tool by @gilbertsoft in <https://github.com/TYPO3/coding-standards/pull/37>
* [TASK] Update copyright by @gilbertsoft in <https://github.com/TYPO3/coding-standards/pull/38>
* [TASK] Update DDEV and debug config by @gilbertsoft in <https://github.com/TYPO3/coding-standards/pull/40>
* [TASK] Optimize CI and add coverage report by @gilbertsoft in <https://github.com/TYPO3/coding-standards/pull/41>
* [TASK] Rename branch-alias from master to main by @gilbertsoft in <https://github.com/TYPO3/coding-standards/pull/43>
* [TASK] Fix nightly CI matrix by @gilbertsoft in <https://github.com/TYPO3/coding-standards/pull/45>

#### 📖 Documentation

* [DOCS] Clarify the usage of the `-f` option by @gilbertsoft in <https://github.com/TYPO3/coding-standards/pull/42>

### New Contributors Since 0.5.3

* @github-actions made their first contribution in <https://github.com/TYPO3/coding-standards/pull/34>

**Full Changelog**: <https://github.com/TYPO3/coding-standards/compare/v0.5.3...v0.5.4>

## [v0.5.3](https://github.com/TYPO3/coding-standards/releases/tag/v0.5.3) - 2022-07-01

Avoid yoda-style conditions in PHP.

### What's Changed Since 0.5.2

#### 🐞 Bug Fixes

* [BUGFIX] Avoid yoda-style conditions in PHP by @eliashaeussler in <https://github.com/TYPO3/coding-standards/pull/31>

#### 🧰 Maintenance

* [TASK] Disable daily core synchronization by @gilbertsoft in <https://github.com/TYPO3/coding-standards/pull/30>

### New Contributors Since 0.5.2

* @eliashaeussler made their first contribution in <https://github.com/TYPO3/coding-standards/pull/31>

**Full Changelog**: <https://github.com/TYPO3/coding-standards/compare/v0.5.2...v0.5.3>

## [v0.5.2](https://github.com/TYPO3/coding-standards/releases/tag/v0.5.2) - 2022-04-09

Enhanced .editorconfig.

### What's Changed Since 0.5.1

#### 🧰 Maintenance

* [TASK] Use recommended naming for config by @gilbertsoft in <https://github.com/TYPO3/coding-standards/pull/21>
* [TASK] Sync .editorconfig template by @gilbertsoft in <https://github.com/TYPO3/coding-standards/pull/29>

**Full Changelog**: <<https://github.com/TYPO3/coding-standards/compare/v0.5.1...v>

## [v0.5.1](https://github.com/TYPO3/coding-standards/releases/tag/v0.5.1) - 2022-04-05

Enhanced .editorconfig.

### What's Changed Since 0.5.0

#### 🧰 Maintenance

* [TASK] Complete composer manifest by @gilbertsoft in <https://github.com/TYPO3/coding-standards/pull/22>
* [TASK] Add PHP 8.1 and nightly to CI by @gilbertsoft in <https://github.com/TYPO3/coding-standards/pull/26>
* [TASK] Sync .editorconfig with the latest TYPO3 Core version by @gilbertsoft
  in <https://github.com/TYPO3/coding-standards/pull/28>

#### 📖 Documentation

* [DOCS] Update README.md with dev hints by @gilbertsoft in <https://github.com/TYPO3/coding-standards/pull/20>
* [DOCS] Move warning about core synchronization by @gilbertsoft in <https://github.com/TYPO3/coding-standards/pull/23>
* [DOCS] Fix wrong folder by @gilbertsoft in <https://github.com/TYPO3/coding-standards/pull/24>
* [DOCS] Add YouTube link to README.md by @marble in <https://github.com/TYPO3/coding-standards/pull/25>

### New Contributors Since 0.5.0

* @marble made their first contribution in <https://github.com/TYPO3/coding-standards/pull/25>

**Full Changelog**: <https://github.com/TYPO3/coding-standards/compare/v0.5.0...v0.5.1>

## [v0.5.0](https://github.com/TYPO3/coding-standards/releases/tag/v0.5.0) - 2021-11-16

This version now supports PHP 8.1 and is synchronized with the core editorconfig.
Also, the php-cs-fixer template for projects has been adjusted to scan `src`
instead of `src/extensions`. Finally, the setup script has been renamed to
`typo3-coding-standards` and is provided as a Composer binary. See README.md for
more information on how to use it.

### What's Changed Since 0.4.0

#### 🚀 Features

* [FEATURE] Provide setup binary through composer by @gilbertsoft in <https://github.com/TYPO3/coding-standards/pull/10>

#### 🐞 Bug Fixes

* [BUGFIX] Bump minimal requirements to support PHP 8.1 by @gilbertsoft in <https://github.com/TYPO3/coding-standards/pull/19>

#### 🧰 Maintenance

* [TASK] Introduce CI workflow by @gilbertsoft in <https://github.com/TYPO3/coding-standards/pull/11>
* [TASK] Update php-cs-fixer project defaults by @gilbertsoft in <https://github.com/TYPO3/coding-standards/pull/18>
* [TASK] Automate updates from core by @gilbertsoft in <https://github.com/TYPO3/coding-standards/pull/17>

**Full Changelog**: <https://github.com/TYPO3/coding-standards/compare/v0.4.0...v0.5.0>

## [v0.4.0](https://github.com/TYPO3/coding-standards/releases/tag/v0.4.0) - 2021-10-22

This release supports PHP-CS-Fixer 3 and creates `.php-cs-fixer.php` now for configuration.

### What's Changed Since 0.3.0

#### 🐞 Bug Fixes

* [BUGFIX] Optimize search for autoloader by @gilbertsoft in <https://github.com/TYPO3/coding-standards/pull/6>
* [BUGFIX] Search for autoloader by @gilbertsoft in <https://github.com/TYPO3/coding-standards/pull/9>

#### 🧰 Maintenance

* [TASK] Synchronize rules with TYPO3 Core by @gilbertsoft in <https://github.com/TYPO3/coding-standards/pull/7>
* [TASK] Apply MD styles by @gilbertsoft in <https://github.com/TYPO3/coding-standards/pull/8>
* [TASK] Upgrade to friendsofphp/php-cs-fixer 3.0 by @simonschaufi in <https://github.com/TYPO3/coding-standards/pull/5>

### New Contributors Since 0.3.0

* @simonschaufi made their first contribution in <https://github.com/TYPO3/coding-standards/pull/5>
* @gilbertsoft made their first contribution in <https://github.com/TYPO3/coding-standards/pull/6>

**Full Changelog**: <https://github.com/TYPO3/coding-standards/compare/v0.3.0...v0.4.0>

## [v0.3.0](https://github.com/TYPO3/coding-standards/releases/tag/v0.3.0) - 2021-03-04

Add compatibility for PHP 8.0.

### What's Changed Since 0.2.0

#### 🧰 Maintenance

* [TASK] Remove warning and error from .editorconfig.dist by @brotkrueml in <https://github.com/TYPO3/coding-standards/pull/2>
* [TASK] Allow PHP 8 by @lolli42 in <https://github.com/TYPO3/coding-standards/pull/4>

### New Contributors Since 0.2.0

* @brotkrueml made their first contribution in <https://github.com/TYPO3/coding-standards/pull/2>
* @lolli42 made their first contribution in <https://github.com/TYPO3/coding-standards/pull/4>

**Full Changelog**: <https://github.com/TYPO3/coding-standards/compare/v0.2.0...v0.3.0>

## [v0.2.0](https://github.com/TYPO3/coding-standards/releases/tag/v0.2.0) - 2020-07-13

Adapt TYPO3 Core guidelines & PSR-12 support.

### What's Changed Since 0.1.0

#### 🧰 Maintenance

* [TASK] Align coding guidelines with latest TYPO3 Core master by @bmack

**Full Changelog**: <https://github.com/TYPO3/coding-standards/compare/v0.1.0...v0.2.0>

## [v0.1.0](https://github.com/TYPO3/coding-standards/releases/tag/v0.1.0) - 2019-11-23

First release with initial release.

### What's Changed

#### 🚀 Features

* [FEATURE] Initial commit by @bmack

#### 🧰 Maintenance

* [TASK] Remove ESlinting option for now by @bmack
