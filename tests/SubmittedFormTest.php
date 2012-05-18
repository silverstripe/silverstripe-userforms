<?php

/**
 * @package userforms
 */	
class SubmittedFormTest extends FunctionalTest {

	static $fixture_file = 'userforms/tests/SubmittedFormTest.yml';
	
	protected $controller, $form, $page, $field;
	
	function setUp() {
		parent::setUp();
		$this->page = $this->objFromFixture('UserDefinedForm', 'popular-form');
		
		$this->controller = new SubmittedFormTest_Controller($this->page);
		$this->form = $this->controller->Form();
		$this->field = $this->form->Fields()->dataFieldByName('Report');
	}
	
	function testSubmissions() {
		$submissions = $this->field->getSubmissions();

		$this->assertEquals($submissions->TotalPages(), 2);
		$this->assertEquals($submissions->getTotalItems(), 11);
	}
	
	function testGetMoreSubmissions() {
		$template = $this->field->getMoreSubmissions();
		$parser = new CSSContentParser($template);
		// check to ensure that the pagination exists
		$pagination = $parser->getBySelector('.userforms-submissions-pagination');

		$this->assertEquals(str_replace("\n", ' ',(string) $pagination[0]->span), "Pages:");
		$this->assertEquals(str_replace("\n", ' ',(string) $pagination[0]->a), "2");

		// ensure the actions exist
		$actions = $parser->getBySelector('.userforms-submission-actions');
		$this->assertEquals(count($actions[0]->li), 2);
		
		// submissions
		$submissions = $parser->getBySelector('.userform-submission');
		$this->assertEquals(count($submissions), 10);
	}

	function testCSVExport() {
		$export = $this->field->export($this->page->ID);

		// Pretend we are opening via file
		$fp = fopen('php://memory', 'w+');
		fwrite($fp, $export);
		rewind($fp);

		$data = array();
		while($data[] = fgetcsv($fp));
		array_pop($data);
		fclose($fp);

		// Check the headers are fine and include every legacy field. They should also be ordered
		// according to the latest form layout.
		$this->assertEquals($data[0], array(
			'First', 'Submitted Title 2', 'Submitted Title', 'Submitted'
		));

		// Check the number of records in the export
		$this->assertEquals(count($data), 12);
		
		// Make sure the number of columns matches
		$this->assertEquals(count($data[1]), 4);
		$this->assertEquals(count($data[2]), 4);
		$this->assertEquals(count($data[3]), 4);
		$this->assertEquals(count($data[11]), 4);

		// Specific value tests
		$this->assertEquals($data[1][1], 'quote " and comma , test');
		$this->assertEquals($data[1][2], 'Value 1');
		$this->assertEquals($data[2][1], 'Value 2');

		$this->assertEquals($data[3][1], "multi\nline\ntest");
		
		$this->assertEquals($data[11][0], 'First');
		$this->assertEquals($data[11][1], 'Second');
	}
	
	function testdeletesubmission() {
		$submission = $this->objFromFixture('SubmittedForm', 'long-1');
		
		$count = $this->page->Submissions()->Count();
		$this->assertTrue($this->field->deletesubmission($submission->ID));
		
		$this->assertEquals($count - 1, $this->page->Submissions()->Count());
		
		$this->assertFalse($this->field->deletesubmission(-1));
	}
	
	function testdeletesubmissions() {
		$this->assertTrue($this->field->deletesubmissions($this->page->ID));
		
		$this->assertEquals($this->page->Submissions()->Count(), 0);
	}
	
	function testOnBeforeDeleteOfForm() {
		$field = $this->objFromFixture('SubmittedFormField', 'submitted-form-field-1');
		$form = $field->Parent();
		
		$this->assertEquals($form->Values()->Count(), 2);
		$form->delete();
		
		$fields = DataObject::get('SubmittedFormField', "\"ParentID\" = '$form->ID'");
		
		$this->assertEquals(array(), $fields->toArray());
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


class SubmittedFormTest_Controller extends ContentController {
	
	function Form() {
		$form = new Form($this, 'Form', new FieldList(new SubmittedFormReportField('Report')), new FieldList(new FormAction('Submit')));

		$form->loadDataFrom($this->data());
		
		return $form;
	}
	
	function forTemplate() {
		return $this->renderWith(array('ContentController'));
	}
}
