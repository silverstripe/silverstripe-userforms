<?php

namespace SilverStripe\UserForms\Model\EditableFormField;

use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\FieldList;
use SilverStripe\ORM\FieldType\DBDatetime;
use SilverStripe\UserForms\Model\EditableFormField;
use SilverStripe\UserForms\Model\EditableFormField\EditableDateField\FormField;

/**
 * EditableDateField
 *
 * Allows a user to add a date field.
 *
 * @package userforms
 * @property int $DefaultToToday
 */
class EditableDateField extends EditableFormField
{
    private static $singular_name = 'Date Field';

    private static $plural_name = 'Date Fields';

    private static $has_placeholder = true;

    private static $db = [
        'DefaultToToday' => 'Boolean' // From customsettings
    ];

    private static $table_name = 'EditableDateField';

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
                    _t('SilverStripe\\UserForms\\Model\\EditableFormField.DEFAULTTOTODAY', 'Default to Today?')
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
            ? DBDatetime::now()->Format('yyyy-MM-dd')
            : $this->Default;

        $field = FormField::create($this->Name, $this->Title ?: false, $defaultValue)
            ->setFieldHolderTemplate(EditableFormField::class . '_holder')
            ->setTemplate(EditableFormField::class);

        $this->doUpdateFormField($field);

        return $field;
    }
}
