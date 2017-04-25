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

    public function migrateSettings($data)
    {
        $this->migratePlaceholder();
        parent::migrateSettings($data);
    }

    private function migratePlaceholder()
    {
        // Migrate Placeholder setting from EditableNumericField table to EditableFormField table
        if ($this->Placeholder) {
            return;
        }
        // Check if draft table exists
        $query = "SHOW TABLES LIKE 'EditableNumericField'";
        $tableExists = DB::query($query)->value();
        if ($tableExists == null) {
            return;
        }
        // Check if old Placeholder column exists
        $query = "SHOW COLUMNS FROM `EditableNumericField` LIKE 'Placeholder'";
        $columnExists = DB::query($query)->value();
        if ($columnExists == null) {
            return;
        }
        // Fetch existing draft Placeholder value
        $query = "SELECT `Placeholder` FROM `EditableNumericField` WHERE `ID` = '$this->ID'";
        $draftPlaceholder = DB::query($query)->value();

        if (!$draftPlaceholder) {
            return;
        }
        // Update draft Placeholder value
        $query = "UPDATE `EditableFormField` SET `Placeholder` = '$draftPlaceholder' WHERE `ID` = '$this->ID'";
        DB::query($query);

        $livePlaceholder = $draftPlaceholder;

        // Check if live table exists
        $query = "SHOW TABLES LIKE 'EditableNumericField_Live'";
        $tableExists = DB::query($query)->value();
        if ($tableExists != null) {
            // Fetch existing live Placeholder value
            $query = "SELECT `Placeholder` FROM `EditableNumericField_Live` WHERE `ID` = '" . $this->ID . "'";
            $livePlaceholder = DB::query($query)->value();
            if (!$livePlaceholder) {
                $livePlaceholder = $draftPlaceholder;
            }
        }

        // Update live Placeholder value
        $query = "UPDATE `EditableFormField_Live` SET `Placeholder` = '$livePlaceholder' WHERE `ID` = '$this->ID'";
        DB::query($query);
    }
}
