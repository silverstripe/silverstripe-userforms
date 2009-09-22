<?php

/**
 * Various tests for the user defined forms.
 * Does not test the user interface in the admin.
 *
 * @todo Add more comprehensive tests
 * @package userforms
 */

class UserDefinedFormFieldTest extends SapphireTest {
	
	/**
	 * Basic Test creating all the editable form fields
	 */
	function testCreatingAllFields() {
		$fields = ClassInfo::subclassesFor('EditableFormField');
		foreach($fields as $field) {
			$object = new $field();
			$object->Name = "$field";
			$object->Title = "$field";
			$object->write();
			
			$this->assertEquals($field, $object->Name);
			$object->delete();
		}
	}
}