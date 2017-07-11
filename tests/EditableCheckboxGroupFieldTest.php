<?php

class EditableCheckboxGroupFieldTest extends SapphireTest
{
    public static $fixture_file = 'userforms/tests/EditableFormFieldTest.yml';

    /**
     * Tests that this element is rendered with a custom template
     */
    public function testRenderedWithCustomTemplate()
    {
        $checkboxGroup = $this->objFromFixture('EditableCheckboxGroupField', 'checkbox-group');
        $this->assertEquals('UserFormsCheckboxSetField', $checkboxGroup->getFormField()->getTemplate());
    }
}
