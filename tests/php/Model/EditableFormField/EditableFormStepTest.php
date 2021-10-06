<?php

namespace SilverStripe\UserForms\Tests\Model\EditableFormField;

use SilverStripe\Dev\SapphireTest;
use SilverStripe\UserForms\Model\EditableFormField\EditableFormStep;

class EditableFormStepTest extends SapphireTest
{
    public function testAllowEmptyTitle()
    {
        /** @var EditableFormStep $field */
        $field = EditableFormStep::create();
        $field->Name = 'EditableFormField_123456';
        $this->assertEmpty($field->getFormField()->Title());
    }
}
