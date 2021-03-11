<?php

namespace SilverStripe\UserForms\Tests\Model\EditableFormField;

use SilverStripe\Dev\SapphireTest;
use SilverStripe\UserForms\Model\EditableFormField\EditableFormHeading;

class EditableFormHeadingTest extends SapphireTest
{
    public function testAllowEmptyTitle()
    {
        /** @var EditableFormHeading $field */
        $field = EditableFormHeading::create();
        $field->Name = 'EditableFormField_123456';
        $this->assertEmpty($field->getFormField()->Title());
    }
}
