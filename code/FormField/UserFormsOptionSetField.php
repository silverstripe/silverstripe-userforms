<?php

namespace SilverStripe\UserForms\FormField;

use SilverStripe\Forms\OptionsetField;
use SilverStripe\UserForms\Model\EditableFormField;

/**
 * @package userforms
 */
class UserFormsOptionSetField extends OptionsetField
{

    /**
     * If your project uses a custom UserFormsCheckboxSetField.ss, ensure that it includes
     * `$Top.getValidationAttributesHTML().RAW` so that custom validation messages work
     * For further details see
     * templates/SilverStripe/UserForms/FormField/UserFormsCheckboxSetField.ss
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
}
