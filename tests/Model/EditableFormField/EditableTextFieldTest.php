<?php

class EditableTextFieldTest extends SapphireTest
{
    public function testGetCmsFields()
    {
        Config::inst()->remove('EditableTextField', 'autocomplete_options');
        Config::inst()->update('EditableTextField', 'autocomplete_options', array('foo' => 'foo'));

        $field = new EditableTextField;
        $result = $field->getCMSFields();

        $autocompleteField = $result->fieldByName('Root.Main.Autocomplete');
        $this->assertInstanceOf('DropdownField', $autocompleteField);
        $this->assertEquals(array('foo' => 'foo'), $autocompleteField->getSource());
    }
}
