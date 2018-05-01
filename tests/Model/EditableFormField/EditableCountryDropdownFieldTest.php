<?php

namespace SilverStripe\UserForms\Tests\Model\EditableFormField;

use SilverStripe\Dev\SapphireTest;
use SilverStripe\Forms\DropdownField;
use SilverStripe\UserForms\Model\EditableFormField\EditableCountryDropdownField;

class EditableCountryDropdownFieldTest extends SapphireTest
{
    public function testGetIcon()
    {
        $field = new EditableCountryDropdownField;

        $this->assertContains('/images/editabledropdown.png', $field->getIcon());
    }

    public function testAllowEmptyTitle()
    {
        /** @var EditableCountryDropdownField $field */
        $field = EditableCountryDropdownField::create();
        $field->Name = 'EditableFormField_123456';
        $this->assertEmpty($field->getFormField()->Title());
    }

    public function testCMSFieldsContainsDefaultValue()
    {
        /** @var EditableCountryDropdownField $field */
        $field = EditableCountryDropdownField::create();
        $cmsFields = $field->getCMSFields();
        $defaultField = $cmsFields->dataFieldByName('Default');
        $this->assertNotNull($defaultField);
        $this->assertInstanceOf(DropdownField::class, $defaultField);
    }

    public function testDefaultValue()
    {
        /** @var EditableCountryDropdownField $field */
        $field = EditableCountryDropdownField::create();
        $field->Default = 'nz';
        $this->assertEquals($field->getFormField()->Value(), 'nz');
    }

    public function testEmptyDefaultValue()
    {
        /** @var EditableCountryDropdownField $field */
        $field = EditableCountryDropdownField::create();

        /** @var DropdownField $formField */
        $formField = $field->getFormField();
        $this->assertFalse($formField->getHasEmptyDefault());
        $this->assertEmpty($formField->getEmptyString());

        $field->UseEmptyString = true;
        $field->EmptyString = '--- empty ---';

        /** @var DropdownField $formField */
        $formField = $field->getFormField();
        $this->assertTrue($formField->getHasEmptyDefault());
        $this->assertEquals($formField->getEmptyString(), $field->EmptyString);
    }
}
