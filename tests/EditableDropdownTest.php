<?php

/**
 * Tests the {@see EditableDropdown} class
 */
class EditableDropdownTest extends SapphireTest
{

    public static $fixture_file = 'userforms/tests/EditableFormFieldTest.yml';

    public function setUp()
    {
        parent::setUp();
    }

    /**
     * Tests that the field sets the empty string if set
     */
    public function testFormField()
    {
        if (!$dropdown = EditableDropdown::get()->filter('UseEmptyString', true)->first()) {
            $dropdown = $this->objFromFixture('EditableDropdown', 'basic-dropdown');

            $dropdown->UseEmptyString = true;
            $dropdown->EmptyString = 'My Default Empty String';
            $dropdown->write();
        }

        $field = $dropdown->getFormField();
        $this->assertEquals($field->getEmptyString(), 'My Default Empty String');

        $alternateDropdown = $this->objFromFixture('EditableDropdown', 'department-dropdown');
        $formField = $alternateDropdown->getFormField();
        $this->assertFalse($formField->getHasEmptyDefault());

        $alternateDropdown->UseEmptyString = true;
        $alternateDropdown->write();
        $this->assertEquals($formField->getEmptyString(), '');

    }

}
