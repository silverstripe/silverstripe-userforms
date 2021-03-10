<?php

namespace SilverStripe\UserForms\Tests\Model\EditableFormField;

use SilverStripe\Dev\SapphireTest;
use SilverStripe\UserForms\FormField\UserFormsCheckboxSetField;
use SilverStripe\UserForms\Model\EditableFormField\EditableCheckboxGroupField;

class EditableCheckboxGroupFieldTest extends SapphireTest
{
    protected static $fixture_file = '../EditableFormFieldTest.yml';

    /**
     * Tests that this element is rendered with a custom template
     */
    public function testRenderedWithCustomTemplate()
    {
        $checkboxGroup = $this->objFromFixture(EditableCheckboxGroupField::class, 'checkbox-group');
        $this->assertSame(UserFormsCheckboxSetField::class, $checkboxGroup->getFormField()->getTemplate());
    }

    public function testAllowEmptyTitle()
    {
        /** @var EditableCheckboxGroupField $field */
        $field = EditableCheckboxGroupField::create();
        $field->Name = 'EditableFormField_123456';
        $this->assertEmpty($field->getFormField()->Title());
    }
}
