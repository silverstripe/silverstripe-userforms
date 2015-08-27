<?php

/**
 * @package userforms
 */

class EditableFormFieldTest extends FunctionalTest {
	
	static $fixture_file = 'userforms/tests/EditableFormFieldTest.yml';
	
	function testFormFieldPermissions() {
		$text = $this->objFromFixture('EditableTextField', 'basic-text');
		
		$this->logInWithPermission('ADMIN');
		$this->assertTrue($text->canEdit());
		$this->assertTrue($text->canDelete());
		
		$text->setReadonly(true);
		$this->assertFalse($text->canEdit());
		$this->assertFalse($text->canDelete());
		
		$text->setReadonly(false);
		$this->assertTrue($text->canEdit());
		$this->assertTrue($text->canDelete());
		
		$member = Member::currentUser();
		$member->logout();
		
		$this->logInWithPermission('SITETREE_VIEW_ALL');
		$text->setReadonly(false);
		$this->assertFalse($text->canEdit());
		$this->assertFalse($text->canDelete());
		
		$text->setReadonly(true);
		$this->assertFalse($text->canEdit());
		$this->assertFalse($text->canDelete());
	}
	
	function testCustomRules() {
		$this->logInWithPermission('ADMIN');
		$form = $this->objFromFixture('UserDefinedForm', 'custom-rules-form');

		$checkbox = $form->Fields()->find('ClassName', 'EditableCheckbox');
		$field = $form->Fields()->find('ClassName', 'EditableTextField');

		$rules = $checkbox->DisplayRules();

		// form has 2 fields - a checkbox and a text field
		// it has 1 rule - when ticked the checkbox hides the text field
		$this->assertEquals($rules->Count(), 1);

		$checkboxRule = $rules->First();
		$checkboxRule->ConditionFieldID = $field->ID;

		$this->assertEquals($checkboxRule->Display, 'Hide');
		$this->assertEquals($checkboxRule->ConditionOption, 'HasValue');
		$this->assertEquals($checkboxRule->FieldValue, '6');
	}
	
	function testEditableDropdownField() {
		$dropdown = $this->objFromFixture('EditableDropdown', 'basic-dropdown');
		
		$field = $dropdown->getFormField();
		
		
		$this->assertThat($field, $this->isInstanceOf('DropdownField'));
		$values = $field->getSource();
		
		$this->assertEquals(array('Option 1' => 'Option 1', 'Option 2' => 'Option 2'), $values);
	}
	
	function testEditableRadioField() {
		$radio = $this->objFromFixture('EditableRadioField', 'radio-field');
	
		$field = $radio->getFormField();
		
		$this->assertThat($field, $this->isInstanceOf('OptionsetField'));
		$values = $field->getSource();
		
		$this->assertEquals(array('Option 5' => 'Option 5', 'Option 6' => 'Option 6'), $values);
	}
	
	function testMultipleOptionDuplication() {
		$dropdown = $this->objFromFixture('EditableDropdown','basic-dropdown');
		
		$clone = $dropdown->duplicate();

		$this->assertEquals($clone->Options()->Count(), $dropdown->Options()->Count());
		
		foreach($clone->Options() as $option) {
			$orginal = $dropdown->Options()->find('Title', $option->Title);
			
			$this->assertEquals($orginal->Sort, $option->Sort);
		}
	}

	public function testFileField() {
		$fileField = $this->objFromFixture('EditableFileField', 'file-field');
		$formField = $fileField->getFormField();

		$this->assertContains('jpg', $formField->getValidator()->getAllowedExtensions());
		$this->assertNotContains('notallowedextension', $formField->getValidator()->getAllowedExtensions());
	}

	public function testFileFieldAllowedExtensionsBlacklist() {
		Config::inst()->update('EditableFileField', 'allowed_extensions_blacklist', array('jpg'));
		$fileField = $this->objFromFixture('EditableFileField', 'file-field');
		$formField = $fileField->getFormField();

		$this->assertNotContains('jpg', $formField->getValidator()->getAllowedExtensions());
	}

}
