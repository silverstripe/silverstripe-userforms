<?php

namespace SilverStripe\UserForms\FormField;

use SilverStripe\Forms\CheckboxSetField;
use SilverStripe\UserForms\Model\EditableFormField;

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

    /**
     * @inheritdoc
     *
     * @param Validator $validator
     *
     * @return bool
     */
    public function validate($validator)
    {
        // get the previous values (could contain comma-delimited list)

        $previous = $value = $this->Value();

        if (is_string($value) && strstr($value ?? '', ",")) {
            $value = explode(",", $value ?? '');
        }

        // set the value as an array for parent validation

        $this->setValue($value);

        $validated = parent::validate($validator);

        // restore previous value after validation

        $this->setValue($previous);

        return $validated;
    }
}
