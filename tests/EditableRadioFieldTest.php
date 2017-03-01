<?php

class EditableRadioFieldTest extends SapphireTest
{
    public static $fixture_file = 'userforms/tests/EditableFormFieldTest.yml';

    /**
     * Tests that this element is rendered with a custom template
     */
    public function testRenderedWithCustomTemplate()
    {
        $radio = $this->objFromFixture('EditableRadioField', 'radio-field');
        $this->assertEquals('UserFormsOptionSetField', $radio->getFormField()->getTemplate());
    }
}
