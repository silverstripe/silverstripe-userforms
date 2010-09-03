<?php

/**
 * @package userforms
 */

class EditableFormFieldTest extends FunctionalTest {
	
	static $fixture_file = 'userforms/tests/EditableFormFields.yml';
	
	function testEditableDropdownField() {
		$dropdown = $this->objFromFixture('EditableDropdown', 'basic-dropdown');

		$option1 = $this->objFromFixture('EditableOption', 'option-1');
		$option2 = $this->objFromFixture('EditableOption', 'option-2');
		
		$dropdown->Options()->add($option1);
		$dropdown->Options()->add($option2);
		
		$field = $dropdown->getFormField();
		
		
		$this->assertThat($field, $this->isInstanceOf('DropdownField'));
		$values = $field->getSource();
		
		$this->assertEquals(array('Option 1' => 'Option 1', 'Option 2' => 'Option 2'), $values);
	}
	
	function testEditableRadioField() {
		$radio = $this->objFromFixture('EditableRadioField', 'radio-field');
		
		$option1 = $this->objFromFixture('EditableOption', 'option-1');
		$option2 = $this->objFromFixture('EditableOption', 'option-2');
		
		$radio->Options()->add($option1);
		$radio->Options()->add($option2);
		
		$field = $radio->getFormField();
		
		$this->assertThat($field, $this->isInstanceOf('OptionsetField'));
		$values = $field->getSource();
		
		$this->assertEquals(array('Option 1' => 'Option 1', 'Option 2' => 'Option 2'), $values);
	}
}