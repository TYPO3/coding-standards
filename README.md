# TYPO3 Coding Standards Package

[![Continuous Integration (CI)](https://github.com/TYPO3/coding-standards/actions/workflows/continuous-integration.yml/badge.svg)](https://github.com/TYPO3/coding-standards/actions/workflows/continuous-integration.yml)
[![Core Synchronization](https://github.com/TYPO3/coding-standards/actions/workflows/core-synchronization.yml/badge.svg)](https://github.com/TYPO3/coding-standards/actions/workflows/core-synchronization.yml)

You know the feeling: You work on your own extension, and then contribute to
TYPO3, and TYPO3 delivers all the nice things to check for proper coding
standards.

Well, same happens to all of us! Luckily, TYPO3 has this configuration in Core,
and it's now available separately - ready to plug-and-play for you!

It does not matter if you're an extension developer, or a TYPO3 contributor, or
working on your TYPO3 project.

## Installation

As this is a composer package, execute `composer req --dev typo3/coding-standards`
in your composer project.

## What's in the package?

The coding guidelines that are used in TYPO3 Core development. Instead of
putting this information in our main repository, it should be helpful to apply
certain rules to other projects as well. TYPO3 is more than just TYPO3 Core!

### PHP-CS-Fixer rules

Making sure your PHP files apply to the same rules.

### .editorconfig

If you work on a team, and you use different IDE settings, `.editorconfig`
helps you to have the same settings across all editors. It does not matter if
it is VS-Code, vim or PhpStorm.

### Setting up the TYPO3 rulesets as boilerplate

Our coding standards file can set this up for you. Run

```bash
composer exec typo3-coding-standards project
```

or

```bash
composer exec typo3-coding-standards extension
```

or if you want to update the rules, add `-f` option to the end.

Have a look at the newly created files in your root folder:

* .php-cs-fixer.php
* .editorconfig

For projects, the folder `src/extensions` is configured by default, but you can
accommodate where your extensions or PHP code resides in. For extensions,
PHP-CS-Fixer scans the whole base directory.

In addition, you can configure your PHP-CS-Fixer cache file folder and other
configurations just like with PHP-CS-Fixer.

You can decide to commit them to your Git repository, which is the recommended
way.

## Executing the PHP-CS-Fixer

Once you've followed the step above, running PHP CS Fixer works like this:

```bash
composer exec php-cs-fixer
```

Leave a note on how you set it up on GitHub Actions or GitLab CI/CD so this
document can be even more helpful for everybody.

## What's next?

We'd love to ship out license headers for all PHP files, however there are
certain limitations (namespace must be underneath the license headers), which
why this option is not enabled by default.

## A note about our standards

### PHP Coding Guidelines

TYPO3's coding guidelines have evolved over time. And we are happy to have
PHP-FIG and PSR-1/PSR-2 and PSR-12. That's why we're committed to following
these guidelines.

However, there are some more rules that we think are good:

* Remove leading slashes in use clauses.
* PHP single-line arrays should not have trailing comma.
* Single-line whitespace before closing semicolon are prohibited.
* Remove unused use statements in the PHP source code
* Ensure Concatenation to have at least one whitespace around
* Remove trailing whitespace at the end of blank lines.

## Maintaining and Development of this package

This package is not meant to be updated regularly, since talking about coding
guidelines takes a lot of time, and is usual a matter of taste. Nonetheless,
you can always open up an issue if you feel like we're missing out on something.

## License & Thanks

This package is available under the MIT license, since it relies heavily on the
PHP-CS-Fixer code.

In addition, I would like to thank the TYPO3 Core Team that kickstarted this
set of rules in 2015, and to the creators and maintainers of PHP-CS-Fixer
package.

Benni Mack, TYPO3 Project Lead
