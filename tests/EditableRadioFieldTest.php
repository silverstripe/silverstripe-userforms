<?php

class EditableRadioFieldTest extends SapphireTest
{
    protected static $fixture_file = 'EditableFormFieldTest.yml';

    /**
     * Tests that this element is rendered with a custom template
     */
    public function testRenderedWithCustomTemplate()
    {
        $radio = $this->objFromFixture('EditableRadioField', 'radio-field');
        $this->assertEquals('UserFormsOptionSetField', $radio->getFormField()->getTemplate());
    }
}
