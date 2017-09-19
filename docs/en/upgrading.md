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

The frontend JavaScript and SASS assets have been converted from Compass to
[Webpack](https://github.com/silverstripe/webpack-config), and now live in the `client/` folder. Please
[see here](https://docs.silverstripe.org/en/4/contributing/build_tooling/) for information on this build toolchain.

## Data migration

UserForms 5.0 includes a legacy class name remapping configuration file (legacy.yml) as well as `$table_name`
configuration for every DataObject to ensure that existing data in your database will remain unchanged. This
should help to ensure that your upgrade process from UserForms 4.x on SilverStripe 3 is as seamless as possible.
