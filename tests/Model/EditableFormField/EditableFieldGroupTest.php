<?php

namespace SilverStripe\UserForms\Tests\Model\EditableFormField;

use SilverStripe\Dev\SapphireTest;
use SilverStripe\UserForms\Model\EditableFormField\EditableFieldGroup;

class EditableFieldGroupTest extends SapphireTest
{
    public function testAllowEmptyTitle()
    {
        /** @var EditableFieldGroup $field */
        $field = EditableFieldGroup::create();
        $field->Name = 'EditableFormField_123456';
        $this->assertEmpty($field->getFormField()->Title());
    }
}
