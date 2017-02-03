<?php

use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\ORM\FieldType\DBDatetime;
use SilverStripe\Forms\DateField;
/**
 * EditableDateField
 *
 * Allows a user to add a date field.
 *
 * @package userforms
 */

class EditableDateField extends EditableFormField
{

    private static $singular_name = 'Date Field';

    private static $plural_name = 'Date Fields';

    private static $db = array(
        'DefaultToToday' => 'Boolean' // From customsettings
    );

    /**
     * @return FieldList
     */
    public function getCMSFields()
    {
        $this->beforeUpdateCMSFields(function (FieldList $fields) {
            $fields->addFieldToTab(
                'Root.Main',
                CheckboxField::create(
                    'DefaultToToday',
                    _t('EditableFormField.DEFAULTTOTODAY', 'Default to Today?')
                ),
                'RightTitle'
            );
        });

        return parent::getCMSFields();
    }

    /**
     * Return the form field
     *
     */
    public function getFormField()
    {
        $defaultValue = $this->DefaultToToday
            ? DBDatetime::now()->Format('Y-m-d')
            : $this->Default;

        $field = EditableDateField_FormField::create($this->Name, $this->EscapedTitle, $defaultValue)
            ->setConfig('showcalendar', true)
            ->setFieldHolderTemplate('UserFormsField_holder')
            ->setTemplate('UserFormsField');

        $this->doUpdateFormField($field);

        return $field;
    }
}

/**
  * @package userforms
 */
class EditableDateField_FormField extends DateField
{

    public function Type()
    {
        return "date-alt text";
    }
}
