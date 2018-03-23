<?php

namespace SilverStripe\UserForms\Tests\Model\EditableFormField;

use SilverStripe\Core\Config\Config;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Forms\DropdownField;
use SilverStripe\UserForms\Model\EditableFormField\EditableTextField;

class EditableTextFieldTest extends SapphireTest
{
    public function testGetCmsFields()
    {
        Config::modify()->set(EditableTextField::class, 'autocomplete_options', ['foo' => 'foo']);

        $field = new EditableTextField;
        $result = $field->getCMSFields();

        $autocompleteField = $result->fieldByName('Root.Main.Autocomplete');
        $this->assertInstanceOf(DropdownField::class, $autocompleteField);
        $this->assertEquals(['foo' => 'foo'], $autocompleteField->getSource());
    }

    public function testAllowEmptyTitle()
    {
        /** @var EditableTextField $field */
        $field = EditableTextField::create();
        $field->Name = 'EditableFormField_123456';
        $field->Rows = 1;
        $this->assertEmpty($field->getFormField()->Title());

        $field->Rows = 3;
        $this->assertEmpty($field->getFormField()->Title());
    }
}
