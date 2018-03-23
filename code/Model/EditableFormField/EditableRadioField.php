<?php

namespace SilverStripe\UserForms\Model\EditableFormField;

use SilverStripe\Forms\OptionsetField;
use SilverStripe\UserForms\Model\EditableCustomRule;

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

    private static $table_name = 'EditableRadioField';

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
        $field = OptionsetField::create($this->Name, $this->Title ?: false, $this->getOptionsMap())
            ->setFieldHolderTemplate(EditableMultipleOptionField::class . '_holder')
            ->setTemplate('SilverStripe\\UserForms\\FormField\\UserFormsOptionSetField');

        // Set default item
        $defaultOption = $this->getDefaultOptions()->first();
        if ($defaultOption) {
            $field->setValue($defaultOption->Value);
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

    public function isRadioField()
    {
        return true;
    }
}
