# UserForms

## Introduction

UserForms enables CMS users to create dynamic forms via a drag and drop interface 
and without getting involved in any PHP code.

## Maintainer Contact

	* Will Rossiter (Nickname: wrossiter, willr) `<will (at) fullscreen (dot) io>`

## Requirements

 * SilverStripe 3.0

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
 4.  Place this directory in your sites root directory. This is the one with sapphire and cms in it.
 5.  Run in your browser - `/dev/build` to rebuild the database. 
 6.  You should see a new PageType in the CMS 'User Defined Form'. This has a new 'Form' tab which has your form builder.

## Thanks

I would like to thank everyone who has contributed to the module, bugfixers, 
testers, clients who use the module and everyone that submits new features.

A big thanks goes out to [Jan Düsedau](http://eformation.de) for drawing 
the custom icon set for the form fields.