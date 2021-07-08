<?php

namespace SilverStripe\UserForms\Model\EditableFormField;

use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextField;
use SilverStripe\UserForms\Model\EditableCustomRule;
use SilverStripe\UserForms\Model\EditableFormField;

/**
 * EditableDropdown
 *
 * Represents a modifiable dropdown (select) box on a form
 *
 * @package userforms
 * @property bool $UseEmptyString
 * @property string $EmptyString
 */
class EditableDropdown extends EditableMultipleOptionField
{
    private static $singular_name = 'Dropdown Field';

    private static $plural_name = 'Dropdowns';

    private static $db = array(
        'UseEmptyString' => 'Boolean',
        'EmptyString' => 'Varchar(255)',
    );

    private static $table_name = 'EditableDropdown';

    /**
     * @return FieldList
     */
    public function getCMSFields()
    {
        $this->beforeUpdateCMSFields(function (FieldList $fields) {
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
        });

        return parent::getCMSFields();
    }

    /**
     * @return DropdownField
     */
    public function getFormField()
    {
        $field = DropdownField::create($this->Name, $this->Title ?: false, $this->getOptionsMap())
            ->setFieldHolderTemplate(EditableFormField::class . '_holder')
            ->setTemplate(__CLASS__);

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
