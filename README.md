# UserForms

[![Build Status](https://secure.travis-ci.org/silverstripe/silverstripe-userforms.png?branch=master)](http://travis-ci.org/silverstripe/silverstripe-userforms)

## Introduction

UserForms enables CMS users to create dynamic forms via a drag and drop interface 
and without getting involved in any PHP code.

## Maintainer Contact

	* Will Rossiter (Nickname: wrossiter, willr) `<will (at) fullscreen (dot) io>`

## Requirements

 * SilverStripe 3.1

## Features

*  Construct a form using all major form fields (text, email, dropdown, radio, checkbox..)
*  Ability to extend userforms from other modules to provide extra fields.
*  Ability to email multiple people the form submission
*  View submitted submissions and export them to CSV
*  Define custom error messages and validation settings
*  Optionally display and hide fields using javascript based on users input
*  Pre fill your form fields, by passing your values by url (http://yoursite.com/formpage?EditableField1=MyValue)

## Installation

 1.  Download the module from the link above. 
 2.  Extract the file (if you are on windows try 7-zip for extracting tar.gz files
 3.  Make sure the folder after being extracted is named 'userforms' 
 4.  Place this directory in your sites root directory. This is the one with framework and cms in it.
 5.  Run in your browser - `/dev/build` to rebuild the database. 
 6.  You should see a new PageType in the CMS 'User Defined Form'. This has a new 'Form' tab which has your form builder.

## Tasks

### UserForms EditableFormField Column Clean task ###

This task is used to clear unused columns from EditableFormField

The reason to clear these columns is because having surplus forms can break form saving.

Currently it only supports MySQL and when it is run it queries the EditableFormField class for the valid columns,
it then grabs the columns for the live database it will create a backup of the table and then remove any columns that
are surplus.

To run the task login as Admin and go to to http://yoursite/dev/tasks/UserFormsColumnCleanTask

## Thanks

I would like to thank everyone who has contributed to the module, bugfixers, 
testers, clients who use the module and everyone that submits new features.

A big thanks goes out to [Jan Düsedau](http://eformation.de) for drawing 
the custom icon set for the form fields.

## Contributing

### Translations

Translations of the natural language strings are managed through a third party translation interface, transifex.com. Newly added strings will be periodically uploaded there for translation, and any new translations will be merged back to the project source code.

Please use [https://www.transifex.com/projects/p/silverstripe-userforms](https://www.transifex.com/projects/p/silverstripe-userforms) to contribute translations, rather than sending pull requests with YAML files.