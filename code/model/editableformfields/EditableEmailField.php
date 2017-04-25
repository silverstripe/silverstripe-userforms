<?php
/**
 * EditableEmailField
 *
 * Allow users to define a validating editable email field for a UserDefinedForm
 *
 * @package userforms
 */

class EditableEmailField extends EditableFormField
{

    private static $singular_name = 'Email Field';

    private static $plural_name = 'Email Fields';

    private static $has_placeholder = true;

    public function getSetsOwnError()
    {
        return true;
    }

    public function getFormField()
    {
        $field = EmailField::create($this->Name, $this->EscapedTitle, $this->Default)
            ->setFieldHolderTemplate('UserFormsField_holder')
            ->setTemplate('UserFormsField');

        $this->doUpdateFormField($field);

        return $field;
    }

    /**
     * Updates a formfield with the additional metadata specified by this field
     *
     * @param FormField $field
     */
    protected function updateFormField($field)
    {
        parent::updateFormField($field);

        $field->setAttribute('data-rule-email', true);
    }

    public function migrateSettings($data)
    {
        $this->migratePlaceholder();
        parent::migrateSettings($data);
    }

    private function migratePlaceholder()
    {
        // Migrate Placeholder setting from _obsolete_EditableEmailField table to EditableFormField table
        if ($this->Placeholder) {
            return;
        }
        // Check if draft table exists
        $query = "SHOW TABLES LIKE '_obsolete_EditableEmailField'";
        $tableExists = DB::query($query)->value();
        if ($tableExists == null) {
            return;
        }
        // Check if old Placeholder column exists
        $query = "SHOW COLUMNS FROM `_obsolete_EditableEmailField` LIKE 'Placeholder'";
        $columnExists = DB::query($query)->value();
        if ($columnExists == null) {
            return;
        }
        // Fetch existing draft Placeholder value
        $query = "SELECT `Placeholder` FROM `_obsolete_EditableEmailField` WHERE `ID` = '$this->ID'";
        $draftPlaceholder = DB::query($query)->value();

        if (!$draftPlaceholder) {
            return;
        }
        // Update draft Placeholder value
        $query = "UPDATE `EditableFormField` SET `Placeholder` = '$draftPlaceholder' WHERE `ID` = '$this->ID'";
        DB::query($query);

        $livePlaceholder = $draftPlaceholder;

        // Check if live table exists
        $query = "SHOW TABLES LIKE '_obsolete_EditableEmailField_Live'";
        $tableExists = DB::query($query)->value();
        if ($tableExists != null) {
            // Fetch existing live Placeholder value
            $query = "SELECT `Placeholder` FROM `_obsolete_EditableEmailField_Live` WHERE `ID` = '" . $this->ID . "'";
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
