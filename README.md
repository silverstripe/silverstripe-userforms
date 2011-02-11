chillap:scripts(git-install) ingo$ php TranslateSSDocs.php ~/Silverstripe/silverstripe-userforms/README.md 
# User Defined Form

## Introduction

UserForms enables CMS users to create dynamic forms via a drag and drop interface and without getting involved in any PHP code.

## Maintainer Contact

Will Rossiter (Nickname: wrossiter, willr) `<will (at) silverstripe (dot) com>`

## Requirements

 * SilverStripe 2.4.0+
 * PHP 5 >= 5.1.0 (fputcsv)

## Features

*  Construct a form using all major form fields (text, email, dropdown, radio, checkbox..)
*  Ability to Extend userforms from other modules to provide extra fields. Such as Map Input Field
*  Ability to email multiple people the form submission
*  View submitted submissions and export them to CSV
*  Define custom error messages and validation settings
*  Optionally Display and Hide fields using javascript based on users input

## Installation

 1.  Download the module from the link above. 
 2.  Extract the file (if you are on windows try 7-zip for extracting tar.gz files
 3.  Make sure the folder after being extracted is named 'userforms' 
 4.  Place this directory in your sites root directory. This is the one with sapphire and cms in it.
 5.  Run in your browser - `/dev/build` to rebuild the database. 
 6.  You should see a new PageType in the CMS 'User Defined Form'. This has a new 'Form' tab which has your form builder.

## Upgrading

### 0.1 to 0.2
We undertook some major API changes. To help you migrate from 0.1 to 0.2 we have included a build task which you can run which will go through your installed forms and rebuilt them with the new 0.2 datamodel.

To run this build task you visit the url `/dev/tasks/UserFormsMigrationTask`