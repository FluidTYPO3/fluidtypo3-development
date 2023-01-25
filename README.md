FluidTYPO3 Development Assistant
================================

> Test runners, coding standards sniffing, local git hook scripts, utilities

[![Total Downloads](https://img.shields.io/packagist/dt/FluidTYPO3/development.svg?style=flat-square)](https://packagist.org/packages/FluidTYPO3/development)

This package defines dependencies and contains wrapper scripts for easy validations of Fluid Powered TYPO3 project repositories
conforming to the coding style and contribution guidelines. The provided `./vendor/bin/make` script can be used for local
testing and will install itself as a git `post-commit` hook that executes after commits are made.

The same scripts can be used from a continuous integration context.

To use `fluidtypo3-development` you must add it as a composer dependency:

```bash
composer require --dev fluidtypo3/development:*
```

Note that this package should only be used in local development!

Requirements
------------

* composer (scripts are tailored to be executed from composer projects only).
* Optional `Documentation/Changelog` directory if you use the `changelog` command.

Usage instructions: utility scripts
-----------------------------------

A few handy shell utilities are included with `fluidtypo3-development` to help you perform a few of the tasks associated with
maintaining and developing official FluidTYPO3 repositories. These utilities are:

**Inside this package:**

* `./vendor/bin/changelog` which can be used to automatically generate a change log for an up-coming version. The command is
  used by the `release` command as well. It takes one mandatory argument, the version number (e.g. `1.2.3`) and one additional
  and optional argument, a "since" date (e.g. `2016/03/10` for March 10th 2016). Executing the command generates a dedicated
  change log file in `Documentation/Changelog/$version.md` and updates the `CHANGELOG.md` file to record the new log file.
* `./vendor/bin/release` which releases an extension (which was staged immediately before that). Checks out the master branch,
  merges the staging branch into it, runs unit tests, generates a change log, raises the version number and commits a new
  (signed) tag. If all of this is succesful, the result is pushed to the remote and GitHub hooks deploy the extension to TER.
* `./vendor/bin/setversion` which sets a new version number (and optionally new stability) in the ext_emconf.php file. 
