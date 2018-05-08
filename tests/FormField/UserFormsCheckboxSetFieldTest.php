<?php

namespace SilverStripe\UserForms\Tests\FormField;

use SilverStripe\Dev\SapphireTest;
use SilverStripe\UserForms\Form\UserFormsRequiredFields;
use SilverStripe\UserForms\FormField\UserFormsCheckboxSetField;

class UserFormsCheckboxSetFieldTest extends SapphireTest
{
    public function testValidate()
    {
        $field = new UserFormsCheckboxSetField('Field', 'My field', ['One' => 'One', 'Two' => 'Two']);
        $validator = new UserFormsRequiredFields();

        // String values
        $field->setValue('One');
        $this->assertTrue($field->validate($validator));
        $field->setValue('One,Two');
        $this->assertTrue($field->validate($validator));
        $field->setValue('Three,Four');
        $this->assertFalse($field->validate($validator));

        // Array values
        $field->setValue(array('One'));
        $this->assertTrue($field->validate($validator));
        $field->setValue(array('One', 'Two'));
        $this->assertTrue($field->validate($validator));

        // Invalid
        $field->setValue('Three');
        $this->assertFalse($field->validate($validator));
        $field->setValue(array('Three', 'Four'));
        $this->assertFalse($field->validate($validator));
    }
}
