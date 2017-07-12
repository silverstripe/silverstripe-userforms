<?php

class EditableCheckboxGroupFieldTest extends SapphireTest
{
    protected static $fixture_file = 'EditableFormFieldTest.yml';

    /**
     * Tests that this element is rendered with a custom template
     */
    public function testRenderedWithCustomTemplate()
    {
        $checkboxGroup = $this->objFromFixture('EditableCheckboxGroupField', 'checkbox-group');
        $this->assertEquals('UserFormsCheckboxSetField', $checkboxGroup->getFormField()->getTemplate());
    }
}
