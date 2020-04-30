<?php

namespace SilverStripe\UserForms\Tests\Control;

use SilverStripe\Assets\Dev\TestAssetStore;
use SilverStripe\Assets\File;
use SilverStripe\Assets\Folder;
use SilverStripe\Assets\Storage\AssetStore;
use SilverStripe\Assets\Upload_Validator;
use InvalidArgumentException;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Control\Session;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\CSSContentParser;
use SilverStripe\Dev\FunctionalTest;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\FormAction;
use SilverStripe\ORM\DataObject;
use SilverStripe\Security\InheritedPermissions;
use SilverStripe\UserForms\Control\UserDefinedFormController;
use SilverStripe\UserForms\Model\EditableFormField;
use SilverStripe\UserForms\Model\EditableFormField\EditableFileField;
use SilverStripe\UserForms\Model\EditableFormField\EditableTextField;
use SilverStripe\UserForms\Model\Recipient\EmailRecipient;
use SilverStripe\UserForms\Model\Submission\SubmittedFormField;
use SilverStripe\UserForms\Model\UserDefinedForm;
use SilverStripe\View\ArrayData;
use SilverStripe\View\SSViewer;

/**
 * @package userforms
 */
class UserDefinedFormControllerTest extends FunctionalTest
{
    protected static $fixture_file = '../UserFormsTest.yml';

    protected static $use_draft_site = true;

    protected static $disable_themes = true;

    protected function setUp()
    {
        parent::setUp();

        // Set backend and base url
        TestAssetStore::activate('AssetStoreTest');

        Config::modify()->merge(SSViewer::class, 'themes', ['simple', '$default']);
        $submissionFolder = Folder::find('Form-submissions');
        if ($submissionFolder) {
            $submissionFolder->delete();
        }

        foreach (Folder::get() as $folder) {
            $folder->publishSingle();
        }
    }

    public function tearDown()
    {
        TestAssetStore::reset();
        parent::tearDown();
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
        $submitted = DataObject::get(SubmittedFormField::class, "\"Name\" = 'basic_text_name'");
        $this->assertListAllMatch(
            [
                'Name' => 'basic_text_name',
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

        $this->assertEmpty($list, 'Email contains no fields');

        // check to see if the user was redirected (301)
        $this->assertEquals($response->getStatusCode(), 302);
        $location = $response->getHeader('Location');
        $this->assertContains('finished', $location);
        $this->assertStringEndsWith('#uff', $location);

        // check that multiple email addresses are supported in to and from
        $this->assertEmailSent(
            'test1@example.com; test2@example.com',
            'test3@example.com; test4@example.com',
            'Test Email'
        );
    }

    public function testValidation()
    {
        $form = $this->setupFormFrontend('email-form');

        // Post with no fields
        $this->get($form->URLSegment);
        /** @var HTTPResponse $response */
        $response = $this->submitForm('UserForm_Form_' . $form->ID, null, []);
        $this->assertContains('This field is required', $response->getBody());

        // Post with all fields, but invalid email
        $this->get($form->URLSegment);
        /** @var HTTPResponse $response */
        $response = $this->submitForm('UserForm_Form_' . $form->ID, null, [
            'required-email' => 'invalid',
            'required-text' => 'bob'
        ]);
        $this->assertContains('Please enter an email address', $response->getBody());

        // Post with only required
        $this->get($form->URLSegment);
        /** @var HTTPResponse $response */
        $response = $this->submitForm('UserForm_Form_' . $form->ID, null, [
            'required-text' => 'bob'
        ]);
        $this->assertContains("Thanks, we've received your submission.", $response->getBody());
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

        // check to see if $Form is placed in the template
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

    public function testRenderingIntoTemplateWithDisabledInterpolation()
    {
        $form = $this->setupFormFrontend();

        $controller = new UserDefinedFormController($form);
        $controller->config()->set('disable_form_content_shortcode', true);
        // check to see if $Form is replaced to inside the content
        $index = new ArrayData($controller->index());
        $html = $index->renderWith(__CLASS__);
        $parser = new CSSContentParser($html);

        // Assert Content has been rendered with the shortcode in place
        $this->assertContains('<p>Here is my form</p><p>$UserDefinedForm</p><p>Thank you for filling it out</p>', $html);
        // And the form in the $From area
        $this->assertArrayHasKey(0, $parser->getBySelector('form#UserForm_Form_' . $form->ID));
        // check for the input
        $this->assertArrayHasKey(0, $parser->getBySelector('input.text'));
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

        $this->actWithPermission('ADMIN', function () use ($form) {
            $form->publishRecursive();
        });

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


    public function testRecipientSubjectMergeFields()
    {
        $form = $this->setupFormFrontend();

        $recipient = $this->objFromFixture(EmailRecipient::class, 'recipient-1');
        $recipient->EmailSubject = 'Email Subject: $basic_text_name';
        $recipient->write();

        $this->autoFollowRedirection = false;
        $this->clearEmails();

        // load the form
        $this->get($form->URLSegment);

        $field = $this->objFromFixture(EditableTextField::class, 'basic-text');

        $response = $this->submitForm('UserForm_Form_' . $form->ID, null, [$field->Name => 'Basic Value']);

        // should have a submitted form field now
        $submitted = DataObject::get(SubmittedFormField::class, "\"Name\" = 'basic_text_name'");
        $this->assertListAllMatch(
            [
                'Name' => 'basic_text_name',
                'Value' => 'Basic Value',
                'Title' => 'Basic Text Field'
            ],
            $submitted
        );

        // check emails
        $this->assertEmailSent('test@example.com', 'no-reply@example.com', 'Email Subject: Basic Value');
    }

    public function testConfirmfolderformInvalidRequest()
    {
        $this->logInWithPermission('CMS_ACCESS_CMSMain');

        $url = 'UserDefinedFormController/confirmfolderform?';
        $userDefinedFormID = $this->idFromFixture(UserDefinedForm::class, 'basic-form-page');
        $fieldID = $this->idFromFixture(EditableFileField::class, 'file-field-1');

        $response = $this->get($url . http_build_query(['UserFormID' => $userDefinedFormID]));
        $this->assertEquals(400, $response->getStatusCode(), 'Request without ID parameter is invalid');

        $response = $this->get($url . http_build_query(['ID' => $fieldID]));
        $this->assertEquals(400, $response->getStatusCode(), 'Request without UserFormID parameter is invalid');

        $response = $this->get($url . http_build_query(['ID' => $fieldID, 'UserFormID' => -1]));
        $this->assertEquals(400, $response->getStatusCode(), 'Request with unknown UserFormID is invalid');

        $response = $this->get($url . http_build_query(['ID' => -1, 'UserFormID' => $userDefinedFormID]));
        $this->assertEquals(400, $response->getStatusCode(), 'Request with unknown ID and known UserFormID is invalid');
    }

    public function testConfirmfolderformAccessControl()
    {
        $url = 'UserDefinedFormController/confirmfolderform?';
        $userDefinedFormID = $this->idFromFixture(UserDefinedForm::class, 'upload-form');
        $restrictedUserDefinedFormID = $this->idFromFixture(UserDefinedForm::class, 'restricted-user-form');
        $fieldID = $this->idFromFixture(EditableFileField::class, 'file-field-1');
        $restrictedFieldID = $this->idFromFixture(EditableFileField::class, 'file-field-2');

        $this->logOut();
        $response = $this->get($url . http_build_query(['ID' => $fieldID, 'UserFormID' => $userDefinedFormID]));
        $this->assertEquals(403, $response->getStatusCode(), 'Anonymous users can\'t access confirm folder form ');

        $this->logInWithPermission('CMS_ACCESS_CMSMain');

        $response = $this->get($url . http_build_query(['ID' => $fieldID, 'UserFormID' => $userDefinedFormID]));
        $this->assertEquals(200, $response->getStatusCode(), 'CMS editors can access confirm folder form ');

        $response = $this->get($url . http_build_query([
                'ID' => $restrictedFieldID,
                'UserFormID' => $restrictedUserDefinedFormID
            ]));
        $this->assertEquals(
            403,
            $response->getStatusCode(),
            'CMS editors can\'t access confirm folder form for restricted form'
        );

        $this->logInWithPermission('ADMIN');

        $response = $this->get($url . http_build_query([
                'ID' => $restrictedFieldID,
                'UserFormID' => $restrictedUserDefinedFormID
            ]));
        $this->assertEquals(
            200,
            $response->getStatusCode(),
            'Admins can access confirm folder form for restricted form'
        );
    }

    public function testConfirmfolderformFields()
    {
        $url = 'UserDefinedFormController/confirmfolderform?';
        $userDefinedFormID = $this->idFromFixture(UserDefinedForm::class, 'upload-form');
        $fieldID = $this->idFromFixture(EditableFileField::class, 'file-field-1');
        $folderID = $this->idFromFixture(Folder::class, 'unrestricted');
        $this->logInWithPermission('ADMIN');

        $response = $this->get(
            $url . http_build_query(['ID' => $fieldID, 'UserFormID' => $userDefinedFormID]),
            null,
            ['X-FormSchema-Request' => 'auto,schema,state,errors']
        );
        $schemaData = json_decode($response->getBody(), true);

        $this->assertEquals('ConfirmFolderForm', $schemaData['schema']['name']);
        $this->assertField($schemaData, 'FolderOptions', ['component' => 'OptionsetField']);
        $this->assertField($schemaData, 'FolderID', ['component' => 'TreeDropdownField']);
        $this->assertField($schemaData, 'ID', ['schemaType' =>'Hidden']);

        $this->assertStateValue($schemaData, ['ID' => $fieldID, 'FolderID' => $folderID]);
    }

    public function testConfirmfolderformDefaultFolder()
    {
        $url = 'UserDefinedFormController/confirmfolderform?';
        $userDefinedFormID = $this->idFromFixture(UserDefinedForm::class, 'restricted-user-form');
        $fieldID = $this->idFromFixture(EditableFileField::class, 'file-field-2');

        $this->logInWithPermission('ADMIN');

        $response = $this->get(
            $url . http_build_query(['ID' => $fieldID, 'UserFormID' => $userDefinedFormID]),
            null,
            ['X-FormSchema-Request' => 'auto,schema,state,errors']
        );
        $schemaData = json_decode($response->getBody(), true);

        $this->assertEquals('ConfirmFolderForm', $schemaData['schema']['name']);
        $this->assertField($schemaData, 'FolderOptions', ['component' => 'OptionsetField']);
        $this->assertField($schemaData, 'FolderID', ['component' => 'TreeDropdownField']);
        $this->assertField($schemaData, 'ID', ['schemaType' =>'Hidden']);

        $folder = Folder::find('Form-submissions');
        $this->assertNotEmpty($folder, 'Default submission folder has been created');

        $this->assertStateValue($schemaData, ['ID' => $fieldID, 'FolderID' => $folder->ID]);

        $this->logOut();
        $this->assertFalse($folder->canView(), 'Default submission folder is protected');
    }

    public function testConfirmfolderInvalidRequest()
    {
        $this->logInWithPermission(['CMS_ACCESS_CMSMain', 'CMS_ACCESS_AssetAdmin']);

        $url = 'UserDefinedFormController/confirmfolder?';
        $response = $this->post($url, []);
        $this->assertEquals(400, $response->getStatusCode(), 'Request without ID parameter is invalid');

        $response = $this->post($url, ['ID' => -1]);
        $this->assertEquals(400, $response->getStatusCode(), 'Request without ID parameter is invalid');
    }

    public function testConfirmfolderAccessControl()
    {
        $url = 'UserDefinedFormController/confirmfolder?';
        $userDefinedFormID = $this->idFromFixture(UserDefinedForm::class, 'upload-form');
        $restrictedUserDefinedFormID = $this->idFromFixture(UserDefinedForm::class, 'restricted-user-form');
        $fieldID = $this->idFromFixture(EditableFileField::class, 'file-field-1');
        $restrictedFieldID = $this->idFromFixture(EditableFileField::class, 'file-field-2');

        $this->logOut();
        $response = $this->post($url, ['ID' => $fieldID]);
        $this->assertEquals(403, $response->getStatusCode(), 'Anonymous users can\'t confirm folder ');

        $this->logInWithPermission('CMS_ACCESS_CMSMain');
        $response = $this->post($url, ['ID' => $fieldID]);
        $this->assertEquals(
            403,
            $response->getStatusCode(),
            'Users without CMS_ACCESS_AssetAdmin can\'t confirm folder'
        );

        $this->logInWithPermission(['CMS_ACCESS_CMSMain', 'CMS_ACCESS_AssetAdmin']);
        $response = $this->post($url, ['ID' => $fieldID]);
        $this->assertEquals(200, $response->getStatusCode(), 'CMS editors can access confirm folder form ');

        $response = $this->post($url, ['ID' => $restrictedFieldID]);
        $this->assertEquals(
            403,
            $response->getStatusCode(),
            'CMS editors can\'t confirm folder form for restricted form'
        );

        $this->logInWithPermission('ADMIN');

        $response = $this->post($url, ['ID' => $restrictedFieldID]);
        $this->assertEquals(
            200,
            $response->getStatusCode(),
            'Admins can confirm folder form for restricted form'
        );
    }

    public function testConfirmfolderExistingFolder()
    {
        $this->logInWithPermission(['CMS_ACCESS_CMSMain', 'CMS_ACCESS_AssetAdmin']);

        $url = 'UserDefinedFormController/confirmfolder?';
        $fieldID = $this->idFromFixture(EditableFileField::class, 'file-field-1');
        $folderID = $this->idFromFixture(Folder::class, 'restricted');

        $response = $this->post($url, ['ID' => $fieldID, 'FolderOptions' => 'existing', 'FolderID' => $folderID]);
        $this->assertEquals(200, $response->getStatusCode(), 'Valid request to confirm an existing folder is successful');
        $this->assertEquals(
            $folderID,
            EditableFileField::get()->byID($fieldID)->FolderID,
            'FileField points to restricted folder'
        );
    }

    public function testConfirmfolderInexistingFolder()
    {
        $this->logInWithPermission(['CMS_ACCESS_CMSMain', 'CMS_ACCESS_AssetAdmin']);

        $url = 'UserDefinedFormController/confirmfolder?';
        $fieldID = $this->idFromFixture(EditableFileField::class, 'file-field-1');

        $response = $this->post($url, ['ID' => $fieldID, 'FolderOptions' => 'existing', 'FolderID' => -1]);
        $this->assertEquals(400, $response->getStatusCode(), 'Confirm a non-existant folder fails with 400');
    }

    public function testConfirmfolderRootFolder()
    {
        $this->logInWithPermission(['CMS_ACCESS_CMSMain', 'CMS_ACCESS_AssetAdmin']);

        $url = 'UserDefinedFormController/confirmfolder?';
        $fieldID = $this->idFromFixture(EditableFileField::class, 'file-field-1');

        $response = $this->post($url, ['ID' => $fieldID, 'FolderOptions' => 'existing', 'FolderID' => 0]);
        $this->assertEquals(200, $response->getStatusCode(), 'Valid request to confirm an root folder is successful');
        $this->assertEquals(0, EditableFileField::get()->byID($fieldID)->FolderID, 'FileField points to root folder');
    }

    public function testConfirmfolderNewFolder()
    {
        $this->logInWithPermission(['CMS_ACCESS_CMSMain', 'CMS_ACCESS_AssetAdmin']);

        $url = 'UserDefinedFormController/confirmfolder?';
        $fieldID = $this->idFromFixture(EditableFileField::class, 'file-field-1');

        $response = $this->post($url, ['ID' => $fieldID, 'FolderOptions' => 'new']);
        $this->assertEquals(200, $response->getStatusCode(), 'Valid request to confirm folder by creating a new one is valid');

        $folder = Folder::find('Form-submissions/Form-with-upload-field');
        $this->assertNotEmpty($folder, 'New folder has been created based on the UserFormPage\'s title');

        $this->logOut();
        $this->assertFalse($folder->canView(), 'New folder is restricted');
    }

    public function testConfirmfolderNewFolderWithSpecificName()
    {
        $this->logInWithPermission(['CMS_ACCESS_CMSMain', 'CMS_ACCESS_AssetAdmin']);

        $url = 'UserDefinedFormController/confirmfolder?';
        $fieldID = $this->idFromFixture(EditableFileField::class, 'file-field-1');

        $response = $this->post(
            $url,
            ['ID' => $fieldID, 'FolderOptions' => 'new', 'CreateFolder' => 'My-Custom-Folder->\'Pow']
        );
        $this->assertEquals(200, $response->getStatusCode(), 'Valid request to confirm folder by creating a new one is valid');

        $folder = Folder::find('Form-submissions/My-Custom-Folder-Pow');
        $this->assertNotEmpty($folder, 'New folder has been created based the provided CreateFolder value');

        $this->logOut();
        $this->assertFalse($folder->canView(), 'New folder is restricted');
    }

    public function testConfirmfolderWithFieldTypeConversion()
    {
        $this->logInWithPermission('ADMIN');

        $url = 'UserDefinedFormController/confirmfolder?';
        $fieldID = $this->idFromFixture(EditableTextField::class, 'become-file-upload');

        $response = $this->post($url, ['ID' => $fieldID, 'FolderOptions' => 'new']);
        $this->assertEquals(200, $response->getStatusCode(), 'Valid request to confirm folder by creating a new one is valid');

        $folder = Folder::find('Form-submissions/Form-editable-only-by-admin');
        $this->assertNotEmpty($folder, 'New folder has been created based on the UserFormPage\'s title');

        $this->logOut();
        $this->assertFalse($folder->canView(), 'New folder is restricted');

        $field = EditableFormField::get()->byID($fieldID);
        $this->assertEquals(
            EditableFileField::class,
            $field->ClassName,
            'EditableTextField has been converted to EditableFileField'
        );
    }

    public function testPreserveSubmissionFolderPermission()
    {
        $folder = Folder::find_or_make('Form-submissions');
        $folder->CanViewType = InheritedPermissions::ANYONE;
        $folder->write();


        $this->logInWithPermission(['CMS_ACCESS_CMSMain', 'CMS_ACCESS_AssetAdmin']);
        $url = 'UserDefinedFormController/confirmfolder?';
        $fieldID = $this->idFromFixture(EditableFileField::class, 'file-field-1');

        $this->post($url, ['ID' => $fieldID, 'FolderOptions' => 'new']);

        $folder = Folder::find('Form-submissions');

        $this->assertEquals(
            InheritedPermissions::ANYONE,
            $folder->CanViewType,
            'Submission folder permissions are preserved'
        );
    }

    /**
     * Assert that a field with the provided attribute exists in $schema.
     *
     * @param array $schema
     * @param string $name
     * @param string $component
     * @param $value
     * @param string $message
     */
    private function assertField(array $schema, string $name, array $attributes, $message = '')
    {
        $message = $message ?: sprintf('A %s field exists with %s', $name, var_export($attributes, true));
        $fields = $schema['schema']['fields'];
        $state = $schema['state']['fields'];
        $this->assertNotEmpty($fields, $message);
        $foundField = false;
        foreach ($fields as $field) {
            if ($field['name'] === $name) {
                $foundField = true;
                foreach ($attributes as $attr => $expectedValue) {
                    $this->assertEquals($expectedValue, $field[$attr]);
                }
                break;
            }
        }
        $this->assertTrue($foundField, $message);
    }

    private function assertStateValue(array $schema, $values)
    {
        $fields = $schema['state']['fields'];
        $this->assertNotEmpty($fields);
        $foundField = false;
        foreach ($fields as $field) {
            $key = $field['name'];
            if (isset($values[$key])) {
                $this->assertEquals($values[$key], $field['value'], sprintf('%s is %s', $key, $values[$key]));
            }
        }
    }

    public function testGetFolderPermissionAccessControl()
    {
        $this->logOut();
        $url = 'UserDefinedFormController/getfoldergrouppermissions?';
        $folder = Folder::find('unrestricted');
        $response = $this->get($url . http_build_query(['FolderID' => $folder->ID]));
        $this->assertEquals(
            403,
            $response->getStatusCode(),
            'Access denied for getting permission of folder unauthenticated'
        );

        $response = $this->get($url . http_build_query(['FolderID' => 0]));
        $this->assertEquals(
            403,
            $response->getStatusCode(),
            'Access denied for getting permission of root folder unauthenticated'
        );

        $response = $this->get($url . http_build_query(['FolderID' => -1]));
        $this->assertEquals(
            403,
            $response->getStatusCode(),
            'Access denied for getting permission of non-existent folder unauthenticated'
        );

        $this->logInWithPermission(['CMS_ACCESS_CMSMain', 'CMS_ACCESS_AssetAdmin']);
        $adminOnlyFolder = Folder::find('admin-only');
        $response = $this->get($url . http_build_query(['FolderID' => $adminOnlyFolder->ID]));
        $this->assertEquals(
            403,
            $response->getStatusCode(),
            'Access denied for getting permission of Folder user does not have read access on'
        );

        $this->logInWithPermission('ADMIN');
        $adminOnlyFolder = Folder::find('admin-only');
        $response = $this->get($url . http_build_query(['FolderID' => $adminOnlyFolder->ID]));
        $this->assertEquals(
            200,
            $response->getStatusCode(),
            'Access denied for getting permission of Folder user does not have read access on'
        );
    }

    public function testGetFolderPermissionNonExistentFolder()
    {
        $this->logInWithPermission(['CMS_ACCESS_CMSMain', 'CMS_ACCESS_AssetAdmin']);
        $url = 'UserDefinedFormController/getfoldergrouppermissions?';

        $response = $this->get($url . http_build_query(['FolderID' => -1]));
        $this->assertEquals(
            400,
            $response->getStatusCode(),
            'Non existent folder should fail'
        );
    }

    public function testGetFolderPermissionValidRequest()
    {
        $url = 'UserDefinedFormController/getfoldergrouppermissions?';
        $this->logInWithPermission(['CMS_ACCESS_CMSMain', 'CMS_ACCESS_AssetAdmin']);

        $folder = Folder::find('unrestricted');
        $response = $this->get($url . http_build_query(['FolderID' => $folder->ID]));
        $this->assertEquals(
            200,
            $response->getStatusCode(),
            'Valid request is successfull'
        );
        $this->assertContains('Unrestricted access, uploads will be visible to anyone', $response->getBody());

        $folder = Folder::find('restricted-folder');
        $response = $this->get($url . http_build_query(['FolderID' => 0]));
        $this->assertEquals(
            200,
            $response->getStatusCode(),
            'Valid request for root folder is successful'
        );
        $this->assertContains('Unrestricted access, uploads will be visible to anyone', $response->getBody());

        $folder = Folder::find('restricted-folder');
        $response = $this->get($url . http_build_query(['FolderID' => $folder->ID]));
        $this->assertEquals(
            200,
            $response->getStatusCode(),
            'Valid request for root folder is successful'
        );
        $this->assertContains('Restricted access, uploads will be visible to logged-in users ', $response->getBody());

        $this->logInWithPermission('ADMIN');
        $adminOnlyFolder = Folder::find('admin-only');
        $response = $this->get($url . http_build_query(['FolderID' => $adminOnlyFolder->ID]));
        $this->assertEquals(
            200,
            $response->getStatusCode(),
            'Valid request for folder restricted to group is successful'
        );
        $this->assertContains('Restricted access, uploads will be visible to the following groups: Administrators', $response->getBody());
    }

    public function testImageThumbnailCreated()
    {
        Config::modify()->set(Upload_Validator::class, 'use_is_uploaded_file', false);

        $userForm = $this->setupFormFrontend('upload-form');
        $controller = UserDefinedFormController::create($userForm);
        $field = $this->objFromFixture(EditableFileField::class, 'file-field-1');

        $path = realpath(__DIR__ . '/fixtures/testfile.jpg');
        $data = [
            $field->Name => [
                'name' => 'testfile.jpg',
                'type' => 'image/jpeg',
                'tmp_name' => $path,
                'error' => 0,
                'size' => filesize($path),
            ]
        ];
        $_FILES[$field->Name] = $data[$field->Name];

        $controller->getRequest()->setSession(new Session([]));
        $controller->process($data, $controller->Form());

        /** @var File $image */
        // Getting File instead of Image so that we still delete the physical file in case it was
        // created with the wrong ClassName
        // Using StartsWith in-case of existing file so was created as testfile-v2.jpg
        $image = File::get()->filter(['Name:StartsWith' => 'testfile'])->last();
        $this->assertNotNull($image);

        // Assert thumbnail variant created
        /** @var AssetStore $store */
        $store = Injector::inst()->get(AssetStore::class);
        $this->assertTrue($store->exists($image->getFilename(), $image->getHash(), 'FitMaxWzM1MiwyNjRd'));
    }

    public function testGetFormSubmissionFolder()
    {
        $submissionFolder = Folder::find('Form-submissions');
        $this->assertEmpty($submissionFolder, 'Submission folder does not exists initially.');

        // No parameters
        $submissionFolder = UserDefinedFormController::getFormSubmissionFolder();
        $this->assertNotEmpty($submissionFolder, 'Submission folder exists after getFormSubmissionFolder call');
        $this->assertEquals('Form-submissions/', $submissionFolder->getFilename(), 'Submission folder got created under correct name');

        $this->assertEquals(InheritedPermissions::ONLY_THESE_USERS, $submissionFolder->CanViewType, 'Submission folder has correct permissions');
        $this->assertNotEmpty($submissionFolder->ViewerGroups()->find('Code', 'administrators'), 'Submission folder is limited to administrators');

        // subfolder name
        $submissionSubFolder = UserDefinedFormController::getFormSubmissionFolder('test-form');
        $this->assertNotEmpty($submissionSubFolder, 'Submission subfolder has been created');
        $this->assertEquals('Form-submissions/test-form/', $submissionSubFolder->getFilename(), 'Submission sub folder got created under correct name');
        $this->assertEquals(InheritedPermissions::INHERIT, $submissionSubFolder->CanViewType, 'Submission sub folder inherit permission from parent');

        // make sure parent folder permission don't get overridden
        $submissionFolder = Folder::find('Form-submissions');
        $submissionFolder->CanViewType = InheritedPermissions::INHERIT;
        $submissionFolder->write();

        $submissionSubFolder = UserDefinedFormController::getFormSubmissionFolder('test-form-2');
        $submissionFolder = Folder::find('Form-submissions');
        $this->assertEquals(InheritedPermissions::INHERIT, $submissionFolder->CanViewType, 'Submission sub folder inherit permission from parent');

        // Submission folder get recreated
        $submissionFolder->delete();
        $submissionFolder = Folder::find('Form-submissions');
        $this->assertEmpty($submissionFolder, 'Submission folder does has been deleted.');

        $submissionSubFolder = UserDefinedFormController::getFormSubmissionFolder('test-form-3');
        $submissionFolder = Folder::find('Form-submissions');
        $this->assertNotEmpty($submissionFolder, 'Submission folder got recreated');
        $this->assertEquals('Form-submissions/', $submissionFolder->getFilename(), 'Submission folder got recreated under correct name');

        $this->assertEquals(InheritedPermissions::ONLY_THESE_USERS, $submissionFolder->CanViewType, 'Submission folder has correct permissions');
        $this->assertNotEmpty($submissionFolder->ViewerGroups()->find('Code', 'administrators'), 'Submission folder is limited to administrators');
    }
}
