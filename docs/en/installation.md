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

## File Uploads and Security

The module optionally allows adding a "File Upload Field" to a form.
The field enables users of this form to upload files to the website's assets
so they can be viewed later by CMS authors. Small files
are also attached to the (optional) email notifications
to any configured recipients.

The field is disabled by default since implementors need to determine how files are secured.
Since uploaded files are kept in `assets/` folder of the webroot, there is no built-in
permission control around who can view them. It is unlikely
that website users guess the URLs to uploaded files unless
they are specifically exposed through custom code.

You should think carefully about the use case for file uploads.
Unauthorised viewing of files might be desired, e.g. submissions for public competitions. 
In other cases, submissions could be expected to contain private data.
Please consider securing these files, e.g. through the [secureassets](http://addons.silverstripe.org/add-ons/silverstripe/secureassets) module.

The field can be enabled with the following configuration setting:

```yml
EditableFileField:
  hidden: false
```

Allowed file extensions can be configured globally through `File.allowed_extensions`,
and default to a safe set of files (e.g. disallowing `*.php` uploads).
The allowed upload size is determined by PHP configuration
for this website (the smaller value of `upload_max_filesize` or `post_max_size`).

### Custom email templates

If you want to use custom email templates set the following config option.

````
UserDefinedForm:
  email_template_directory: your/template/path/
````

Any SilverStripe templates placed in your `email_template_directory` directory will be available for use with submission emails.
