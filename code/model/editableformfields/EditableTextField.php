<?php
/**
 * EditableTextField
 *
 * This control represents a user-defined text field in a user defined form
 *
 * @package userforms
 */

class EditableTextField extends EditableFormField
{

    private static $singular_name = 'Text Field';

    private static $plural_name = 'Text Fields';

    private static $has_placeholder = true;

    private static $autocomplete_options = array(
        'off' => 'Off',
        'on' => 'On',
        'name' => 'Full name',
        'honorific-prefix' => 'Prefix or title',
        'given-name' => 'First name',
        'additional-name' => 'Additional name',
        'family-name' => 'Family name',
        'honorific-suffix' => 'Suffix (e.g Jr.)',
        'nickname' => 'Nickname',
        'email' => 'Email',
        'organization-title' => 'Job title',
        'organization' => 'Organization',
        'street-address' => 'Street address',
        'address-line1' => 'Address line 1',
        'address-line2' => 'Address line 2',
        'address-line3' => 'Address line 3',
        'address-level1' => 'Address level 1',
        'address-level2' => 'Address level 2',
        'address-level3' => 'Address level 3',
        'address-level4' => 'Address level 4',
        'country' => 'Country',
        'country-name' => 'Country name',
        'postal-code' => 'Postal code',
        'bday' => 'Birthday',
        'sex' => 'Gender identity',
        'tel' => 'Telephone number',
        'url' => 'Home page'
      );

    protected $jsEventHandler = 'keyup';

    private static $db = array(
        'MinLength' => 'Int',
        'MaxLength' => 'Int',
        'Rows' => 'Int(1)',
        'Autocomplete' => 'Varchar(255)'
    );

    private static $defaults = array(
        'Rows' => 1
    );

    public function getCMSFields()
    {
        $this->beforeUpdateCMSFields(function ($fields) {
            $fields->addFieldToTab(
                'Root.Main',
                NumericField::create(
                    'Rows',
                    _t('EditableTextField.NUMBERROWS', 'Number of rows')
                )->setDescription(_t(
                    'EditableTextField.NUMBERROWS_DESCRIPTION',
                    'Fields with more than one row will be generated as a textarea'
                ))
            );

            $fields->addFieldToTab(
                'Root.Main',
                DropdownField::create(
                    'Autocomplete',
                    _t('EditableTextField.AUTOCOMPLETE', 'Autocomplete'),
                    $this->config()->get('autocomplete_options')
                  )->setDescription(_t(
                      'EditableTextField.AUTOCOMPLETE_DESCRIPTION',
                      'Supported browsers will attempt to populate this field automatically with the users information, use to set the value populated'
                  ))
            );

        });

        return parent::getCMSFields();
    }

    /**
     * @return FieldList
     */
    public function getFieldValidationOptions()
    {
        $fields = parent::getFieldValidationOptions();

        $fields->merge(array(
            FieldGroup::create(
                _t('EditableTextField.TEXTLENGTH', 'Allowed text length'),
                array(
                    NumericField::create('MinLength', false),
                    LiteralField::create('RangeLength', _t("EditableTextField.RANGE_TO", "to")),
                    NumericField::create('MaxLength', false)
                )
            )
        ));

        return $fields;
    }

    /**
     * @return TextareaField|TextField
     */
    public function getFormField()
    {
        if ($this->Rows > 1) {
            $field = TextareaField::create($this->Name, $this->EscapedTitle, $this->Default)
                ->setFieldHolderTemplate('UserFormsField_holder')
                ->setTemplate('UserFormsTextareaField')
                ->setRows($this->Rows);
        } else {
            $field = TextField::create($this->Name, $this->EscapedTitle, $this->Default)
                ->setFieldHolderTemplate('UserFormsField_holder')
                ->setTemplate('UserFormsField');
        }

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

        if (is_numeric($this->MinLength) && $this->MinLength > 0) {
            $field->setAttribute('data-rule-minlength', intval($this->MinLength));
        }

        if (is_numeric($this->MaxLength) && $this->MaxLength > 0) {
            if ($field instanceof TextField) {
                $field->setMaxLength(intval($this->MaxLength));
            }
            $field->setAttribute('data-rule-maxlength', intval($this->MaxLength));
        }

        if ($this->Autocomplete) {
            $field->setAttribute('autocomplete', $this->Autocomplete);
        }

    }

    public function migrateSettings($data)
    {
        $this->migratePlaceholder();
        parent::migrateSettings($data);
    }

    private function migratePlaceholder()
    {
        // Migrate Placeholder setting from EditableTextField table to EditableFormField table
        if ($this->Placeholder) {
            return;
        }
        // Check if draft table exists
        $query = "SHOW TABLES LIKE 'EditableTextField'";
        $tableExists = DB::query($query)->value();
        if ($tableExists == null) {
            return;
        }
        // Check if old Placeholder column exists
        $query = "SHOW COLUMNS FROM `EditableTextField` LIKE 'Placeholder'";
        $columnExists = DB::query($query)->value();
        if ($columnExists == null) {
            return;
        }
        // Fetch existing draft Placeholder value
        $query = "SELECT `Placeholder` FROM `EditableTextField` WHERE `ID` = '$this->ID'";
        $draftPlaceholder = DB::query($query)->value();

        if (!$draftPlaceholder) {
            return;
        }
        // Update draft Placeholder value
        $query = "UPDATE `EditableFormField` SET `Placeholder` = '$draftPlaceholder' WHERE `ID` = '$this->ID'";
        DB::query($query);

        $livePlaceholder = $draftPlaceholder;

        // Check if live table exists
        $query = "SHOW TABLES LIKE 'EditableTextField_Live'";
        $tableExists = DB::query($query)->value();
        if ($tableExists != null) {
            // Fetch existing live Placeholder value
            $query = "SELECT `Placeholder` FROM `EditableTextField_Live` WHERE `ID` = '" . $this->ID . "'";
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
