<?php

namespace SilverStripe\UserForms\Tests\FormField;

use SilverStripe\Dev\SapphireTest;
use SilverStripe\UserForms\Form\UserFormsRequiredFields;
use SilverStripe\UserForms\FormField\UserFormsCheckboxSetField;
use SilverStripe\UserForms\Model\EditableFormField\EditableCheckboxGroupField;

class UserFormsCheckboxSetFieldTest extends SapphireTest
{
    protected static $fixture_file = '../UserFormsTest.yml';

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

    public function testCustomErrorMessageValidationAttributesHTML()
    {
        /** @var EditableCheckboxGroupField $editableCheckboxGroupField */
        $editableCheckboxGroupField = $this->objFromFixture(EditableCheckboxGroupField::class, 'checkbox-group');
        $editableCheckboxGroupField->Required = true;
        $editableCheckboxGroupField->CustomErrorMessage = 'My custom error message with \'single\' and "double" quotes';
        $userFormsCheckboxSetField = $editableCheckboxGroupField->getFormField();
        $html = $userFormsCheckboxSetField->renderWith(UserFormsCheckboxSetField::class)->getValue();
        $attributesHTML = 'data-rule-required="true" data-msg-required="My custom error message with &amp;#039;single&amp;#039; and &amp;quot;double&amp;quot; quotes"';
        $this->assertTrue(strpos($html, $attributesHTML) > 0);
    }
}
