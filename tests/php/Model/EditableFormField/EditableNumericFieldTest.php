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

    public function testValidateAddsErrorWhenMinValueIsGreaterThanMaxValue()
    {
        /** @var EditableNumericField $field */
        $field = EditableNumericField::create();
        $field->MinValue = 10;
        $field->MaxValue = 5;

        $result = $field->validate();
        $this->assertFalse($result->isValid(), 'Validation should fail when min is greater than max');
        $this->assertContains('Minimum length should be less than the maximum length', $result->serialize());
    }

    public function testValidate()
    {
        /** @var EditableNumericField $field */
        $field = EditableNumericField::create();
        $field->MinValue = 5;
        $field->MaxValue = 10;

        $result = $field->validate();
        $this->assertTrue($result->isValid());
    }
}
