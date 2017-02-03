<?php

use SilverStripe\Dev\SapphireTest;

class UserFormsCheckboxSetFieldTest extends SapphireTest
{
    public function testValidate() {
        $field = new UserFormsCheckboxSetField('Field', 'My field', array('One' => 'One', 'Two' => 'Two'));
        $validator = new RequiredFields();

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
