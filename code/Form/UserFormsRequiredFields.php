<?php

namespace SilverStripe\UserForms\Form;

use InvalidArgumentException;
use SilverStripe\Dev\Debug;
use SilverStripe\Forms\FileField;
use SilverStripe\Forms\FormField;
use SilverStripe\Forms\RequiredFields;
use SilverStripe\ORM\ArrayLib;
use SilverStripe\UserForms\Model\EditableFormField;

/**
 * An extension of RequiredFields which handles conditionally required fields.
 *
 * A conditionally required is a field that is required, but can be hidden by display rules.
 * When it is visible, (according to the submitted form data) it will be validated as required.
 * When it is hidden, it will skip required validation.
 *
 * Required fields will be validated as usual.
 * Conditionally required fields will be validated IF the display rules are satisfied in the submitted dataset.
 */
class UserFormsRequiredFields extends RequiredFields
{
    /**
     * Allows validation of fields via specification of a php function for
     * validation which is executed after the form is submitted.
     *
     * @param array $data
     *
     * @return bool
     */
    public function php($data)
    {
        $valid = true;
        $fields = $this->form->Fields();

        foreach ($fields as $field) {
            $valid = ($field->validate($this) && $valid);
        }

        if (empty($this->required)) {
            return $valid;
        }

        foreach ($this->required as $fieldName) {
            if (!$fieldName) {
                continue;
            }

            // get form field
            if ($fieldName instanceof FormField) {
                $formField = $fieldName;
                $fieldName = $fieldName->getName();
            } else {
                $formField = $fields->dataFieldByName($fieldName);
            }

            // get editable form field - owns display rules for field
            $editableFormField = $this->getEditableFormFieldByName($fieldName);

            // Validate if the field is displayed
            $error =
                $editableFormField->isDisplayed($data) &&
                $this->validateRequired($formField, $data);

            // handle error case
            if ($formField && $error) {
                $this->handleError($formField, $fieldName);
                $valid = false;
            }
        }

        return $valid;
    }

    /**
     * Retrieve an Editable Form field by its name.
     * @param string $name
     * @return EditableFormField
     */
    private function getEditableFormFieldByName($name)
    {
        $field = EditableFormField::get()->filter(['Name' => $name])->first();

        if ($field) {
            return $field;
        }

        // This should happen if form field data got corrupted
        throw new InvalidArgumentException(sprintf(
            'Could not find EditableFormField with name `%s`',
            $name
        ));
    }

    /**
     * Check if the validation rules for the specified field are met by the provided data.
     *
     * @note Logic replicated from php() method of parent class `SilverStripe\Forms\RequiredFields`
     * @param EditableFormField $field
     * @param array $data
     * @return bool
     */
    private function validateRequired(FormField $field, array $data)
    {
        $error = false;
        $fieldName = $field->getName();
        // submitted data for file upload fields come back as an array
        $value = isset($data[$fieldName]) ? $data[$fieldName] : null;

        if (is_array($value)) {
            if ($field instanceof FileField && isset($value['error']) && $value['error']) {
                $error = true;
            } else {
                $error = (count($value ?? [])) ? false : true;
            }
        } else {
            // assume a string or integer
            $error = (strlen($value ?? '')) ? false : true;
        }

        return $error;
    }

    /**
     * Register an error for the provided field.
     * @param FormField $formField
     * @param string $fieldName
     * @return void
     */
    private function handleError(FormField $formField, $fieldName)
    {
        $errorMessage = _t(
            'SilverStripe\\Forms\\Form.FIELDISREQUIRED',
            '{name} is required',
            [
                'name' => strip_tags(
                    '"' . ($formField->Title() ? $formField->Title() : $fieldName) . '"'
                )
            ]
        );

        if ($msg = $formField->getCustomValidationMessage()) {
            $errorMessage = $msg;
        }

        $this->validationError($fieldName, $errorMessage, "required");
    }
}
