<?php
/**
 * EditableRadioField
 *
 * Represents a set of selectable radio buttons
 *
 * @package userforms
 */

class EditableRadioField extends EditableMultipleOptionField
{

    private static $singular_name = 'Radio Group';

    private static $plural_name = 'Radio Groups';

    /**
     * @return FieldList
     */
    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->removeByName('Default');

        return $fields;
    }

    public function getFormField()
    {
        $field = OptionsetField::create($this->Name, $this->EscapedTitle, $this->getOptionsMap());
        $field->setFieldHolderTemplate('UserFormsMultipleOptionField_holder');

        // Set default item
        $defaultOption = $this->getDefaultOptions()->first();
        if ($defaultOption) {
            $field->setValue($defaultOption->EscapedTitle);
        }
        $this->doUpdateFormField($field);
        return $field;
    }

    public function getSelectorField(EditableCustomRule $rule, $forOnLoad = false)
    {
        // We only want to trigger on load once for the radio group - hence we focus on the first option only.
        $first = $forOnLoad ? ':first' : '';
        return "$(\"input[name='{$this->Name}']{$first}\")";
    }
}
