<?php

use SilverStripe\Forms\CompositeField;

/**
 * Represents a composite field group, which may contain other groups
 */
abstract class UserFormsCompositeField extends CompositeField implements UserFormsFieldContainer
{

    /**
     * Parent field
     *
     * @var UserFormsFieldContainer
     */
    protected $parent = null;

    public function getParent()
    {
        return $this->parent;
    }

    public function setParent(UserFormsFieldContainer $parent)
    {
        $this->parent = $parent;
        return $this;
    }

    public function processNext(EditableFormField $field)
    {
        // When we find a step, bubble up to the top
        if ($field instanceof EditableFormStep) {
            return $this->getParent()->processNext($field);
        }

        // Skip over fields that don't generate formfields
        $formField = $field->getFormField();
        if (!$formField) {
            return $this;
        }

        // Save this field
        $this->push($formField);

        // Nest fields that are containers
        if ($formField instanceof UserFormsFieldContainer) {
            return $formField->setParent($this);
        }

        // Add any subsequent fields to this
        return $this;
    }
}
