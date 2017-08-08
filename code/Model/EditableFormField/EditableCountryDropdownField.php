<?php

/**
 * A dropdown field which allows the user to select a country
 *
 * @package userforms
 */
class EditableCountryDropdownField extends EditableFormField
{

    private static $singular_name = 'Country Dropdown';

    private static $plural_name = 'Country Dropdowns';

    /**
     * @return FieldList
     */
    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->removeByName('Default');

        return $fields;
    }

    public function getFormField()
    {
        $field = CountryDropdownField::create($this->Name, $this->EscapedTitle)
            ->setFieldHolderTemplate('UserFormsField_holder')
            ->setTemplate('UserFormsDropdownField');

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
        return USERFORMS_DIR . '/images/editabledropdown.png';
    }

    public function getSelectorField(EditableCustomRule $rule, $forOnLoad = false)
    {
        return "$(\"select[name='{$this->Name}']\")";
    }
}
