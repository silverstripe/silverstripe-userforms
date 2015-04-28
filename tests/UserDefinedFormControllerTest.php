<?php

/**
 * @package userforms
 */

class UserDefinedFormControllerTest extends FunctionalTest {
	
	static $fixture_file = 'userforms/tests/UserDefinedFormTest.yml';

	function testProcess() {
		$form = $this->setupFormFrontend();
		
		$controller = new UserDefinedFormControllerTest_Controller($form);
		
		$this->autoFollowRedirection = false;
		$this->clearEmails();
		
		// load the form
		$this->get($form->URLSegment);	
		$response = $this->submitForm('Form_Form', null, array('basic-text-name' => 'Basic Value'));

		// should have a submitted form field now
		$submitted = DataObject::get('SubmittedFormField', "\"Name\" = 'basic-text-name'");
		$this->assertDOSAllMatch(array('Name' => 'basic-text-name', 'Value' => 'Basic Value', 'Title' => 'Basic Text Field'), $submitted);
	
		// check emails
		$this->assertEmailSent('test@example.com', 'no-reply@example.com', 'Email Subject');	
		$email = $this->findEmail('test@example.com', 'no-reply@example.com', 'Email Subject');
		
		// assert that the email has the field title and the value html email
		$parser = new CSSContentParser($email['content']);
		$title = $parser->getBySelector('strong');
		
		$this->assertEquals('Basic Text Field', (string) $title[0], 'Email contains the field name');
		
		$value = $parser->getBySelector('dd');
		$this->assertEquals('Basic Value', (string) $value[0], 'Email contains the value');
		
		// no html
		$this->assertEmailSent('nohtml@example.com', 'no-reply@example.com', 'Email Subject');	
		$nohtml = $this->findEmail('nohtml@example.com', 'no-reply@example.com', 'Email Subject');

		$this->assertContains('Basic Text Field: Basic Value', $nohtml['content'], 'Email contains no html');
	
		// no data
		$this->assertEmailSent('nodata@example.com', 'no-reply@example.com', 'Email Subject');	
		$nodata = $this->findEmail('nodata@example.com', 'no-reply@example.com', 'Email Subject');
		
		$parser = new CSSContentParser($nodata['content']);
		$list = $parser->getBySelector('dl');
		
		$this->assertFalse(isset($list[0]), 'Email contains no fields');
		
		// check to see if the user was redirected (301)
		$this->assertEquals($response->getStatusCode(), 302);
		$this->assertStringEndsWith('finished#uff', $response->getHeader('Location'));
	}
	
	function testFinished() {
		$form = $this->setupFormFrontend();

		// set formProcessed and SecurityID to replicate the form being filled out
		$this->session()->inst_set('SecurityID', 1);
		$this->session()->inst_set('FormProcessed', 1);

		$response = $this->get($form->URLSegment.'/finished');
		
		$this->assertContains($form->OnCompleteMessage ,$response->getBody());
	}

	function testAppendingFinished() {
		$form = $this->setupFormFrontend();

		// replicate finished being added to the end of the form URL without the form being filled out
		$this->session()->inst_set('SecurityID', 1);
		$this->session()->inst_set('FormProcessed', null);

		$response = $this->get($form->URLSegment.'/finished');
		
		$this->assertNotContains($form->OnCompleteMessage ,$response->getBody());
	}
	
	function testForm() {
		$form = $this->objFromFixture('UserDefinedForm', 'basic-form-page');
		
		$controller = new UserDefinedFormControllerTest_Controller($form);
		
		// test form 
		$this->assertEquals($controller->Form()->getName(), 'Form', 'The form is referenced as Form');
		$this->assertEquals($controller->Form()->Fields()->Count(), 1); // disabled SecurityID token fields
		$this->assertEquals($controller->Form()->Actions()->Count(), 1);
		$this->assertEquals(count($controller->Form()->getValidator()->getRequired()), 0);

		$requiredForm = $this->objFromFixture('UserDefinedForm', 'validation-form');
		$controller = new UserDefinedFormControllerTest_Controller($requiredForm);

		$this->assertEquals($controller->Form()->Fields()->Count(), 1); // disabled SecurityID token fields
		$this->assertEquals($controller->Form()->Actions()->Count(), 1);
		$this->assertEquals(count($controller->Form()->getValidator()->getRequired()), 1);
	}
	
	function testGetFormFields() {
		// generating the fieldset of fields
		$form = $this->objFromFixture('UserDefinedForm', 'basic-form-page');
		
		$controller = new UserDefinedFormControllerTest_Controller($form);
		
		$fields = $controller->getFormFields();
		
		$this->assertEquals($fields->Count(), 1);
		
		// custom error message on a form field
		$requiredForm = $this->objFromFixture('UserDefinedForm', 'validation-form');
		$controller = new UserDefinedFormControllerTest_Controller($requiredForm);
		
		UserDefinedForm::config()->required_identifier = "*";
		
		$fields = $controller->getFormFields();
		
		$this->assertEquals($fields->First()->getCustomValidationMessage()->getValue(), 'Custom Error Message');
		$this->assertEquals($fields->First()->Title(), 'Required Text Field <span class=\'required-identifier\'>*</span>');
		
		// test custom right title
		$field = $form->Fields()->First();
		$field->setSetting('RightTitle', 'Right Title');
		$field->write();
		
		$controller = new UserDefinedFormControllerTest_Controller($form);
		$fields = $controller->getFormFields();

		$this->assertEquals($fields->First()->RightTitle(), "Right Title");
		
		// test empty form
		$emptyForm = $this->objFromFixture('UserDefinedForm', 'empty-form');
		$controller = new UserDefinedFormControllerTest_Controller($emptyForm);
		
		$this->assertFalse($controller->Form());
	}
	
	function testGetFormActions() {
		// generating the fieldset of actions
		$form = $this->objFromFixture('UserDefinedForm', 'basic-form-page');
		
		$controller = new UserDefinedFormControllerTest_Controller($form);
		$actions = $controller->getFormActions();
		
		// by default will have 1 submit button which links to process
		$expected = new FieldList(new FormAction('process', 'Submit'));
		
		$this->assertEquals($actions, $expected);
		
		// the custom popup should have a reset button and a custom text
		$custom = $this->objFromFixture('UserDefinedForm', 'form-with-reset-and-custom-action');
		$controller = new UserDefinedFormControllerTest_Controller($custom);
		
		$actions = $controller->getFormActions();

		$expected = new FieldList(new FormAction('process', 'Custom Button'));
		$expected->push(new ResetFormAction("clearForm", "Clear"));
		
		$this->assertEquals($actions, $expected);
	}
	
	function testArrayToJson() {
		$array = array('1' => 'one', '2' => 'two');
		$string = "{\n1:\"one\", 2:\"two\"\n}\n";
		$form = new UserDefinedFormControllerTest_Controller();
		$this->assertEquals($form->array2json($array), $string);
	}
	
	
	function testRenderingIntoFormTemplate() {
		$form = $this->setupFormFrontend();
		
		$form->Content = 'This is some content without a form nested between it';
		$form->doPublish();
		
		$controller = new UserDefinedFormControllerTest_Controller($form);
		
		// check to see if $Form is replaced to inside the content
		$index = new ArrayData($controller->index());
		$parser = new CSSContentParser($index->renderWith(array('UserDefinedFormControllerTest')));

		$this->checkTemplateIsCorrect($parser);
	}
	
	function testRenderingIntoTemplateWithSubstringReplacement() {
		$form = $this->setupFormFrontend();
		
		$controller = new UserDefinedFormControllerTest_Controller($form);
		
		// check to see if $Form is replaced to inside the content
		$index = new ArrayData($controller->index());
		$parser = new CSSContentParser($index->renderWith(array('UserDefinedFormControllerTest')));
		
		$this->checkTemplateIsCorrect($parser);
	}
	
	function setupFormFrontend() {
		$form = $this->objFromFixture('UserDefinedForm', 'basic-form-page');
		$this->logInWithPermission('ADMIN');
		
		$form->doPublish();

		$member = Member::currentUser();
		$member->logOut();
		
		return $form;
	}
	
	function checkTemplateIsCorrect($parser) {
		$this->assertArrayHasKey(0, $parser->getBySelector('form#Form_Form'));
		
		// check for the input
		$this->assertArrayHasKey(0, $parser->getBySelector('input.text'));
		
		// check for the label and the text
		$label = $parser->getBySelector('label.left');
		$this->assertArrayHasKey(0, $label);
		
		$this->assertEquals((string) $label[0][0], "Basic Text Field", "Label contains correct field name");
		
		// check for the action
		$action = $parser->getBySelector('input.action');
		$this->assertArrayHasKey(0, $action);
		
		$this->assertEquals((string) $action[0]['value'], "Submit", "Submit button has default text");
	}
}

class UserDefinedFormControllerTest_Controller extends UserDefinedForM_Controller implements TestOnly {
	
	/**
	 * Overloaded to avoid inconsistencies between 2.4.2 and 2.4.3 (disables all security tokens in unit tests by default)
	 */
	function Form() {
		$form = parent::Form();
		
		if($form) $form->disableSecurityToken();
		
		return $form;
	}
	
}
