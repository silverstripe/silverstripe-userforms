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
		$whereClause = defined('Database::USE_ANSI_SQL') ? "\"UserDefinedForm_Live\".\"ID\" = $id" : "UserDefinedForm_Live.ID = $id";
		$live = Versioned::get_one_by_stage("UserDefinedForm", "Live", $whereClause);
		$this->assertEquals($live->Fields()->Count(), 1);
	}
	
	function testUnpublishing() {
		$id = $this->form->ID;
		$this->form->Fields()->removeAll();
		$this->form->Fields()->add(new EditableFormField());
		$this->form->doUnPublish();
		$whereClauseStage = defined('Database::USE_ANSI_SQL') ? "\"UserDefinedForm\".\"ID\" = $id" : "UserDefinedForm.ID = $id";
		$whereClauseLive = defined('Database::USE_ANSI_SQL') ? "\"UserDefinedForm_Live\".\"ID\" = $id" : "UserDefinedForm_Live.ID = $id";
		$live = Versioned::get_one_by_stage("UserDefinedForm", "Live", $whereClauseLive);
		$stage = Versioned::get_one_by_stage("UserDefinedForm", "Stage", $whereClauseStage);
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