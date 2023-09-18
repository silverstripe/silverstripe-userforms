<?php

namespace SilverStripe\UserForms\Model\EditableFormField;

use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldGroup;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\FormField;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\NumericField;
use SilverStripe\Forms\TextareaField;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\ValidationResult;
use SilverStripe\UserForms\Model\EditableFormField;

/**
 * EditableTextField
 *
 * This control represents a user-defined text field in a user defined form
 *
 * @package userforms
 * @property string $Autocomplete
 * @property int $MaxLength
 * @property int $MinLength
 * @property int $Rows
 */
class EditableTextField extends EditableFormField
{
    private static $singular_name = 'Text Field';

    private static $plural_name = 'Text Fields';

    private static $has_placeholder = true;

    private static $autocomplete_options = [
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
    ];

    protected $jsEventHandler = 'keyup';

    private static $db = [
        'MinLength' => 'Int',
        'MaxLength' => 'Int',
        'Rows' => 'Int(1)',
        'Autocomplete' => 'Varchar(255)'
    ];

    private static $defaults = [
        'Rows' => 1
    ];

    private static $table_name = 'EditableTextField';

    public function getCMSFields()
    {
        $this->beforeUpdateCMSFields(function ($fields) {
            $fields->addFieldsToTab(
                'Root.Main',
                [
                    NumericField::create(
                        'Rows',
                        _t(__CLASS__.'.NUMBERROWS', 'Number of rows')
                    )->setDescription(_t(
                        __CLASS__.'.NUMBERROWS_DESCRIPTION',
                        'Fields with more than one row will be generated as a textarea'
                    )),
                    DropdownField::create(
                        'Autocomplete',
                        _t(__CLASS__.'.AUTOCOMPLETE', 'Autocomplete'),
                        $this->config()->get('autocomplete_options')
                    )->setDescription(_t(
                        __CLASS__.'.AUTOCOMPLETE_DESCRIPTION',
                        'Supported browsers will attempt to populate this field automatically with the users information, use to set the value populated'
                    ))
                ]
            );
        });

        return parent::getCMSFields();
    }

    /**
     * @return ValidationResult
     */
    public function validate()
    {
        $result = parent::validate();

        if ($this->MinLength > $this->MaxLength) {
            $result->addError(_t(
                __CLASS__ . '.MINMAXLENGTHCHECK',
                'Minimum length should be less than the Maximum length.'
            ));
        }

        return $result;
    }

    /**
     * @return FieldList
     */
    public function getFieldValidationOptions()
    {
        $fields = parent::getFieldValidationOptions();

        $fields->merge([
            FieldGroup::create(
                _t(__CLASS__.'.TEXTLENGTH', 'Allowed text length'),
                [
                    NumericField::create('MinLength', false)
                        ->setAttribute('aria-label', _t(__CLASS__ . '.MIN_LENGTH', 'Minimum text length')),
                    LiteralField::create(
                        'RangeLength',
                        '<span class="userform-field__allowed-length-separator">'
                        . _t(__CLASS__ . '.RANGE_TO', 'to')
                        . '</span>'
                    ),
                    NumericField::create('MaxLength', false)
                        ->setAttribute('aria-label', _t(__CLASS__ . '.MAX_LENGTH', 'Maximum text length')),
                ]
            )->addExtraClass('userform-field__allowed-length')
        ]);

        return $fields;
    }

    /**
     * @return TextareaField|TextField
     */
    public function getFormField()
    {
        if ($this->Rows > 1) {
            $field = TextareaField::create($this->Name, $this->Title ?: false, $this->Default)
                ->setFieldHolderTemplate(EditableFormField::class . '_holder')
                ->setTemplate(str_replace('EditableTextField', 'EditableTextareaField', __CLASS__))
                ->setRows($this->Rows);
        } else {
            $field = TextField::create($this->Name, $this->Title ?: false, $this->Default)
                ->setFieldHolderTemplate(EditableFormField::class . '_holder')
                ->setTemplate(EditableFormField::class);
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
            $field->setAttribute('data-rule-minlength', (int) $this->MinLength);
        }

        if (is_numeric($this->MaxLength) && $this->MaxLength > 0) {
            if ($field instanceof TextField) {
                $field->setMaxLength((int) $this->MaxLength);
            }
            $field->setAttribute('data-rule-maxlength', (int) $this->MaxLength);
        }

        if ($this->Autocomplete) {
            $field->setAttribute('autocomplete', $this->Autocomplete);
        }
    }
}
