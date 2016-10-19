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

* composer (scripts are tailored to be executed from composer projects only)
* PHP `xdebug` extension (optional, but required for generating code coverage)
* PHPUnit configuration file present in `./phpunit.xml.dist`
* Optional `Documentation/Changelog` directory if you use the `changelog` command

Usage instructions: utility scripts
-----------------------------------

A few handy shell utilities are included with `fluidtypo3-development` to help you perform a few of the tasks associated with
maintaining and developing official FluidTYPO3 repositories. These utilities are:

**Inside this package:**

* `./vendor/bin/changelog` which can be used to automatically generate a change log for an up-coming version. The command is
  used by the `release` command as well. It takes one mandatory argument, the version number (e.g. `1.2.3`) and one additional
  and optional argument, a "since" date (e.g. `2016/03/10` for March 10th 2016). Executing the command generates a dedicated
  change log file in `Documentation/Changelog/$version.md` and updates the `CHANGELOG.md` file to record the new log file.
* `./vendor/bin/stage` which performs the standard staging steps for FluidTYPO3 extensions - makes sure the working copy is
  clean, checks out staging and merges development, runs unit tests and if succesful, pushes the result to the remote for
  continuous integration to do its work.
* `./vendor/bin/release` which releases an extension (which was staged immediately before that). Checks out the master branch,
  merges the staging branch into it, runs unit tests, generates a change log, raises the version number and commits a new
  (signed) tag. If all of this is succesful, the result is pushed to the remote and GitHub hooks deploy the extension to TER.

**From the `namelesscoder/typo3-repository-client` package:**

* `./vendor/bin/setversion` which can be used to consistently update the version number when the repository is a TYPO3
  extension. The script updates the `composer.json` and `ext_emconf.php` files with a new version number and must be used
  for example as: `./vendor/bin/setversion 1.2.3 stable` - which sets the version number to `1.2.3` and stability to `stable`.
* `./vendor/bin/upload` which is capable of uploading the current state of the extension to TER. Use this with great care;
  the utility is included in case the automatic TER uploads fail. Example: `./vendor/bin/upload . $username $password "Comment"`
  where `$username` and `$password` are your/our official TYPO3 account credentials with access to the extension on TER.

In addition, all third-party commands such as `phpunit` and `phpcs` can be manually executed from the `./vendor/bin/` directory.
For instructions on using those commands (with the exception of the two scripts above), run the command with the `-h` argument,
e.g. `./vendor/bin/phpunit -h`.

Usage instructions: testing and validation
------------------------------------------

Hooks are provided which prepare and validate commit messages that you make. You can install these by copying them into
the `.git/hooks/` folder. When this package is added as a dependency, you normally find the hooks we deliver in the path
`./vendor/fluidtypo3/development/hooks` - simply copy them from here to the applied hooks folder.

Every FluidTYPO3 extension is testable using a completely vanilla phpunit command:

```
./vendor/bin/phpunit
```

Nothing else required. The command is the default phpunit CLI command and supports all the usual switches. This command is
also what gets executed as part of the `stage` and `release` CLI commands to ensure consistency before pushing anything.
