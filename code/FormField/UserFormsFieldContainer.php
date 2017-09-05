<?php

namespace SilverStripe\UserForms\FormField;

use SilverStripe\UserForms\Model\EditableFormField;

/**
 * Represents a field container which can iteratively process nested fields, converting it into a fieldset
 */
interface UserFormsFieldContainer
{

    /**
     * Process the next field in the list, returning the container to add the next field to.
     *
     * @param EditableFormField $field
     * @return EditableContainerField
     */
    public function processNext(EditableFormField $field);

    /**
     * Set the parent
     *
     * @param UserFormsFieldContainer $parent
     * @return $this
     */
    public function setParent(UserFormsFieldContainer $parent);

    /**
     * Get the parent
     *
     * @return UserFormsFieldContainer
     */
    public function getParent();
}
