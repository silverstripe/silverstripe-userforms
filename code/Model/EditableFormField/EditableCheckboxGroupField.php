<?php

namespace SilverStripe\UserForms\Model\EditableFormField;

use SilverStripe\UserForms\FormField\UserFormsCheckboxSetField;
use SilverStripe\UserForms\Model\EditableCustomRule;

/**
 * EditableCheckboxGroup
 *
 * Represents a set of selectable radio buttons
 *
 * @package userforms
 */
class EditableCheckboxGroupField extends EditableMultipleOptionField
{
    private static $singular_name = 'Checkbox Group';

    private static $plural_name = 'Checkbox Groups';

    protected $jsEventHandler = 'click';

    private static $table_name = 'EditableCheckboxGroupField';

    public function getFormField()
    {
        $field = UserFormsCheckboxSetField::create($this->Name, $this->Title ?: false, $this->getOptionsMap())
            ->setFieldHolderTemplate(EditableMultipleOptionField::class . '_holder')
            ->setTemplate(UserFormsCheckboxSetField::class);

        // Set the default checked items
        $defaultCheckedItems = $this->getDefaultOptions();
        if ($defaultCheckedItems->count()) {
            $field->setDefaultItems($defaultCheckedItems->map('Value')->keys());
        }

        $this->doUpdateFormField($field);
        return $field;
    }

    public function getValueFromData($data)
    {
        $result = '';
        $entries = (isset($data[$this->Name])) ? $data[$this->Name] : false;

        if ($entries) {
            if (!is_array($data[$this->Name])) {
                $entries = [$data[$this->Name]];
            }
            foreach ($entries as $selected => $value) {
                if (!$result) {
                    $result = $value;
                } else {
                    $result .= ', ' . $value;
                }
            }
        }
        return $result;
    }

    public function getSelectorField(EditableCustomRule $rule, $forOnLoad = false)
    {
        // watch out for checkboxs as the inputs don't have values but are 'checked
        if ($rule->FieldValue) {
            return "$(\"input[name='{$this->Name}[]'][value='{$rule->FieldValue}']\")";
        } else {
            return "$(\"input[name='{$this->Name}[]']:first\")";
        }
    }

    public function isCheckBoxField()
    {
        return true;
    }

    public function getSelectorFieldOnly()
    {
        return "[name='{$this->Name}[]']";
    }

    public function isCheckBoxGroupField()
    {
        return true;
    }
}
