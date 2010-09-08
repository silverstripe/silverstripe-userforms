<?php

/**
 * @package userforms
 */

class EditableFormFieldTest extends FunctionalTest {
	
	static $fixture_file = 'userforms/tests/UserDefinedFormTest.yml';
	
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
	
	function testGettingAndSettingSettings() {
		$text = $this->objFromFixture('EditableTextField', 'basic-text');
		
		$this->logInWithPermission('ADMIN');
				
		$this->assertEquals($text->getSettings(), array());
		$text->setSetting('Test', 'Value');
		$text->write();
		
		$this->assertEquals($text->getSetting('Test'), 'Value');
		$this->assertEquals($text->getSettings(), array('Test' => 'Value'));
		
		$text->setSetting('Foo', 'Bar');
		$text->write();
		
		$this->assertEquals($text->getSetting('Foo'), 'Bar');
		$this->assertEquals($text->getSettings(), array('Test' => 'Value', 'Foo' => 'Bar'));
		
		// test overridding an existing setting
		$text->setSetting('Foo', 'Baz');
		$text->write();
		
		$this->assertEquals($text->getSetting('Foo'), 'Baz');
		$this->assertEquals($text->getSettings(), array('Test' => 'Value', 'Foo' => 'Baz'));
	}
	
	function testShowOnLoad() {
		$text = $this->objFromFixture('EditableTextField', 'basic-text');
		
		$this->logInWithPermission('ADMIN');	
		$this->assertTrue($text->getShowOnLoad());
		
		$text->setSetting('ShowOnLoad', 'Show');
		$this->assertTrue($text->getShowOnLoad());
		
		$text->setSetting('ShowOnLoad', 'Hide');
		$this->assertFalse($text->getShowOnLoad());
		
		$text->setSetting('ShowOnLoad', '');
		$this->assertTrue($text->getShowOnLoad());
	}

	
	function testPopulateFromPostData() {
		$this->logInWithPermission('ADMIN');
		$set = new DataObjectSet();
		
		$field = new EditableFormField();
		
		$data = array(
			'Title' => 'Field Title',
			'Default' => 'Default Value',
			'Sort' => '2',
			'Required' => 0,
			'CustomErrorMessage' => 'Custom'
		);
		
		$field->populateFromPostData($data);
		$set->push($field);
		$this->assertDOSEquals(array($data), $set);
		
		// test the custom settings
		$data['CustomSettings'] = array(
			'Foo' => 'Bar'
		);
		
		$checkbox = new EditableCheckbox();
		$checkbox->write();
		
		$checkbox->populateFromPostData(array('Title' => 'Checkbox'));
		
		$field->populateFromPostData($data);
		
		$this->assertEquals($field->getSettings(), array('Foo' => 'Bar'));

		$rule = array(
			'Display' => 'Hide',
			'ConditionField' => $checkbox->Name,
			'ConditionOption' => 'HasValue',
			'Value' => 6
		);
		
		// test the custom rules
		$data['CustomRules'] = array(
			'Rule1' => $rule
		);
		
		$field->populateFromPostData($data);
		
		$rules = unserialize($field->CustomRules);
		
		$this->assertEquals($rules[0], $rule);
	}
	
	function testCustomRules() {
		$this->logInWithPermission('ADMIN');
		$form = $this->objFromFixture('UserDefinedForm', 'custom-rules-form');

		$checkbox = $form->Fields()->find('ClassName', 'EditableCheckbox');
		$field = $form->Fields()->find('ClassName', 'EditableTextField');

		$rule = array(
			'Display' => 'Hide',
			'ConditionField' => $checkbox->Name,
			'ConditionOption' => 'HasValue',
			'Value' => 6
		);

		$data['CustomRules'] = array(
			'Rule1' => $rule
		);

		$field->populateFromPostData($data);
		
		$rules = $field->CustomRules();
		
		// form has 2 fields - a checkbox and a text field
		// it has 1 rule -  when ticked the checkbox hides the text field
		$this->assertEquals($rules->Count(), 1);

		// rules are ArrayDatas not dataobjects
		// $this->assertDOSEquals(array($rule), $rules);
		
		$checkboxRule = $rules->First();
		$this->assertEquals($checkboxRule->Display, 'Hide');
		$this->assertEquals($checkboxRule->ConditionField, $checkbox->Name);
		$this->assertEquals($checkboxRule->ConditionOption, 'HasValue');
		$this->assertEquals($checkboxRule->Value, '6');
		
		foreach($checkboxRule->Fields as $condition) {
			if($checkbox->Name == $condition->Name) {
				$this->assertTrue($condition->isSelected);
			}
			else {
				$this->assertFalse($condition->isSelected);
			}
		}
		
		$data['CustomRules'] = array(
			'Rule2' => array(
				'Display' => 'Hide',
				'ConditionField' => $checkbox->Name,
				'ConditionOption' => 'Blank'
			)
		);
		
		$field->populateFromPostData($data);
	
		$rules = $field->CustomRules();
		
		// test that saving additional rules deletes the old one
		$this->assertEquals($rules->Count(), 1);
		
	}
	
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
	
	function testTitleField() {
		$text = $this->objFromFixture('EditableTextField', 'basic-text');
		$this->logInWithPermission('ADMIN');
		
		$title = $text->TitleField();
		
		$this->assertThat($title, $this->isInstanceOf('TextField'));
		$this->assertEquals($title->Title(), "Enter Question");
		$this->assertEquals($title->Value(), "Basic Text Field");

		$member = Member::currentUser();
		$member->logOut();
		
		// read only version
		$title = $text->TitleField();
		
		$this->assertThat($title, $this->isInstanceOf('ReadonlyField'));
		$this->assertEquals($title->Title(), "Enter Question");
		$this->assertEquals($title->Value(), "Basic Text Field");
	}
	
	function testGettingFieldAndSettingNames() {
		$text = $this->objFromFixture('EditableTextField', 'basic-text');
		
		$this->assertEquals($text->getFieldName(), "Fields[". $text->ID ."]");
		$this->assertEquals($text->getFieldName('Setting'), "Fields[". $text->ID ."][Setting]");
		
		$this->assertEquals($text->getSettingFieldName('Foo'), "Fields[". $text->ID ."][CustomSettings][Foo]");
	}
}