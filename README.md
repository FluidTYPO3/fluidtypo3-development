FluidTYPO3 Development Assistant
================================

> Test runners, coding standards sniffing, local git hook scripts

[![Total Downloads](https://img.shields.io/packagist/dt/FluidTYPO3/development.svg?style=flat-square)](https://packagist.org/packages/FluidTYPO3/development)

This package defines dependencies and contains wrapper scripts for easy validations of Fluid Powered TYPO3 project repositories
conforming to the coding style and contribution guidelines. The provided `./vendor/bin/make` script can be used for local
testing and will install itself as a git `post-commit` hook that executes after commits are made.

The same script can be used from a continuous integration context.

Requirements
------------

* composer (scripts are tailored to be executed from composer projects only)
* PHP `xdebug` extension
* PHPUnit configuration file present in `./phpunit.xml.dist`

Usage instructions
------------------

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
```

Outputs
-------

After tests have been executed you will find a Clover format code coverage log in the `build/logs/` folder - you can load this
file into an IDE like PHPStorm in order to highlight covered/uncovered lines, and you can run `./vendor/bin/coveralls` without
additional arguments to upload the coverage data to https://coveralls.io (note that Coveralls uses a repository token which
must either be exported as an ENV variable or configured in a `.coveralls.yml` file - see Coveralls documentation).
