<?php

namespace SilverStripe\UserForms\FormField;

use SilverStripe\UserForms\Model\EditableFormField\EditableFieldGroupEnd;
use SilverStripe\UserForms\Model\EditableFormField;

/**
 * Front end composite field for userforms
 */
class UserFormsGroupField extends UserFormsCompositeField
{
    public function __construct($children = null)
    {
        parent::__construct($children);
        $this->setTag('fieldset');
    }

    public function getLegend()
    {
        // Legend defaults to title
        return parent::getLegend() ?: $this->Title();
    }

    public function processNext(EditableFormField $field)
    {
        // When ending a group, jump up one level
        if ($field instanceof EditableFieldGroupEnd) {
            return $this->getParent();
        }

        // Otherwise behave as per normal composite field
        return parent::processNext($field);
    }
}
