<?php

namespace SilverStripe\UserForms\Tests\Control;

use ReflectionClass;
use SilverStripe\Assets\Dev\TestAssetStore;
use SilverStripe\Assets\File;
use SilverStripe\Assets\Folder;
use SilverStripe\Assets\Storage\AssetStore;
use SilverStripe\Assets\Upload_Validator;
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
use SilverStripe\UserForms\Model\EditableFormField\EditableFileField;
use SilverStripe\UserForms\Model\EditableFormField\EditableTextField;
use SilverStripe\UserForms\Model\Recipient\EmailRecipient;
use SilverStripe\UserForms\Model\Submission\SubmittedFormField;
use SilverStripe\UserForms\Model\UserDefinedForm;
use SilverStripe\UserForms\Tests\Control\fixtures\SizeStringTestableController;
use SilverStripe\Versioned\Versioned;
use SilverStripe\View\ArrayData;
use SilverStripe\View\SSViewer;
use function filesize;

/**
 * @package userforms
 */
class UserDefinedFormControllerTest extends FunctionalTest
{
    protected static $fixture_file = '../UserFormsTest.yml';

    protected static $disable_themes = true;

    protected function setUp(): void
    {
        parent::setUp();

        // Set backend and base url
        TestAssetStore::activate('AssetStoreTest');

        $config = Config::modify();
        $config->set(UserDefinedFormController::class, 'maximum_email_attachment_size', "1M");
        $config->merge(SSViewer::class, 'themes', ['simple', '$default']);
    }

    protected function tearDown(): void
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

        /** @var EditableTextField $field */
        $field = $this->objFromFixture(EditableTextField::class, 'basic-text');

        $data = [$field->Name => 'Basic Value <b>HTML</b>'];
        $response = $this->submitForm('UserForm_Form_' . $form->ID, null, $data);

        // should have a submitted form field now
        $submitted = DataObject::get(SubmittedFormField::class, "\"Name\" = 'basic_text_name'");
        $this->assertListAllMatch(
            [
                'Name' => 'basic_text_name',
                'Value' => 'Basic Value <b>HTML</b>',
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

        // submitted html tags are escaped for the html value
        $value = 'class="readonly">My body html Basic Value &lt;b&gt;HTML&lt;/b&gt;</span>';
        $this->assertTrue(strpos($email['Content'] ?? '', $value ?? '') !== false, 'Email contains the merge field value');

        $value = $parser->getBySelector('dd');
        $this->assertEquals('Basic Value <b>HTML</b>', (string) $value[0], 'Email contains the value');

        // no html
        $this->assertEmailSent('nohtml@example.com', 'no-reply@example.com', 'Email Subject');
        $nohtml = $this->findEmail('nohtml@example.com', 'no-reply@example.com', 'Email Subject');

        $this->assertStringContainsString('* Basic Value <b>HTML</b>', $nohtml['Content'], 'Email contains no html');

        // submitted html tags are not escaped because the email is being sent as text/plain
        $value = 'My body text Basic Value <b>HTML</b>';
        $this->assertStringContainsString($value, $nohtml['Content'], 'Email contains the merge field value');

        // no data
        $this->assertEmailSent('nodata@example.com', 'no-reply@example.com', 'Email Subject');
        $nodata = $this->findEmail('nodata@example.com', 'no-reply@example.com', 'Email Subject');

        $parser = new CSSContentParser($nodata['Content']);
        $list = $parser->getBySelector('dl');

        $this->assertEmpty($list, 'Email contains no fields');

        // check to see if the user was redirected (301)
        $this->assertEquals($response->getStatusCode(), 302);
        $location = $response->getHeader('Location');
        $this->assertStringContainsString('finished', $location);
        $this->assertStringEndsWith('#uff', $location);

        // check that multiple email addresses are supported in to and from
        $this->assertEmailSent(
            'test1@example.com, test2@example.com',
            'test3@example.com, test4@example.com',
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
        $this->assertStringContainsString('This field is required', $response->getBody());

        // Post with all fields, but invalid email
        $this->get($form->URLSegment);
        /** @var HTTPResponse $response */
        $response = $this->submitForm('UserForm_Form_' . $form->ID, null, [
            'required-email' => 'invalid',
            'required-text' => 'bob'
        ]);
        $this->assertStringContainsString('Please enter an email address', $response->getBody());

        // Post with only required
        $this->get($form->URLSegment);
        /** @var HTTPResponse $response */
        $response = $this->submitForm('UserForm_Form_' . $form->ID, null, [
            'required-text' => 'bob'
        ]);
        $this->assertStringContainsString("Thanks, we've received your submission.", $response->getBody());
    }

    public function testFinished()
    {
        $form = $this->setupFormFrontend();

        // set formProcessed and SecurityID to replicate the form being filled out
        $this->session()->set('SecurityID', 1);
        $this->session()->set('FormProcessed', 1);

        $response = $this->get($form->URLSegment.'/finished');

        $this->assertStringContainsString($form->OnCompleteMessage, $response->getBody());
    }

    public function testAppendingFinished()
    {
        $form = $this->setupFormFrontend();

        // replicate finished being added to the end of the form URL without the form being filled out
        $this->session()->set('SecurityID', 1);
        $this->session()->set('FormProcessed', null);

        $response = $this->get($form->URLSegment.'/finished');

        $this->assertStringNotContainsString($form->OnCompleteMessage, $response->getBody());
    }

    public function testForm()
    {
        $form = $this->objFromFixture(UserDefinedForm::class, 'basic-form-page');

        $controller = new UserDefinedFormController($form);

        // test form
        $this->assertEquals($controller->Form()->getName(), 'Form_' . $form->ID, 'The form is referenced as Form');
        $this->assertEquals($controller->Form()->Fields()->Count(), 1); // disabled SecurityID token fields
        $this->assertEquals($controller->Form()->Actions()->Count(), 1);
        $this->assertEquals(count($controller->Form()->getValidator()->getRequired() ?? []), 0);

        $requiredForm = $this->objFromFixture(UserDefinedForm::class, 'validation-form');
        $controller = new UserDefinedFormController($requiredForm);

        $this->assertEquals($controller->Form()->Fields()->Count(), 1); // disabled SecurityID token fields
        $this->assertEquals($controller->Form()->Actions()->Count(), 1);
        $this->assertEquals(count($controller->Form()->getValidator()->getRequired() ?? []), 1);
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
        $clearAction = new FormAction('clearForm', 'Clear');
        $expected->push($clearAction->setAttribute('type', 'reset'));
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
        $this->assertStringContainsString('<p>Here is my form</p><p>$UserDefinedForm</p><p>Thank you for filling it out</p>', $html);
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

    public function testImageThumbnailCreated()
    {
        Config::modify()->set(Upload_Validator::class, 'use_is_uploaded_file', false);

        $userForm = $this->setupFormFrontend('upload-form');
        $controller = new UserDefinedFormController($userForm);
        $field = $this->objFromFixture(EditableFileField::class, 'file-field-1');

        $path = realpath(__DIR__ . '/fixtures/testfile.jpg');
        $data = [
            $field->Name => [
                'name' => 'testfile.jpg',
                'type' => 'image/jpeg',
                'tmp_name' => $path,
                'error' => 0,
                'size' => filesize($path ?? ''),
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

    public function testRecipientAttachment()
    {
        Config::modify()->set(Upload_Validator::class, 'use_is_uploaded_file', false);

        $userForm = $this->setupFormFrontend('upload-form');
        $controller = new UserDefinedFormController($userForm);
        $field = $this->objFromFixture(EditableFileField::class, 'file-field-1');

        $path = realpath(__DIR__ . '/fixtures/testfile.jpg');
        $data = [
            $field->Name => [
                'name' => 'testfile.jpg',
                'type' => 'image/jpeg',
                'tmp_name' => $path,
                'error' => 0,
                'size' => filesize($path ?? ''),
            ]
        ];
        $_FILES[$field->Name] = $data[$field->Name];

        $controller->getRequest()->setSession(new Session([]));
        $controller->process($data, $controller->Form());

        // check emails
        $this->assertEmailSent('test@example.com', 'no-reply@example.com', 'Email Subject');
        $email = $this->findEmail('test@example.com', 'no-reply@example.com', 'Email Subject');
        $this->assertNotEmpty($email['AttachedFiles'], 'Recipients receive attachment by default');

        // no data
        $this->assertEmailSent('nodata@example.com', 'no-reply@example.com', 'Email Subject');
        $nodata = $this->findEmail('nodata@example.com', 'no-reply@example.com', 'Email Subject');
        $this->assertEmpty($nodata['AttachedFiles'], 'Recipients with HideFormData do not receive attachment');
    }

    public function testMissingFolderCreated()
    {
        Config::modify()->set(Upload_Validator::class, 'use_is_uploaded_file', false);
        $userForm = $this->setupFormFrontend('upload-form-without-folder');
        $controller = UserDefinedFormController::create($userForm);
        $field = $this->objFromFixture(EditableFileField::class, 'file-field-3');

        $path = realpath(__DIR__ . '/fixtures/testfile.jpg');
        $data = [
            $field->Name => [
                'name' => 'testfile.jpg',
                'type' => 'image/jpeg',
                'tmp_name' => $path,
                'error' => 0,
                'size' => filesize($path ?? ''),
            ]
        ];
        $_FILES[$field->Name] = $data[$field->Name];

        $controller->getRequest()->setSession(new Session([]));

        $folderExistBefore = $field->getFolderExists();
        $stageBefore = Versioned::get_stage();

        $controller->process($data, $controller->Form());

        $field = EditableFileField::get_by_id($field->ID);
        $filter = [
            'ParentID' => $field->Folder()->ID,
            'Name' => 'testfile.jpg',
        ];
        $fileDraftCount = Versioned::get_by_stage(File::class, Versioned::DRAFT)->filter($filter)->count();
        $fileLiveCount = Versioned::get_by_stage(File::class, Versioned::LIVE)->filter($filter)->count();

        $folderExistAfter = $field->getFolderExists();

        $this->assertFalse($folderExistBefore);
        $this->assertTrue($folderExistAfter);
        $this->assertEquals($stageBefore, Versioned::get_stage());
        $this->assertEquals(1, $fileDraftCount);
        $this->assertEquals(0, $fileLiveCount);
    }

    public function testEmailAttachmentMaximumSizeCanBeConfigured()
    {
        $udfController = new UserDefinedFormController();
        $config = Config::modify();
        $config->set(UserDefinedFormController::class, 'maximum_email_attachment_size', '1M');
        $this->assertSame(1 * 1024 * 1024, $udfController->getMaximumAllowedEmailAttachmentSize());
        $config->set(UserDefinedFormController::class, 'maximum_email_attachment_size', '5M');
        $this->assertSame(5 * 1024 * 1024, $udfController->getMaximumAllowedEmailAttachmentSize());
    }

    public function getParseByteSizeStringTestValues()
    {
        return [
            ['9846', 9846],
            ['1048576', 1048576],
            ['1k', 1024],
            ['1K', 1024],
            ['4k', 4096],
            ['4K', 4096],
            ['1kb', 1024],
            ['1KB', 1024],
            ['4kB', 4096],
            ['4Kb', 4096],
            ['1m', 1048576],
            ['1M', 1048576],
            ['4mb', 4194304],
            ['4MB', 4194304],
            ['25mB', 26214400],
            ['25Mb', 26214400],
            ['1g', 1073741824],
            ['2GB', 2147483648],
        ];
    }

    /**
     * @dataProvider getParseByteSizeStringTestValues
     */
    public function testParseByteSizeString($input, $expectedOutput)
    {
        $controller = new SizeStringTestableController(); // extends UserDefinedFormController
        $this->assertSame($expectedOutput, $controller->convertSizeStringToBytes($input));
    }

    public function getParseByteSizeStringTestBadValues()
    {
        return [
            ['1234b'],
            ['9846B'],
            ['1kilobyte'],
            ['1 K'],
            ['Four kilobytes'],
            ['4Mbs'],
            ['12Gigs'],
        ];
    }

    /**
     * @dataProvider getParseByteSizeStringTestBadValues
     * @expectedException \InvalidArgumentException
     */
    public function testParseByteSizeStringBadValuesThrowException($input)
    {
        $this->expectException('\InvalidArgumentException');
        $controller = new SizeStringTestableController(); // extends UserDefinedFormController
        $controller->convertSizeStringToBytes($input);
    }

    public function provideValidEmailsToArray()
    {
        return [
            [[], [null]],
            [[], [' , , ']],
            [[], ['broken.email, broken@.email, broken2.@email']],
            [
                ['broken@email', 'correctemail@email.com'],
                [', broken@email, email@-email.com,correctemail@email.com,']
            ],
            [
                ['correctemail1@email.com', 'correctemail2@email.com', 'correctemail3@email.com'],
                ['correctemail1@email.com, correctemail2@email.com, correctemail3@email.com']
            ]
        ];
    }

    /**
     * @dataProvider provideValidEmailsToArray
     * Test that provided email is valid
     */
    public function testValidEmailsToArray(array $expectedOutput, array $input)
    {
        $class = new ReflectionClass(UserDefinedFormController::class);
        $method = $class->getMethod('validEmailsToArray');
        $method->setAccessible(true);

        $controller = new UserDefinedFormController();

        $this->assertEquals($expectedOutput, $method->invokeArgs($controller, $input));
    }
}
