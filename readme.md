# UserForms

UserForms enables CMS users to create dynamic forms via a drag and drop interface
and without getting involved in any PHP code.

[![Build Status](https://travis-ci.org/silverstripe/silverstripe-userforms.svg?branch=master)](https://travis-ci.org/silverstripe/silverstripe-userforms)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/silverstripe/silverstripe-userforms/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/silverstripe/silverstripe-userforms/?branch=master)
[![codecov](https://codecov.io/gh/silverstripe/silverstripe-userforms/branch/master/graph/badge.svg)](https://codecov.io/gh/silverstripe/silverstripe-userforms)
[![Version](http://img.shields.io/packagist/v/silverstripe/userforms.svg?style=flat)](https://packagist.org/packages/silverstripe/silverstripe-userforms)
[![License](http://img.shields.io/packagist/l/silverstripe/userforms.svg?style=flat)](LICENSE.md)

## Requirements

See the "require" section of [composer.json](https://github.com/silverstripe/silverstripe-userforms/blob/master/composer.json)

## Features

*  Construct a form using all major form fields (text, email, dropdown, radio, checkbox..)
*  Ability to extend userforms from other modules to provide extra fields.
*  Ability to email multiple people the form submission
*  View submitted submissions and export them to CSV
*  Define custom error messages and validation settings
*  Optionally display and hide fields using javascript based on users input
*  Displays a confirmation message when navigating away from a partially completed form.

## Installation

```sh
$ composer require silverstripe/userforms
```

You'll also need to run `dev/build`. You should see a new page type in the CMS 'User Defined Form'. This has a new 'Form' tab which has your form builder.

## Documentation

 * [Index](docs/en/index.md)
 * [Installation instructions](docs/en/installation.md)
 * [Troubleshooting](docs/en/troubleshooting.md)
 * [User Documentation](docs/en/userguide/index.md)

## Thanks

I would like to thank everyone who has contributed to the module, bugfixers, testers, clients who use the module and everyone that submits new features.

A big thanks goes out to [Jan Düsedau](http://eformation.de) for drawing the custom icon set for the form fields.

## Versioning

This library follows [Semver](http://semver.org). According to Semver, you will be able to upgrade to any minor or patch version of this library without any breaking changes to the public API. Semver also requires that we clearly define the public API for this library.

All methods, with `public` visibility, are part of the public API. All other methods are not part of the public API. Where possible, we'll try to keep `protected` methods backwards-compatible in minor/patch versions, but if you're overriding methods then please test your work before upgrading.

## Reporting Issues

Please [create an issue](http://github.com/silverstripe/silverstripe-userforms/issues) for any bugs you've found, or features you're missing.
