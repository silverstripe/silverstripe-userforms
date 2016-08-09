<?php
/**
 * EditableCheckboxGroup
 *
 * Represents a set of selectable radio buttons
 *
 * @package userforms
 */

class EditableCheckboxGroupField extends EditableMultipleOptionField
{

    private static $singular_name = "Checkbox Group";

    private static $plural_name = "Checkbox Groups";

    public function getFormField()
    {
        $field = new UserFormsCheckboxSetField($this->Name, $this->EscapedTitle, $this->getOptionsMap());
        $field->setFieldHolderTemplate('UserFormsMultipleOptionField_holder');

        // Set the default checked items
        $defaultCheckedItems = $this->getDefaultOptions();
        if ($defaultCheckedItems->count()) {
            $field->setDefaultItems($defaultCheckedItems->map('EscapedTitle')->keys());
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
                $entries = array($data[$this->Name]);
            }
            foreach ($entries as $selected => $value) {
                if (!$result) {
                    $result = $value;
                } else {
                    $result .= ", " . $value;
                }
            }
        }
        return $result;
    }

    public function getSelectorField(EditableCustomRule $rule, $forOnLoad = false)
    {
        // watch out for checkboxs as the inputs don't have values but are 'checked
        // @todo - Test this
        if ($rule->FieldValue) {
            return "$(\"input[name='{$this->Name}[]'][value='{$rule->FieldValue}']\")";
        } else {
            return "$(\"input[name='{$this->Name}[]']:first\")";
        }
    }
}
