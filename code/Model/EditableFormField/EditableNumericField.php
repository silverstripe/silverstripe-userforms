<?php

namespace SilverStripe\UserForms\Model\EditableFormField;

use SilverStripe\Forms\FieldGroup;
use SilverStripe\Forms\FormField;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\NumericField;
use SilverStripe\UserForms\Model\EditableFormField;

/**
 * EditableNumericField
 *
 * This control represents a user-defined numeric field in a user defined form
 *
 * @package userforms
 * @property int $MaxValue
 * @property int $MinValue
 */
class EditableNumericField extends EditableFormField
{

    private static $singular_name = 'Numeric Field';

    private static $plural_name = 'Numeric Fields';

    private static $has_placeholder = true;

    private static $db = [
        'MinValue' => 'Int',
        'MaxValue' => 'Int'
    ];

    private static $table_name = 'EditableNumericField';

    public function getSetsOwnError()
    {
        return true;
    }

    /**
     * @return NumericField
     */
    public function getFormField()
    {
        $field = NumericField::create($this->Name, $this->Title ?: false, $this->Default)
            ->setFieldHolderTemplate(EditableFormField::class . '_holder')
            ->setTemplate(EditableFormField::class)
            ->addExtraClass('number');

        $this->doUpdateFormField($field);

        return $field;
    }

    public function getFieldValidationOptions()
    {
        $fields = parent::getFieldValidationOptions();
        $fields->push(FieldGroup::create(
            _t(__CLASS__.'.RANGE', 'Allowed numeric range'),
            [
                NumericField::create('MinValue', false),
                LiteralField::create('RangeValue', _t(__CLASS__.'.RANGE_TO', 'to')),
                NumericField::create('MaxValue', false)
            ]
        ));
        return $fields;
    }

    /**
     * Updates a formfield with the additional metadata specified by this field
     *
     * @param FormField $field
     */
    protected function updateFormField($field)
    {
        parent::updateFormField($field);

        if ($this->MinValue) {
            $field->setAttribute('data-rule-min', $this->MinValue);
        }

        if ($this->MaxValue) {
            $field->setAttribute('data-rule-max', $this->MaxValue);
        }
    }

    public function validate()
    {
        $result = parent::validate();
        if ($this->MinValue > $this->MaxValue) {
            $result->addError(
                _t(__CLASS__ . '.ORDER_WARNING', 'Minimum length should be less than the maximum length.')
            );
        }
        return $result;
    }
}
