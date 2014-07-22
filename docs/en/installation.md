# Installation

Installation can be done either by composer or by manually downloading a release.

## Via composer

`composer require "silverstripe/userforms:*"`

## Manually

 1.  Download the module from [the releases page](https://github.com/silverstripe/silverstripe-userforms/releases).
 2.  Extract the file (if you are on windows try 7-zip for extracting tar.gz files
 3.  Make sure the folder after being extracted is named 'userforms' 
 4.  Place this directory in your sites root directory. This is the one with framework and cms in it.

## Configuration

After installation, make sure you rebuild your database through `dev/build`.

You should see a new PageType in the CMS 'User Defined Form'. This has a new 'Form' tab which has your form builder.
