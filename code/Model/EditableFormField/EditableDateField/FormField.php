<?php

namespace SilverStripe\UserForms\Model\EditableFormField\EditableDateField;

use SilverStripe\Forms\DateField;

/**
  * @package userforms
 */
class FormField extends DateField
{
    public function Type()
    {
        return 'date-alt text';
    }
}
