<?php

namespace SilverStripe\UserForms\Tests\Model\EditableFormField;

use SilverStripe\Dev\SapphireTest;
use SilverStripe\UserForms\Model\EditableFormField\EditableEmailField;

class EditableEmailFieldTest extends SapphireTest
{
    public function testAllowEmptyTitle()
    {
        /** @var EditableEmailField $field */
        $field = EditableEmailField::create();
        $field->Name = 'EditableFormField_123456';
        $this->assertEmpty($field->getFormField()->Title());
    }
}
