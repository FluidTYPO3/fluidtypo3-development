FluidTYPO3 Development Assistant
================================

> Test runners, coding standards sniffing, local git hook scripts, utilities

[![Total Downloads](https://img.shields.io/packagist/dt/FluidTYPO3/development.svg?style=flat-square)](https://packagist.org/packages/FluidTYPO3/development)

This package defines dependencies and contains wrapper scripts for easy validations of Fluid Powered TYPO3 project repositories
conforming to the coding style and contribution guidelines. The provided `./vendor/bin/make` script can be used for local
testing and will install itself as a git `post-commit` hook that executes after commits are made.

The same script can be used from a continuous integration context.

Requirements
------------

* composer (scripts are tailored to be executed from composer projects only)
* PHP `xdebug` extension (optional, but required for the `runcoverage` command)
* PHPUnit configuration file present in `./phpunit.xml.dist`

Usage instructions: utility scripts
-----------------------------------

A few handy shell utilities are included with `fluidtypo3-development` to help you perform a few of the tasks associated with
maintaining and developing official FluidTYPO3 repositories. These utilities are:

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

To use `fluidtypo3-development` you must add it as a composer dependency:

```bash
composer require fluidtypo3/development:*
```

After this, run the assistant script once in order to confirm a working setup:

```bash
./vendor/bin/make
```

This will validate commits in history, perform code style validations and execute tests. It will also prepare your local Git
repository with a so-called `post-commit` hook script which will run the assistant script every time you make a new commit in Git,
ensuring that you do not commit code that violates the style guidelines or break tests.

You can also run the individual assistant scripts:

```bash
./vendor/bin/checkcommits
./vendor/bin/checkstyle
./vendor/bin/runtests
./vendor/bin/runcoverage
```

Coverage file outputs
---------------------

After tests have been executed you will find a Clover format code coverage log in the `build/logs/` folder - you can load this
file into an IDE like PHPStorm in order to highlight covered/uncovered lines, and you can run `./vendor/bin/coveralls` without
additional arguments to upload the coverage data to https://coveralls.io (note that Coveralls uses a repository token which
must either be exported as an ENV variable or configured in a `.coveralls.yml` file - see Coveralls documentation).
