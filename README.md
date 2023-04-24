# UserForms

[![CI](https://github.com/silverstripe/silverstripe-userforms/actions/workflows/ci.yml/badge.svg)](https://github.com/silverstripe/silverstripe-userforms/actions/workflows/ci.yml)
[![Silverstripe supported module](https://img.shields.io/badge/silverstripe-supported-0071C4.svg)](https://www.silverstripe.org/software/addons/silverstripe-commercially-supported-module-list/)

UserForms enables CMS users to create dynamic forms via a drag and drop interface
and without getting involved in any PHP code.

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
composer require silverstripe/userforms
```

## Spam protection

This module does not include spam protection out of the box. Without it, it's likely that your submissions could contain a considerable amount of spam. For public facing forms it is encouraged you review and install the following module plus one of the recommended 'verification system' modules outlined in the README.

Read the [SpamProtection Module README](https://github.com/silverstripe/silverstripe-spamprotection/) for details on how to configure this module.

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
