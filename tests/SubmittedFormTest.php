<?php

class SubmittedFormTest extends FunctionalTest {

	static $fixture_file = 'userforms/tests/SubmittedFormTest.yml';
	
	function testReportSubmissions() {
		
	}
	
	function testCSVExport() {
		
	}
	
	function testdeletesubmission() {
		
	}
	
	function testdeletesubmissions() {
		
	}
	
	function testOnBeforeDeleteOfForm() {
		$field = $this->objFromFixture('SubmittedFormField', 'submitted-form-field-1');
		$form = $field->Parent();
		
		$this->assertEquals($form->FieldValues()->Count(), 2);
		$form->delete();
		
		$fields = DataObject::get('SubmittedFormField', "ParentID = '$form->ID'");
		
		$this->assertNull($fields);
	}
	
	function testGetFormattedValue() {
		$field = $this->objFromFixture('SubmittedFormField', 'submitted-form-field-1');
		
		$this->assertEquals('1', $field->getFormattedValue());
		
		$textarea = $this->objFromFixture('SubmittedFormField', 'submitted-textarea-1');
		
		$text = "I am here testing<br />\nTesting until I cannot<br />\nI love my testing";
		
		$this->assertEquals($text, $textarea->getFormattedValue());
	}
	
	function testFileGetLink() {
		$field = $this->objFromFixture('SubmittedFileField', 'submitted-file-1');

		// @todo add checks for if no file can be downloaded
		$this->assertContains('my-file.jpg', $field->getLink());
		
	}
	function testFileGetFormattedValue() {
		$field = $this->objFromFixture('SubmittedFileField', 'submitted-file-1');

		// @todo add checks for if no file can be downloaded
		$this->assertContains('Download File', $field->getFormattedValue());
	}
}


class SubmittedFormTest_Controller extends Controller {
	
	function ReportField() {
		return new Form($this, 'ReportField', new FieldSet(new SubmittedFormReportField('Report'), new FieldSet()));
	}
}
