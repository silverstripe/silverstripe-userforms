<?php
/**
 * EditableDropdown
 *
 * Represents a modifiable dropdown (select) box on a form
 *
 * @package userforms
 */

class EditableDropdown extends EditableMultipleOptionField
{

    private static $singular_name = 'Dropdown Field';

    private static $plural_name = 'Dropdowns';

    /**
     * @return FieldList
     */
    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->removeByName('Default');

        return $fields;
    }

    /**
     * @return DropdownField
     */
    public function getFormField()
    {
        $field = DropdownField::create($this->Name, $this->EscapedTitle, $this->getOptionsMap())
            ->setFieldHolderTemplate('UserFormsField_holder')
            ->setTemplate('UserFormsDropdownField');

        // Set default
        $defaultOption = $this->getDefaultOptions()->first();
        if ($defaultOption) {
            $field->setValue($defaultOption->EscapedTitle);
        }
        $this->doUpdateFormField($field);
        return $field;
    }

    public function getSelectorField(EditableCustomRule $rule, $forOnLoad = false)
    {
        return "$(\"select[name='{$this->Name}']\")";
    }
}
