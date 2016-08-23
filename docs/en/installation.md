# Installation

Installation can be done either by composer or by manually downloading a release.

## Via composer

`composer require "silverstripe/userforms:*"`

## Manually

 1.  Download the module from [the releases page](https://github.com/silverstripe/silverstripe-userforms/releases).
 2.  Extract the file (if you are on windows try 7-zip for extracting tar.gz files
 3.  Make sure the folder after being extracted is named 'userforms' 
 4.  Place this directory in your sites root directory. This is the one with framework and cms in it.
 5.  Install the dependencies. See the "require" section of [composer.json](https://github.com/silverstripe/silverstripe-userforms/blob/master/composer.json)

## Configuration

After installation, make sure you rebuild your database through `dev/build`.

You should see a new PageType in the CMS 'User Defined Form'. This has a new 'Form' tab which has your form builder.

## File Uploads and Security

The module optionally allows adding a "File Upload Field" to a form.
The field enables users of this form to upload files to the website's assets
so they can be viewed later by CMS authors. Small files
are also attached to the (optional) email notifications
to any configured recipients.

Allowed file extensions can be configured globally through `File.allowed_extensions`,
and default to a safe set of files (e.g. disallowing `*.php` uploads).
You can define further exclusions through the `EditableFileField.allowed_extensions_blacklist`
configuration setting.

The allowed upload size can be set in the cms as long as it doesn't exceed the PHP configuration
for this website (the smaller value of `upload_max_filesize` or `post_max_size`).

The field is disabled by default since implementors need to determine how files are secured.
Since uploaded files are kept in `assets/` folder of the webroot, there is no built-in
permission control around who can view them. It is unlikely
that website users guess the URLs to uploaded files unless
they are specifically exposed through custom code.

You should think carefully about the use case for file uploads.
Unauthorised viewing of files might be desired, e.g. submissions for public competitions. 
In other cases, submissions could be expected to contain private data.

In order to enable this field it is advisable to install the
[secureassets](http://addons.silverstripe.org/add-ons/silverstripe/secureassets) module.

This can be done using the below composer command:

```
composer require silverstripe/secureassets ~1.0.4
```

This step will have the following effects:

 * The "File Upload Field" will be enabled through the CMS interface
 * By default any new file upload fields will be assigned to a default folder, `SecureUploads`,
   which can only be accessed by admins.
 * Any existing file upload fields, if they are not assigned to a folder, will now upload
   to this folder.
 * Any existing file upload fields which are assigned folders will have the security settings
   for those folders updated so that only admins can access them.

This functionality can be configured in the following ways:

 * Assigning another group to the `SecureUploads` folder will allow users from that group
   (rather than admins only) to access those files.
 * The folder name can be configured using the `EditableFileField.secure_folder_name` config option.
 * Security functionality can be disabled (although this is not advisable) by setting
   `EditableFileField.disable_security` to true.


### Custom email templates

If you want to use custom email templates set the following config option.

````
UserDefinedForm:
  email_template_directory: your/template/path/
````

Any SilverStripe templates placed in your `email_template_directory` directory will be available for use with submission emails.

### Custom Multi-Step button Text

If you want to change the button text when using the Multi-Step/Page Break feature, simply add the following to your `config.yml`:

````
UserForm:
  button_text: 'Your Text Here'
````