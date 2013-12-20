<?php

/**
 * @package userforms
 */

class EditableFormFieldTest extends FunctionalTest {
	
	static $fixture_file = 'userforms/tests/EditableFormFieldTest.yml';

	protected $extraDataObjects = array(
		'ExtendedEditableFormField',
		'EditableFormFieldExtension'
	);
	
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
		$set = new ArrayList();
		
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
		// it has 1 rule - when ticked the checkbox hides the text field
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
		
		$this->assertEquals($text->getSettingName('Foo'), "Fields[". $text->ID ."][CustomSettings][Foo]");
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
	
	function testMultipleOptionPopulateFromPostData() {
		$dropdown = $this->objFromFixture('EditableDropdown','basic-dropdown');
		
		$data = array();
		
		foreach($dropdown->Options() as $option) {
			$orginal[$option->ID] = array(
				'Title' => $option->Title,
				'Sort' => $option->Sort
			);
			
			$data[$option->ID] = array(
				'Title' => 'New - '. $option->Title,
				'Sort' => $option->Sort + 1
			);
		}
		
		$dropdown->populateFromPostData($data);
		
		$count = $dropdown->Options()->Count();
		
		foreach($dropdown->Options() as $option) {
			$this->assertEquals($option->Title, 'New - '. $orginal[$option->ID]['Title']);
			$this->assertEquals($option->Sort, $orginal[$option->ID]['Sort'] + 1);
		}

		// remove the first one. can't assume by ID
		foreach($data as $key => $value) {
			unset($data[$key]);
			break;
		}

		$dropdown->populateFromPostData($data);
		
		$this->assertEquals($dropdown->Options()->Count(), $count-1);
	}
	
	function testEditableTextFieldConfiguration() {
//		$text = $this->objFromFixture('EditableTextField', 'basic-text');
		
//		$configuration = $text->getFieldConfiguration();

	}

    function testExtendedEditableFormField() {
        /** @var ExtendedEditableFormField $field */
        $field = $this->objFromFixture('ExtendedEditableFormField', 'extended-field');

        // Check db fields
        $dbFields = $field->stat('db');
        $this->assertTrue(array_key_exists('TestExtraField', $dbFields));
        $this->assertTrue(array_key_exists('TestValidationField', $dbFields));

        // Check Field Configuration
        $fieldConfiguration = $field->getFieldConfiguration();
        $extraField = $fieldConfiguration->dataFieldByName($field->getSettingName('TestExtraField'));
        $this->assertNotNull($extraField);

        // Check Validation Fields
        $fieldValidation = $field->getFieldValidationOptions();
        $validationField = $fieldValidation->dataFieldByName($field->getSettingName('TestValidationField'));
        $this->assertNotNull($validationField);
    }

}

/**
 * Class ExtendedEditableFormField
 * A base EditableFormFieldClass that will be extended with {@link EditableFormFieldExtension}
 * @mixin EditableFormFieldExtension
 */
class ExtendedEditableFormField extends EditableFormField implements TestOnly
{
    private static $extensions = array(
        'EditableFormFieldExtension'
    );
}

/**
 * Class EditableFormFieldExtension
 * Used for testing extensions to EditableFormField and the extended Fields methods
 * @property EditableFormField owner
 */
class EditableFormFieldExtension extends DataExtension implements TestOnly
{
    private static $db = array(
        'TestExtraField'      => 'Varchar',
        'TestValidationField' => 'Boolean'
    );

    public function updateFieldConfiguration(FieldList $fields)
    {
        $extraField = 'TestExtraField';
        $fields->push(TextField::create(
            $this->owner->getSettingName($extraField),
            'Test extra field',
            $this->owner->getSetting($extraField)
        ));
    }

    public function updateFieldValidationOptions(FieldList $fields)
    {
        $extraField = 'TestValidationField';
        $fields->push(CheckboxField::create(
            $this->owner->getSettingName($extraField),
            'Test validation field',
            $this->owner->getSetting($extraField)
        ));
    }
}
