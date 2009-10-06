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
		
		$this->form = new UserDefinedForm();
		$this->form->write();
	}
	
	function testPublishing() {
		$id = $this->form->ID;
		$this->form->Fields()->add(new EditableFormField());
		$this->form->doPublish();
		$live =  Versioned::get_one_by_stage("UserDefinedForm", "Live", "UserDefinedForm.ID = $id");
		$this->assertEquals($live->Fields()->Count(), 1);
	}
	
	function testUnpublishing() {
		$id = $this->form->ID;
		$this->form->Fields()->removeAll();
		$this->form->Fields()->add(new EditableFormField());
		$this->form->doUnPublish();
		$live =  Versioned::get_one_by_stage("UserDefinedForm", "Live", "UserDefinedForm.ID = $id");
		$stage =  Versioned::get_one_by_stage("UserDefinedForm", "Stage", "UserDefinedForm.ID = $id");
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