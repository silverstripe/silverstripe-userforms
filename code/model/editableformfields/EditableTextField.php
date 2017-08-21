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
        // PHP 5.3 compat
        $self = $this;

        $this->beforeUpdateCMSFields(function ($fields) use ($self) {
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
                    $self->config()->get('autocomplete_options')
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
}
