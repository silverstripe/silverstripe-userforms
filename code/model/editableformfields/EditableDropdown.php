<?php

/**
 * EditableDropdown
 *
 * Represents a modifiable dropdown (select) box on a form
 *
 * @property bool $UseEmptyString
 * @property string $EmptyString
 *
 * @package userforms
 */
class EditableDropdown extends EditableMultipleOptionField
{

    private static $singular_name = 'Dropdown Field';

    private static $plural_name = 'Dropdowns';

    private static $db = array(
        'UseEmptyString' => 'Boolean',
        'EmptyString' => 'Varchar(255)',
    );

    /**
     * @return FieldList
     */
    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->addFieldToTab(
            'Root.Main',
            CheckboxField::create('UseEmptyString')
                ->setTitle('Set default empty string')
        );

        $fields->addFieldToTab(
            'Root.Main',
            TextField::create('EmptyString')
                ->setTitle('Empty String')
        );

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

        if ($this->UseEmptyString) {
            $field->setEmptyString(($this->EmptyString) ? $this->EmptyString : '');
        }

        // Set default
        $defaultOption = $this->getDefaultOptions()->first();
        if ($defaultOption) {
            $field->setValue($defaultOption->Value);
        }
        $this->doUpdateFormField($field);
        return $field;
    }

    public function getSelectorField(EditableCustomRule $rule, $forOnLoad = false)
    {
        return "$(\"select[name='{$this->Name}']\")";
    }
}
