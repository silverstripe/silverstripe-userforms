<?php

namespace SilverStripe\UserForms\Form;

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
     * @return boolean
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

            $error = false;

            // validate if there are no display rules or the field is conditionally visible
            if (!$this->hasDisplayRules($editableFormField) ||
                $this->conditionalFieldEnabled($editableFormField, $data)) {
                $error = $this->validateRequired($formField, $data);
            }

            // handle error case
            if ($formField && $error) {
                $this->handleError($formField, $fieldName);

                $valid = false;
            }
        }

        return $valid;
    }

    private function getEditableFormFieldByName($name)
    {
        return EditableFormField::get()->filter(['name' => $name])->first();
    }

    private function hasDisplayRules($field)
    {
        return ($field->DisplayRules()->count() > 0);
    }

    private function conditionalFieldEnabled($editableFormField, $data)
    {
        $displayRules = $editableFormField->DisplayRules();

        $conjunction = $editableFormField->DisplayRulesConjunctionNice();

        $displayed = ($editableFormField->ShowOnLoadNice() === 'show');

        // && start with true and find and condition that doesn't satisfy
        // || start with false and find and condition that satisfies
        $conditionsSatisfied = ($conjunction === '&&');

        foreach ($displayRules as $rule) {
            $controllingField = EditableFormField::get()->byID($rule->ConditionFieldID);

            if ($controllingField->DisplayRules()->count() > 0) { // controllingField is also a conditional field
                // recursively check - if any of the dependant fields are hidden, then this field cannot be visible.
                if ($this->conditionalFieldEnabled($controllingField, $data)) {
                    return false;
                };
            }

            $ruleSatisfied = $rule->validateAgainstFormData($data);

            if ($conjunction === '||' && $ruleSatisfied) {
                $conditionsSatisfied = true;
                break;
            }
            if ($conjunction === '&&' && !$ruleSatisfied) {
                $conditionsSatisfied = false;
                break;
            }
        }

        // initially displayed - condition fails || initially hidden, condition passes
        return ($displayed xor $conditionsSatisfied);
    }

    // logic replicated from php() method of parent class SilverStripe\Forms\RequiredFields
    // TODO refactor to share with parent (would require corrosponding change in framework)
    private function validateRequired($field, $data)
    {
        $error = false;
        $fieldName = $field->getName();
        // submitted data for file upload fields come back as an array
        $value = isset($data[$fieldName]) ? $data[$fieldName] : null;

        if (is_array($value)) {
            if ($field instanceof FileField && isset($value['error']) && $value['error']) {
                $error = true;
            } else {
                $error = (count($value)) ? false : true;
            }
        } else {
            // assume a string or integer
            $error = (strlen($value)) ? false : true;
        }

        return $error;
    }

    private function handleError($formField, $fieldName)
    {
        $errorMessage = _t(
            'SilverStripe\\Forms\\Form.FIELDISREQUIRED',
            '{name} is required',
            array(
                'name' => strip_tags(
                    '"' . ($formField->Title() ? $formField->Title() : $fieldName) . '"'
                )
            )
        );

        if ($msg = $formField->getCustomValidationMessage()) {
            $errorMessage = $msg;
        }

        $this->validationError(
            $fieldName,
            $errorMessage,
            "required"
        );
    }
}
