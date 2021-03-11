<?php

namespace SilverStripe\UserForms\Tests\Model\EditableFormField;

use SilverStripe\Dev\SapphireTest;
use SilverStripe\UserForms\Model\EditableFormField\EditableCheckbox;

class EditableCheckboxTest extends SapphireTest
{
    public function testAllowEmptyTitle()
    {
        /** @var EditableCheckbox $field */
        $field = EditableCheckbox::create();
        $field->Name = 'EditableFormField_123456';
        $this->assertEmpty($field->getFormField()->Title());
    }
}
