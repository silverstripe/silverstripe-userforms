<?php

namespace SilverStripe\UserForms\FormField;

use SilverStripe\Core\Validation\ValidationResult;
use SilverStripe\Forms\CheckboxSetField;

/**
 * @package userforms
 */
class UserFormsCheckboxSetField extends CheckboxSetField
{

    /**
     * If your project uses a custom UserFormsCheckboxSetField template, ensure that it includes
     * `$Top.getValidationAttributesHTML().RAW` so that custom validation messages work
     * For further details see
     * templates/SilverStripe/UserForms/FormField/UserFormsCheckboxSetField template
     *
     * Use on a template with .RAW - single and double quoted strings will be safely escaped
     *
     * @return string
     * @see EditableFormField::updateFormField()
     */
    public function getValidationAttributesHTML()
    {
        $attrs = array_filter(array_keys($this->getAttributes() ?? []), function ($attr) {
            return !in_array($attr, ['data-rule-required', 'data-msg-required']);
        });
        return $this->getAttributesHTML(...$attrs);
    }

    /**
     * jQuery validate requires that the value of the option does not contain
     * the actual value of the input.
     */
    public function getOptions()
    {
        $options = parent::getOptions();

        foreach ($options as $option) {
            $option->Name = "{$this->name}[]";
        }

        return $options;
    }

    public function getValueForValidation(): mixed
    {
        $value = $this->getValue();
        if (is_iterable($value) || is_null($value)) {
            return $value;
        }
        // Value may contain a comma-delimited list of values
        if (is_string($value) && strstr($value, ',')) {
            return explode(',', $value);
        }
        return [$value];
    }
}
