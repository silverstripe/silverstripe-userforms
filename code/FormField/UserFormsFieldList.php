<?php

namespace SilverStripe\UserForms\FormField;

use SilverStripe\Forms\FieldList;
use SilverStripe\UserForms\Model\EditableFormField;

/**
 * A list of formfields which allows for iterative processing of nested composite fields
 */
class UserFormsFieldList extends FieldList implements UserFormsFieldContainer
{
    public function processNext(EditableFormField $field)
    {
        $formField = $field->getFormField();
        if (!$formField) {
            return $this;
        }

        $this->push($formField);

        if ($formField instanceof UserFormsFieldContainer) {
            return $formField->setParent($this);
        }

        return $this;
    }

    public function getParent()
    {
        // Field list does not have a parent
        return null;
    }

    public function setParent(UserFormsFieldContainer $parent)
    {
        return $this;
    }

    /**
     * Remove all empty steps
     */
    public function clearEmptySteps()
    {
        foreach ($this as $field) {
            if ($field instanceof UserFormsStepField && count($field->getChildren() ?? []) === 0) {
                $this->remove($field);
            }
        }
    }
}
