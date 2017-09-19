# Upgrading to SilverStripe 4

## API changes

The SilverStripe 4 compatible version of UserForms (5.x) introduces some breaking API changes:

* Namespaces are added. Please use the SilverStripe upgrader to automatically update references in your code.
* Previously deprecated code from UserForms 4.x has been removed:
  * `EditableFormField::getSettings()`
  * `EditableFormField::setSettings()`
  * `EditableFormField::setSetting()`
  * `EditableFormField::getSetting()`
  * `EditableFormField::getFieldName()`
  * `EditableFormField::getSettingName()`
  * `EditableFormField::migrateSettings()`

The frontend JavaScript and SASS assets have been converted from Compass to
[Webpack](https://github.com/silverstripe/webpack-config), and now live in the `client/` folder. Please
[see here](https://docs.silverstripe.org/en/4/contributing/build_tooling/) for information on this build toolchain.

## Data migration

UserForms 5.0 includes a legacy class name remapping configuration file (legacy.yml) as well as `$table_name`
configuration for every DataObject to ensure that existing data in your database will remain unchanged. This
should help to ensure that your upgrade process from UserForms 4.x on SilverStripe 3 is as seamless as possible.

## Secure assets for EditableFileField

In SilverStripe 3 with UserForms you could install the SecureAssets module to allow the ability to set permissions
on folders that files can be uploaded into via your custom forms.

In SilverStripe 4 this functionality is built into the core assets module, so the SecureAssets module is no longer
required, and the SecureEditableFileField extension has been removed.

**Important:** While you shouldn't have to do anything in terms of folder permissions during this upgrade, we highly
recommend that you sanity check any secure folders you have in your website to ensure that the permissions are still
locked down before going live.

Please be aware that if you had SecureAssets installed in SilverStripe 3 with UserForms your EditableFileField folder
choice would be made secure by default, whereas in SilverStripe 4 this is up to the user to configure at their
discretion.
