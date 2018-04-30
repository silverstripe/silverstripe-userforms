<?php

namespace SilverStripe\UserForms\Model\EditableFormField;

use SilverStripe\Core\Manifest\ModuleLoader;
use SilverStripe\Forms\DropdownField;
use SilverStripe\i18n\i18n;
use SilverStripe\UserForms\Model\EditableCustomRule;
use SilverStripe\UserForms\Model\EditableFormField;

/**
 * A dropdown field which allows the user to select a country
 *
 * @package userforms
 */
class EditableCountryDropdownField extends EditableFormField
{
    private static $singular_name = 'Country Dropdown';

    private static $plural_name = 'Country Dropdowns';

    private static $table_name = 'EditableCountryDropdownField';

    /**
     * @return \SilverStripe\Forms\FieldList
     */
    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->replaceField(
            'Default',
            DropdownField::create('Default', _t(__CLASS__ . '.DEFAULT', 'Default value'))
            ->setSource(i18n::getData()->getCountries())
        );

        return $fields;
    }

    public function getFormField()
    {
        $field = DropdownField::create($this->Name, $this->Title ?: false)
            ->setSource(i18n::getData()->getCountries())
            ->setFieldHolderTemplate(EditableFormField::class . '_holder')
            ->setTemplate(EditableDropdown::class);

        // Set default
        if ($this->Default) {
            $field->setValue($this->Default);
        }

        $this->doUpdateFormField($field);

        return $field;
    }

    public function getValueFromData($data)
    {
        if (isset($data[$this->Name])) {
            $source = $this->getFormField()->getSource();
            return $source[$data[$this->Name]];
        }
    }

    public function getIcon()
    {
        $resource = ModuleLoader::getModule('silverstripe/userforms')->getResource('images/editabledropdown.png');

        if (!$resource->exists()) {
            return '';
        }

        return $resource->getURL();
    }

    public function getSelectorField(EditableCustomRule $rule, $forOnLoad = false)
    {
        return "$(\"select[name='{$this->Name}']\")";
    }
}
