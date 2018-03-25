<?php

namespace SilverStripe\UserForms\Tests\Model\EditableFormField;

use SilverStripe\Dev\SapphireTest;
use SilverStripe\UserForms\Model\EditableFormField\EditableNumericField;

class EditableNumericFieldTest extends SapphireTest
{
    public function testAllowEmptyTitle()
    {
        /** @var EditableNumericField $field */
        $field = EditableNumericField::create();
        $field->Name = 'EditableFormField_123456';
        $this->assertEmpty($field->getFormField()->Title());
    }
}
