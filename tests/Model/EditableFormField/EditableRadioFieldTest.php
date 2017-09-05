<?php

namespace SilverStripe\UserForms\Tests\Model\EditableFormField;

use SilverStripe\Dev\SapphireTest;
use SilverStripe\UserForms\FormField\UserFormsCheckboxSetField;
use SilverStripe\UserForms\Model\EditableFormField\EditableCheckboxGroupField;
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
            'SilverStripe\\UserForms\\FormField\\UserFormsOptionSetField',
            $radio->getFormField()->getTemplate()
        );
    }
}
