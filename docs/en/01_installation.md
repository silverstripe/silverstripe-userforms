---
title: Installation
---

# Installation

Installation should be done using Composer:

```bash
composer require silverstripe/userforms
```

## Configuration

After installation, make sure you rebuild your database through `dev/build`.

You should see a new page type in the CMS called "User Defined Form". This has a new "Form" tab which has your form builder.

### File uploads and security

The module allows adding a "File Upload Field" to a form.
The field enables users of this form to upload files to the website's assets
so they can be viewed later by CMS authors. Small files
are also attached to the (optional) email notifications
to any configured recipients.

Allowed file extensions can be configured globally through [`File.allowed_extensions`](api:SilverStripe\Assets\File->allowed_extensions),
and default to a safe set of files (e.g. disallowing `*.php` uploads).
You can define further exclusions through the [`EditableFileField.allowed_extensions_blacklist`](api:SilverStripe\UserForms\Model\EditableFormField\EditableFileField->allowed_extensions_blacklist)
configuration setting.

The allowed upload size can be set in the CMS as long as it doesn't exceed the PHP configuration
for this website (the smaller value of `upload_max_filesize` or `post_max_size`).

### Securing uploaded files

By adding a File Upload Field to your user form you can allow your website users to upload files, which will be stored in a user-defined folder in your Silverstripe CMS system. You can access these files via the "Submissions" tab, or from the "Files" area in the admin interface.

Please be aware that the responsibility of choosing a folder for files to be uploaded into is that of the CMS user. You can set the necessary "can view" permissions for the folder you create and/or choose via the "Files" section, then select that folder in the settings for the File Upload Field in your form.

If you choose a folder that anyone can view you may be exposing files uploaded via your form to the public, as well as anyone with access to the CMS.

You should think carefully about the use case for file uploads.
Unauthorised viewing of files might be desired, e.g. submissions for public competitions.
In other cases, submissions could be expected to contain private data.

### Custom email templates

If you want to use custom email templates set the following config option.

```yml
SilverStripe\UserForms\Model\UserDefinedForm:
  email_template_directory: mysite/templates/custom_userforms_emails/
```

Any templates placed in your `email_template_directory` directory will be available for use with submission emails.

### Custom multi-step button text

If you want to change the button text when using the Multi-Step/Page Break feature, simply add the following to your `config.yml`:

```yml
SilverStripe\UserForms\Form\UserForm:
  button_text: 'Your Text Here'
```
