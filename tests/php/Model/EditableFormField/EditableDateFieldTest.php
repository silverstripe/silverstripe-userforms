<?php

namespace SilverStripe\UserForms\Tests\Model\EditableFormField;

use SilverStripe\Dev\SapphireTest;
use SilverStripe\UserForms\Model\EditableFormField\EditableDateField;

class EditableDateFieldTest extends SapphireTest
{
    public function testAllowEmptyTitle()
    {
        /** @var EditableDateField $field */
        $field = EditableDateField::create();
        $field->Name = 'EditableFormField_123456';
        $this->assertEmpty($field->getFormField()->Title());
    }
}
