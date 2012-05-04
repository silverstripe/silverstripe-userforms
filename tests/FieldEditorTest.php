<?php

/** 
 * Tests covering the form editor / builder and 
 * some of the user interface 
 * 
 * @package userforms
 */

class FieldEditorTest extends FunctionalTest {
	
	static $fixture_file = 'userforms/tests/UserDefinedFormTest.yml';
	
	protected $editor;
	
	function setUp() {
		parent::setUp();
		
		$form = $this->objFromFixture('UserDefinedForm', 'basic-form-page');

		$controller = new FieldEditorTest_Controller($form);
		
		$fields = $controller->Form()->Fields();
		
		$this->editor = $fields->fieldByName('Fields');	
	}
	
	function testSaveInto() {
		$this->logInWithPermission('ADMIN');
		
		// @todo
	}
	
	function testAddField() {
		$this->logInWithPermission('ADMIN');
		
	//	Debug::show($this->editor->addfield());
	}
}

class FieldEditorTest_Controller extends Controller {
	
	public function Form() {
		return new Form($this, 'Form', new FieldList(new FieldEditor('Fields')), new FieldList());
	}
}