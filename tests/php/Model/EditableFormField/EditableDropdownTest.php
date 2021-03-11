<?php

namespace SilverStripe\UserForms\Tests\Model\EditableFormField;

use SilverStripe\UserForms\Model\EditableFormField\EditableDropdown;
use SilverStripe\Dev\SapphireTest;

/**
 * Tests the {@see EditableDropdown} class
 */
class EditableDropdownTest extends SapphireTest
{
    protected static $fixture_file = '../EditableFormFieldTest.yml';

    /**
     * Tests that the field sets the empty string if set
     */
    public function testFormField()
    {
        if (!$dropdown = EditableDropdown::get()->filter('UseEmptyString', true)->first()) {
            $dropdown = $this->objFromFixture(EditableDropdown::class, 'basic-dropdown');

            $dropdown->UseEmptyString = true;
            $dropdown->EmptyString = 'My Default Empty String';
            $dropdown->write();
        }

        $field = $dropdown->getFormField();
        $this->assertEquals($field->getEmptyString(), 'My Default Empty String');

        $alternateDropdown = $this->objFromFixture(EditableDropdown::class, 'department-dropdown');
        $formField = $alternateDropdown->getFormField();
        $this->assertFalse($formField->getHasEmptyDefault());

        $alternateDropdown->UseEmptyString = true;
        $alternateDropdown->write();
        $this->assertEquals($formField->getEmptyString(), '');
    }

    public function testAllowEmptyTitle()
    {
        /** @var EditableDropdown $field */
        $field = EditableDropdown::create();
        $field->Name = 'EditableFormField_123456';
        $this->assertEmpty($field->getFormField()->Title());
    }

    public function testDuplicate()
    {
        /** @var EditableDropdown $dropdown */
        $dropdown = $this->objFromFixture(EditableDropdown::class, 'basic-dropdown');
        $this->assertCount(2, $dropdown->Options());

        $duplicatedDropdown = $dropdown->duplicate();
        $this->assertSame($dropdown->Options()->count(), $duplicatedDropdown->Options()->count());
    }
}
