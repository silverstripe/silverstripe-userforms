<?php

/** 
 * Tests covering the form editor / builder and 
 * some of the user interface 
 * 
 * @package userforms
 */

class UserDefinedFormEditorTest extends FunctionalTest {

	protected $form;
	
	function setUp() {
		parent::setUp();
		$this->logInWithPermission('ADMIN');
		
		$this->form = new UserDefinedForm();
		$this->form->write();
	}
	
	function testPublishingNormalField() {		
		$id = $this->form->ID;
		
		// test a normal field
		$field = new EditableFormField();
		$field->write();
		
		$this->form->Fields()->add($field);
		
		// upon adding it, it shouldn't  be on the live site
		$live = Versioned::get_one_by_stage("UserDefinedForm", "Live", "\"UserDefinedForm_Live\".\"ID\" = $id");
		$this->assertFalse($live);
		
		// upon publishing the field should exist
		$this->form->doPublish();
		$live = Versioned::get_one_by_stage("UserDefinedForm", "Live", "\"UserDefinedForm_Live\".\"ID\" = $id");
		$this->assertEquals($live->Fields()->Count(), 1);
	}
	
	function testPublishingMultipleOptions() {
		$id = $this->form->ID;
		$this->form->Fields()->removeAll();
		
		// test a editable option field
		$dropdown = new EditableDropdown();
		$dropdown->write();
		
		$checkbox = new EditableCheckboxGroupField();
		$checkbox->write();
		
		$option = new EditableOption();
		$option->write();
		
		$option2 = new EditableOption();
		$option2->write();
		
		$dropdown->Options()->add($option);
		$checkbox->Options()->add($option2);
		
		$this->form->Fields()->add($dropdown);
		$this->form->Fields()->add($checkbox);
		
		// upon adding it, it shouldn't  be on the live site
		$live = Versioned::get_one_by_stage("UserDefinedForm", "Live", "\"UserDefinedForm_Live\".\"ID\" = $id");
		$this->assertFalse($live);
		
		// and when published it should exist and the option
		$this->form->doPublish();
		$live = Versioned::get_one_by_stage("UserDefinedForm", "Live", "\"UserDefinedForm_Live\".\"ID\" = $id");
		$this->assertEquals($live->Fields()->Count(), 2);
		
		// check they have options attached
		foreach($live->Fields() as $field) {
			$this->assertEquals($field->Options()->Count(), 1);
		}
	}
	
	function testUnpublishing() {
		$id = $this->form->ID;
		$this->form->Fields()->removeAll();
		$this->form->Fields()->add(new EditableFormField());
		$this->form->doUnPublish();
		$live = Versioned::get_one_by_stage("UserDefinedForm", "Live", "\"UserDefinedForm_Live\".\"ID\" = $id");
		$stage = Versioned::get_one_by_stage("UserDefinedForm", "Stage", "\"UserDefinedForm\".\"ID\" = $id");
		$this->assertEquals($live, false);
		$this->assertEquals($stage->Fields()->Count(), 1);
	}
	
	function testDuplicatingPage() {
		$this->form->Fields()->add(new EditableFormField());
		$form_copy = $this->form->duplicate();
		
		$this->assertEquals($this->form->Fields()->Count(), $form_copy->Fields()->Count());
	}
	
	function tearDown() {		
		$this->form->delete();

		parent::tearDown();
	}
}