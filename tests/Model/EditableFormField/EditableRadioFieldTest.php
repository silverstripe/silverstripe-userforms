<?php

namespace SilverStripe\UserForms\Tests\Model\EditableFormField;

use SilverStripe\Dev\SapphireTest;
use SilverStripe\UserForms\FormField\UserFormsOptionSetField;
use SilverStripe\UserForms\Model\EditableFormField\EditableRadioField;

class EditableRadioFieldTest extends SapphireTest
{
    protected static $fixture_file = '../EditableFormFieldTest.yml';

    /**
     * Tests that this element is rendered with a custom template
     */
    public function testRenderedWithCustomTemplate()
    {
        $radio = $this->objFromFixture(EditableRadioField::class, 'radio-field');
        $this->assertSame(
            UserFormsOptionSetField::class,
            $radio->getFormField()->getTemplate()
        );
    }

    public function testAllowEmptyTitle()
    {
        /** @var EditableRadioField $field */
        $field = EditableRadioField::create();
        $field->Name = 'EditableFormField_123456';
        $this->assertEmpty($field->getFormField()->Title());
    }
}
