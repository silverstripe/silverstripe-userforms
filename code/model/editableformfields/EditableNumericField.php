<?php
/**
 * EditableNumericField
 *
 * This control represents a user-defined numeric field in a user defined form
 *
 * @package userforms
 */

class EditableNumericField extends EditableFormField
{

    private static $singular_name = 'Numeric Field';

    private static $plural_name = 'Numeric Fields';

    private static $has_placeholder = true;

    private static $db = array(
        'MinValue' => 'Int',
        'MaxValue' => 'Int'
    );

    public function getSetsOwnError()
    {
        return true;
    }

    /**
     * @return NumericField
     */
    public function getFormField()
    {
        $field = NumericField::create($this->Name, $this->EscapedTitle, $this->Default)
            ->setFieldHolderTemplate('UserFormsField_holder')
            ->setTemplate('UserFormsField')
            ->addExtraClass('number');

        $this->doUpdateFormField($field);

        return $field;
    }

    public function getFieldValidationOptions()
    {
        $fields = parent::getFieldValidationOptions();
        $fields->push(FieldGroup::create(
            _t("EditableNumericField.RANGE", "Allowed numeric range"),
            array(
                new NumericField('MinValue', false),
                new LiteralField('RangeValue', _t("EditableNumericField.RANGE_TO", "to")),
                new NumericField('MaxValue', false)
            )
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
}
