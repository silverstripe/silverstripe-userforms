<?php

namespace SilverStripe\UserForms\Tests\Control;

use SilverStripe\Core\Config\Config;
use SilverStripe\Dev\CSSContentParser;
use SilverStripe\Dev\FunctionalTest;
use SilverStripe\Dev\TestOnly;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\FormAction;
use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Member;
use SilverStripe\UserForms\Model\EditableFormField\EditableTextField;
use SilverStripe\UserForms\Model\Submission\SubmittedFormField;
use SilverStripe\UserForms\Model\UserDefinedForm;
use SilverStripe\UserForms\Model\UserDefinedFormController;
use SilverStripe\View\ArrayData;
use SilverStripe\View\SSViewer;

/**
 * @package userforms
 */
class UserDefinedFormControllerTest extends FunctionalTest
{
    protected static $fixture_file = 'UserDefinedFormControllerTest.yml';

    protected static $use_draft_site = true;

    protected function setUp()
    {
        parent::setUp();

        Config::modify()->set(SSViewer::class, 'themes', ['simple', '$default']);
    }

    public function testProcess()
    {
        $form = $this->setupFormFrontend();

        $controller = new UserDefinedFormController($form);

        $this->autoFollowRedirection = false;
        $this->clearEmails();

        // load the form
        $this->get($form->URLSegment);

        $field = $this->objFromFixture(EditableTextField::class, 'basic-text');

        $response = $this->submitForm('UserForm_Form_' . $form->ID, null, [$field->Name => 'Basic Value']);

        // should have a submitted form field now
        $submitted = DataObject::get(SubmittedFormField::class, "\"Name\" = 'basic-text-name'");
        $this->assertDOSAllMatch(
            [
                'Name' => 'basic-text-name',
                'Value' => 'Basic Value',
                'Title' => 'Basic Text Field'
            ],
            $submitted
        );

        // check emails
        $this->assertEmailSent('test@example.com', 'no-reply@example.com', 'Email Subject');
        $email = $this->findEmail('test@example.com', 'no-reply@example.com', 'Email Subject');

        // assert that the email has the field title and the value html email
        $parser = new CSSContentParser($email['Content']);
        $title = $parser->getBySelector('strong');

        $this->assertEquals('Basic Text Field', (string) $title[0], 'Email contains the field name');

        $value = $parser->getBySelector('dd');
        $this->assertEquals('Basic Value', (string) $value[0], 'Email contains the value');

        // no html
        $this->assertEmailSent('nohtml@example.com', 'no-reply@example.com', 'Email Subject');
        $nohtml = $this->findEmail('nohtml@example.com', 'no-reply@example.com', 'Email Subject');

        $this->assertContains('Basic Text Field: Basic Value', $nohtml['Content'], 'Email contains no html');

        // no data
        $this->assertEmailSent('nodata@example.com', 'no-reply@example.com', 'Email Subject');
        $nodata = $this->findEmail('nodata@example.com', 'no-reply@example.com', 'Email Subject');

        $parser = new CSSContentParser($nodata['Content']);
        $list = $parser->getBySelector('dl');

        $this->assertFalse(isset($list[0]), 'Email contains no fields');

        // check to see if the user was redirected (301)
        $this->assertEquals($response->getStatusCode(), 302);
        $this->assertStringEndsWith('finished#uff', $response->getHeader('Location'));
    }

    public function testValidation()
    {
        $form = $this->setupFormFrontend('email-form');

        // Post with no fields
        $this->get($form->URLSegment);
        $response = $this->submitForm('UserForm_Form_' . $form->ID, null, []);
        $this->assertPartialMatchBySelector(
            '.field .message',
            ['This field is required']
        );

        // Post with all fields, but invalid email
        $this->get($form->URLSegment);
        $this->submitForm('UserForm_Form_' . $form->ID, null, [
            'required-email' => 'invalid',
            'required-text' => 'bob'
        ]);
        $this->assertPartialMatchBySelector(
            '.field .message',
            ['Please enter an email address']
        );

        // Post with only required
        $this->get($form->URLSegment);
        $this->submitForm('UserForm_Form_' . $form->ID, null, [
            'required-text' => 'bob'
        ]);
        $this->assertPartialMatchBySelector(
            'p',
            ["Thanks, we've received your submission."]
        );
    }

    public function testFinished()
    {
        $form = $this->setupFormFrontend();

        // set formProcessed and SecurityID to replicate the form being filled out
        $this->session()->set('SecurityID', 1);
        $this->session()->set('FormProcessed', 1);

        $response = $this->get($form->URLSegment.'/finished');

        $this->assertContains($form->OnCompleteMessage, $response->getBody());
    }

    public function testAppendingFinished()
    {
        $form = $this->setupFormFrontend();

        // replicate finished being added to the end of the form URL without the form being filled out
        $this->session()->set('SecurityID', 1);
        $this->session()->set('FormProcessed', null);

        $response = $this->get($form->URLSegment.'/finished');

        $this->assertNotContains($form->OnCompleteMessage, $response->getBody());
    }

    public function testForm()
    {
        $form = $this->objFromFixture(UserDefinedForm::class, 'basic-form-page');

        $controller = new UserDefinedFormController($form);

        // test form
        $this->assertEquals($controller->Form()->getName(), 'Form_' . $form->ID, 'The form is referenced as Form');
        $this->assertEquals($controller->Form()->Fields()->Count(), 1); // disabled SecurityID token fields
        $this->assertEquals($controller->Form()->Actions()->Count(), 1);
        $this->assertEquals(count($controller->Form()->getValidator()->getRequired()), 0);

        $requiredForm = $this->objFromFixture(UserDefinedForm::class, 'validation-form');
        $controller = new UserDefinedFormController($requiredForm);

        $this->assertEquals($controller->Form()->Fields()->Count(), 1); // disabled SecurityID token fields
        $this->assertEquals($controller->Form()->Actions()->Count(), 1);
        $this->assertEquals(count($controller->Form()->getValidator()->getRequired()), 1);
    }

    public function testGetFormFields()
    {
        // generating the fieldset of fields
        $form = $this->objFromFixture(UserDefinedForm::class, 'basic-form-page');

        $controller = new UserDefinedFormController($form);

        $formSteps = $controller->Form()->getFormFields();
        $firstStep = $formSteps->first();

        $this->assertEquals($formSteps->Count(), 1);
        $this->assertEquals($firstStep->getChildren()->Count(), 1);

        // custom error message on a form field
        $requiredForm = $this->objFromFixture(UserDefinedForm::class, 'validation-form');
        $controller = new UserDefinedFormController($requiredForm);

        Config::modify()->set(UserDefinedForm::class, 'required_identifier', '*');

        $formSteps = $controller->Form()->getFormFields();
        $firstStep = $formSteps->first();
        $firstField = $firstStep->getChildren()->first();

        $this->assertEquals('Custom Error Message', $firstField->getCustomValidationMessage());
        $this->assertEquals($firstField->Title(), 'Required Text Field <span class=\'required-identifier\'>*</span>');

        // test custom right title
        $field = $form->Fields()->limit(1, 1)->First();
        $field->RightTitle = 'Right Title';
        $field->write();

        $controller = new UserDefinedFormController($form);
        $formSteps = $controller->Form()->getFormFields();
        $firstStep = $formSteps->first();

        $this->assertEquals($firstStep->getChildren()->First()->RightTitle(), "Right Title");

        // test empty form
        $emptyForm = $this->objFromFixture(UserDefinedForm::class, 'empty-form');
        $controller = new UserDefinedFormController($emptyForm);

        $this->assertFalse($controller->Form()->getFormFields()->exists());
    }

    public function testGetFormActions()
    {
        // generating the fieldset of actions
        $form = $this->objFromFixture(UserDefinedForm::class, 'basic-form-page');

        $controller = new UserDefinedFormController($form);
        $actions = $controller->Form()->getFormActions();

        // by default will have 1 submit button which links to process
        $expected = new FieldList(new FormAction('process', 'Submit'));
        $expected->setForm($controller->Form());

        $this->assertEquals($actions, $expected);

        // the custom popup should have a reset button and a custom text
        $custom = $this->objFromFixture(UserDefinedForm::class, 'form-with-reset-and-custom-action');
        $controller = new UserDefinedFormController($custom);
        $actions = $controller->Form()->getFormActions();

        $expected = new FieldList(new FormAction('process', 'Custom Button'));
        $expected->push(FormAction::create('clearForm', 'Clear')->setAttribute('type', 'reset'));
        $expected->setForm($controller->Form());

        $this->assertEquals($actions, $expected);
    }

    public function testRenderingIntoFormTemplate()
    {
        $form = $this->setupFormFrontend();

        $this->logInWithPermission('ADMIN');
        $form->Content = 'This is some content without a form nested between it';
        $form->publishRecursive();

        $controller = new UserDefinedFormController($form);

        // check to see if $Form is replaced to inside the content
        $index = new ArrayData($controller->index());
        $parser = new CSSContentParser($index->renderWith(__CLASS__));

        $this->checkTemplateIsCorrect($parser, $form);
    }

    public function testRenderingIntoTemplateWithSubstringReplacement()
    {
        $form = $this->setupFormFrontend();

        $controller = new UserDefinedFormController($form);

        // check to see if $Form is replaced to inside the content
        $index = new ArrayData($controller->index());
        $parser = new CSSContentParser($index->renderWith(__CLASS__));

        $this->checkTemplateIsCorrect($parser, $form);
    }
    /**
     * Publish a form for use on the frontend
     *
     * @param string $fixtureName
     * @return UserDefinedForm
     */
    protected function setupFormFrontend($fixtureName = 'basic-form-page')
    {
        $form = $this->objFromFixture(UserDefinedForm::class, $fixtureName);
        $this->logInWithPermission('ADMIN');

        $form->publishRecursive();

        $member = Member::currentUser();
        $member->logOut();

        return $form;
    }

    public function checkTemplateIsCorrect($parser, $form)
    {
        $this->assertArrayHasKey(0, $parser->getBySelector('form#UserForm_Form_' . $form->ID));

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
