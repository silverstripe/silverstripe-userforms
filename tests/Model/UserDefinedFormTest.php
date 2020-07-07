<?php

namespace SilverStripe\UserForms\Tests\Model;

use SilverStripe\Control\Controller;
use SilverStripe\Control\Email\Email;
use SilverStripe\Core\Convert;
use SilverStripe\Dev\FunctionalTest;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldDataColumns;
use SilverStripe\ORM\DB;
use SilverStripe\UserForms\Extension\UserFormFieldEditorExtension;
use SilverStripe\UserForms\Extension\UserFormValidator;
use SilverStripe\UserForms\Model\EditableCustomRule;
use SilverStripe\UserForms\Model\EditableFormField;
use SilverStripe\UserForms\Model\EditableFormField\EditableDropdown;
use SilverStripe\UserForms\Model\EditableFormField\EditableEmailField;
use SilverStripe\UserForms\Model\EditableFormField\EditableFieldGroup;
use SilverStripe\UserForms\Model\EditableFormField\EditableFieldGroupEnd;
use SilverStripe\UserForms\Model\Recipient\EmailRecipient;
use SilverStripe\UserForms\Model\UserDefinedForm;
use SilverStripe\Versioned\Versioned;

/**
 * @package userforms
 */
class UserDefinedFormTest extends FunctionalTest
{
    protected $usesTransactions = false;

    protected static $fixture_file = '../UserFormsTest.yml';

    protected static $required_extensions = [
        UserDefinedForm::class => [UserFormFieldEditorExtension::class],
    ];

    protected function setUp()
    {
        parent::setUp();
        Email::config()->update('admin_email', 'no-reply@example.com');
    }

    public function testRollbackToVersion()
    {
        $this->markTestSkipped(
            'UserDefinedForm::rollback() has not been implemented completely'
        );

        // @todo
        $this->logInWithPermission('ADMIN');
        $form = $this->objFromFixture(UserDefinedForm::class, 'basic-form-page');

        $form->SubmitButtonText = 'Button Text';
        $form->write();
        $form->publishRecursive();
        $origVersion = $form->Version;

        $form->SubmitButtonText = 'Updated Button Text';
        $form->write();
        $form->publishRecursive();

        // check published site
        $updated = Versioned::get_one_by_stage(UserDefinedForm::class, 'Stage', "\"UserDefinedForm\".\"ID\" = $form->ID");
        $this->assertEquals($updated->SubmitButtonText, 'Updated Button Text');

        $form->doRollbackTo($origVersion);

        $orignal = Versioned::get_one_by_stage(UserDefinedForm::class, 'Stage', "\"UserDefinedForm\".\"ID\" = $form->ID");
        $this->assertEquals($orignal->SubmitButtonText, 'Button Text');
    }

    public function testGetCMSFields()
    {
        $this->logInWithPermission('ADMIN');
        $form = $this->objFromFixture(UserDefinedForm::class, 'basic-form-page');

        $fields = $form->getCMSFields();

        $this->assertNotNull($fields->dataFieldByName('Fields'));
        $this->assertNotNull($fields->dataFieldByName('EmailRecipients'));
        $this->assertNotNull($fields->dataFieldByName('Submissions'));
        $this->assertNotNull($fields->dataFieldByName('OnCompleteMessage'));
    }


    public function testGetCMSFieldsShowInSummary()
    {
        $this->logInWithPermission('ADMIN');
        $form = $this->objFromFixture(UserDefinedForm::class, 'summary-rules-form');

        $fields = $form->getCMSFields();

        $this->assertInstanceOf(GridField::class, $fields->dataFieldByName('Submissions'));

        $submissionsgrid = $fields->dataFieldByName('Submissions');
        $gridFieldDataColumns = $submissionsgrid->getConfig()->getComponentByType(GridFieldDataColumns::class);

        $summaryFields = $gridFieldDataColumns->getDisplayFields($submissionsgrid);

        $this->assertContains('SummaryShow', array_keys($summaryFields), 'Summary field not showing displayed field');
        $this->assertNotContains('SummaryHide', array_keys($summaryFields), 'Summary field showing displayed field');
    }

    public function testEmailRecipientPopup()
    {
        $this->logInWithPermission('ADMIN');

        $form = $this->objFromFixture(UserDefinedForm::class, 'basic-form-page');

        $popup = new EmailRecipient();
        $popup->FormID = $form->ID;
        $popup->FormClass = UserDefinedForm::class;
        $popup->EmailAddress = 'test@example.com';

        $fields = $popup->getCMSFields();

        $this->assertNotNull($fields->dataFieldByName('EmailSubject'));
        $this->assertNotNull($fields->dataFieldByName('EmailFrom'));
        $this->assertNotNull($fields->dataFieldByName('EmailAddress'));
        $this->assertNotNull($fields->dataFieldByName('HideFormData'));
        $this->assertNotNull($fields->dataFieldByName('SendPlain'));
        $this->assertNotNull($fields->dataFieldByName('EmailBody'));

        // add an email field, it should now add a or from X address picker
        $email = $this->objFromFixture(EditableEmailField::class, 'email-field');
        $form->Fields()->add($email);

        $popup->write();

        $fields = $popup->getCMSFields();
        $this->assertThat($fields->dataFieldByName('SendEmailToFieldID'), $this->isInstanceOf(DropdownField::class));

        // if the front end has checkboxes or dropdown they can select from that can also be used to send things
        $dropdown = $this->objFromFixture(EditableDropdown::class, 'department-dropdown');
        $form->Fields()->add($dropdown);

        $fields = $popup->getCMSFields();
        $this->assertTrue($fields->dataFieldByName('SendEmailToFieldID') !== null);

        $popup->delete();
    }

    public function testGetEmailBodyContent()
    {
        $recipient = new EmailRecipient();
        $recipient->EmailAddress = 'test@example.com';

        $emailBody = 'not html';
        $emailBodyHtml = '<p>html</p>';

        $recipient->EmailBody = $emailBody;
        $recipient->EmailBodyHtml = $emailBodyHtml;
        $recipient->write();

        $this->assertEquals($recipient->SendPlain, 0);
        $this->assertEquals($recipient->getEmailBodyContent(), $emailBodyHtml);

        $recipient->SendPlain = 1;
        $recipient->write();

        $this->assertEquals($recipient->getEmailBodyContent(), $emailBody);

        $recipient->delete();
    }

    public function testGetEmailTemplateDropdownValues()
    {
        $page = $this->objFromFixture(UserDefinedForm::class, 'basic-form-page');
        $recipient = new EmailRecipient();
        $recipient->FormID = $page->ID;
        $recipient->FormClass = UserDefinedForm::class;

        $result = $recipient->getEmailTemplateDropdownValues();

        // Installation path can be as a project when testing in Travis, so check partial match
        $foundKey = false;
        foreach (array_keys($result) as $key) {
            if (strpos($key, 'email' . DIRECTORY_SEPARATOR . 'SubmittedFormEmail') !== false) {
                $foundKey = true;
            }
        }
        $this->assertTrue($foundKey);
        $this->assertTrue(in_array('SubmittedFormEmail', array_values($result)));
    }

    public function testEmailTemplateExists()
    {
        $page = $this->objFromFixture(UserDefinedForm::class, 'basic-form-page');
        $recipient = new EmailRecipient();
        $recipient->FormID = $page->ID;
        $recipient->FormClass = UserDefinedForm::class;
        $recipient->EmailAddress = 'test@example.com';

        // Set the default template
        $recipient->EmailTemplate = current(array_keys($recipient->getEmailTemplateDropdownValues()));
        $recipient->write();

        // The default template exists
        $this->assertTrue($recipient->emailTemplateExists());

        // A made up template doesn't exists
        $this->assertFalse($recipient->emailTemplateExists('MyTemplateThatsNotThere'));

        $recipient->delete();
    }

    public function testCanEditAndDeleteRecipient()
    {
        $form = $this->objFromFixture(UserDefinedForm::class, 'basic-form-page');

        $this->logInWithPermission('ADMIN');
        foreach ($form->EmailRecipients() as $recipient) {
            $this->assertTrue($recipient->canEdit());
            $this->assertTrue($recipient->canDelete());
        }

        $this->logOut();
        $this->logInWithPermission('SITETREE_VIEW_ALL');

        foreach ($form->EmailRecipients() as $recipient) {
            $this->assertFalse($recipient->canEdit());
            $this->assertFalse($recipient->canDelete());
        }
    }

    public function testPublishing()
    {
        $this->logInWithPermission('ADMIN');

        $form = $this->objFromFixture(UserDefinedForm::class, 'basic-form-page');
        $form->write();

        $form->publishRecursive();

        $live = Versioned::get_one_by_stage(UserDefinedForm::class, 'Live', "\"UserDefinedForm_Live\".\"ID\" = $form->ID");

        $this->assertNotNull($live);
        $this->assertEquals(2, $live->Fields()->Count()); // one page and one field

        $dropdown = $this->objFromFixture(EditableDropdown::class, 'basic-dropdown');
        $form->Fields()->add($dropdown);

        $stage = Versioned::get_one_by_stage(UserDefinedForm::class, 'Stage', "\"UserDefinedForm\".\"ID\" = $form->ID");
        $this->assertEquals(3, $stage->Fields()->Count());

        // should not have published the dropdown
        $liveDropdown = Versioned::get_one_by_stage(EditableFormField::class, 'Live', "\"EditableFormField_Live\".\"ID\" = $dropdown->ID");
        $this->assertNull($liveDropdown);

        // when publishing it should have added it
        $form->publishRecursive();

        $live = Versioned::get_one_by_stage(UserDefinedForm::class, 'Live', "\"UserDefinedForm_Live\".\"ID\" = $form->ID");
        $this->assertEquals(3, $live->Fields()->Count());

        // edit the title
        $text = $form->Fields()->limit(1, 1)->First();
        $text->Title = 'Edited title';
        $text->write();

        $liveText = Versioned::get_one_by_stage(EditableFormField::class, 'Live', "\"EditableFormField_Live\".\"ID\" = $text->ID");
        $this->assertFalse($liveText->Title == $text->Title);

        $form->publishRecursive();

        $liveText = Versioned::get_one_by_stage(EditableFormField::class, 'Live', "\"EditableFormField_Live\".\"ID\" = $text->ID");
        $this->assertTrue($liveText->Title == $text->Title);

        // Add a display rule to the dropdown
        $displayRule = new EditableCustomRule();
        $displayRule->ParentID = $dropdown->ID;
        $displayRule->ConditionFieldID = $text->ID;
        $displayRule->write();
        $ruleID = $displayRule->ID;

        // Not live
        $liveRule = Versioned::get_one_by_stage(EditableCustomRule::class, 'Live', "\"EditableCustomRule_Live\".\"ID\" = $ruleID");
        $this->assertEmpty($liveRule);

        // Publish form, it's now live
        $form->publishRecursive();
        $liveRule = Versioned::get_one_by_stage(EditableCustomRule::class, 'Live', "\"EditableCustomRule_Live\".\"ID\" = $ruleID");
        $this->assertNotEmpty($liveRule);

        // Remove rule
        $displayRule->delete();

        // Live rule still exists
        $liveRule = Versioned::get_one_by_stage(EditableCustomRule::class, 'Live', "\"EditableCustomRule_Live\".\"ID\" = $ruleID");
        $this->assertNotEmpty($liveRule);

        // Publish form, it should remove this rule
         $form->publishRecursive();
         $liveRule = Versioned::get_one_by_stage(EditableCustomRule::class, 'Live', "\"EditableCustomRule_Live\".\"ID\" = $ruleID");
         $this->assertEmpty($liveRule);
    }

    public function testUnpublishing()
    {
        $this->logInWithPermission('ADMIN');
        $form = $this->objFromFixture(UserDefinedForm::class, 'basic-form-page');
        $form->write();
        $this->assertEquals(0, DB::query("SELECT COUNT(*) FROM \"EditableFormField_Live\"")->value());
        $form->publishRecursive();

        // assert that it exists and has a field
        $live = Versioned::get_one_by_stage(UserDefinedForm::class, 'Live', "\"UserDefinedForm_Live\".\"ID\" = $form->ID", false);

        $this->assertTrue(isset($live));
        $this->assertEquals(2, DB::query("SELECT COUNT(*) FROM \"EditableFormField_Live\"")->value());

        // unpublish
        $form->doUnpublish();

        $this->assertNull(Versioned::get_one_by_stage(UserDefinedForm::class, 'Live', "\"UserDefinedForm_Live\".\"ID\" = $form->ID", false));
        $this->assertEquals(0, DB::query("SELECT COUNT(*) FROM \"EditableFormField_Live\"")->value());
    }

    public function testDoRevertToLive()
    {
        $this->logInWithPermission('ADMIN');
        $form = $this->objFromFixture(UserDefinedForm::class, 'basic-form-page');
        $field = $form->Fields()->First();

        $field->Title = 'Title';
        $field->write();

        $form->publishRecursive();

        $field->Title = 'Edited title';
        $field->write();

        // check that the published version is not updated
        $live = Versioned::get_one_by_stage(EditableFormField::class, 'Live', "\"EditableFormField_Live\".\"ID\" = $field->ID");
        $this->assertInstanceOf(EditableFormField::class, $live);
        $this->assertEquals('Title', $live->Title);

        // revert back to the live data
        $form->doRevertToLive();
        $form->flushCache();

        $check = Versioned::get_one_by_stage(EditableFormField::class, 'Stage', "\"EditableFormField\".\"ID\" = $field->ID");

        $this->assertEquals('Title', $check->Title);
    }

    public function testDuplicatingForm()
    {
        $this->logInWithPermission('ADMIN');
        $form = $this->objFromFixture(UserDefinedForm::class, 'basic-form-page');

        $duplicate = $form->duplicate();

        $this->assertEquals($form->Fields()->Count(), $duplicate->Fields()->Count());

        // can't compare object since the dates/ids change
        $this->assertEquals($form->Fields()->First()->Title, $duplicate->Fields()->First()->Title);

        // Test duplicate with group
        $form2 = $this->objFromFixture(UserDefinedForm::class, 'page-with-group');
        $form2Validator = new UserFormValidator();
        $form2Validator->setForm(new Form(new Controller(), Form::class, new FieldList(), new FieldList()));
        $this->assertTrue($form2Validator->php($form2->toMap()));

        // Check field groups exist
        $form2GroupStart = $form2->Fields()->filter('ClassName', EditableFieldGroup::class)->first();
        $form2GroupEnd = $form2->Fields()->filter('ClassName', EditableFieldGroupEnd::class)->first();
        $this->assertEquals($form2GroupEnd->ID, $form2GroupStart->EndID);

        // Duplicate this
        $form3 = $form2->duplicate();
        $form3Validator = new UserFormValidator();
        $form3Validator->setForm(new Form(new Controller(), Form::class, new FieldList(), new FieldList()));
        $this->assertTrue($form3Validator->php($form3->toMap()));
        // Check field groups exist
        $form3GroupStart = $form3->Fields()->filter('ClassName', EditableFieldGroup::class)->first();
        $form3GroupEnd = $form3->Fields()->filter('ClassName', EditableFieldGroupEnd::class)->first();
        $this->assertEquals($form3GroupEnd->ID, $form3GroupStart->EndID);
        $this->assertNotEquals($form2GroupEnd->ID, $form3GroupStart->EndID);
    }

    public function testDuplicateFormDuplicatesRecursively()
    {
        $this->logInWithPermission('ADMIN');
        /** @var UserDefinedForm $form */
        $form = $this->objFromFixture(UserDefinedForm::class, 'form-with-multioptions');

        $this->assertGreaterThanOrEqual(1, $form->Fields()->count(), 'Fixtured page has a field');
        $this->assertCount(
            2,
            $form->Fields()->Last()->Options(),
            'Fixtured multiple option field has two options'
        );

        $newForm = $form->duplicate();
        $this->assertEquals(
            $form->Fields()->count(),
            $newForm->Fields()->count(),
            'Duplicated page has same number of fields'
        );
        $this->assertEquals(
            $form->Fields()->Last()->Options()->count(),
            $newForm->Fields()->Last()->Options()->count(),
            'Duplicated dropdown field from duplicated form has duplicated options'
        );
    }

    public function testFormOptions()
    {
        $this->logInWithPermission('ADMIN');
        $form = $this->objFromFixture(UserDefinedForm::class, 'basic-form-page');

        $fields = $form->getFormOptions();
        $submit = $fields->fieldByName('SubmitButtonText');
        $reset = $fields->fieldByName('ShowClearButton');

        $this->assertEquals($submit->Title(), 'Text on submit button:');
        $this->assertEquals($reset->Title(), 'Show Clear Form Button');
    }

    public function testEmailRecipientFilters()
    {
        /** @var UserDefinedForm $form */
        $form = $this->objFromFixture(UserDefinedForm::class, 'filtered-form-page');

        // Check unfiltered recipients
        $result0 = $form
            ->EmailRecipients()
            ->sort('EmailAddress')
            ->column('EmailAddress');
        $this->assertEquals(
            [
                'filtered1@example.com',
                'filtered2@example.com',
                'unfiltered@example.com'
            ],
            $result0
        );

        // check filters based on given data
        $result1 = $form->FilteredEmailRecipients(
            [
                'your-name' => 'Value',
                'address' => '',
                'street' => 'Anything',
                'city' => 'Matches Not Equals',
                'colours' => ['Red'] // matches 2
            ],
            null
        )
            ->sort('EmailAddress')
            ->column('EmailAddress');
        $this->assertEquals(
            [
                'filtered2@example.com',
                'unfiltered@example.com'
            ],
            $result1
        );

        // Check all positive matches
        $result2 = $form->FilteredEmailRecipients(
            [
                'your-name' => '',
                'address' => 'Anything',
                'street' => 'Matches Equals',
                'city' => 'Anything',
                'colours' => ['Red', 'Blue'] // matches 2
            ],
            null
        )
            ->sort('EmailAddress')
            ->column('EmailAddress');
        $this->assertEquals(
            [
                'filtered1@example.com',
                'filtered2@example.com',
                'unfiltered@example.com'
            ],
            $result2
        );

        $result3 = $form->FilteredEmailRecipients(
            [
                'your-name' => 'Should be blank but is not',
                'address' => 'Anything',
                'street' => 'Matches Equals',
                'city' => 'Anything',
                'colours' => ['Blue']
            ],
            null
        )->column('EmailAddress');
        $this->assertEquals(
            [
                'unfiltered@example.com'
            ],
            $result3
        );


        $result4 = $form->FilteredEmailRecipients(
            [
                'your-name' => '',
                'address' => 'Anything',
                'street' => 'Wrong value for this field',
                'city' => '',
                'colours' => ['Blue', 'Green']
            ],
            null
        )->column('EmailAddress');
        $this->assertEquals(
            ['unfiltered@example.com'],
            $result4
        );
    }

    public function testIndex()
    {
        // Test that the $UserDefinedForm is stripped out
        $page = $this->objFromFixture(UserDefinedForm::class, 'basic-form-page');
        $page->publish('Stage', 'Live');

        $result = $this->get($page->Link());
        $body = Convert::nl2os($result->getBody(), ''); // strip out newlines
        $this->assertFalse($result->isError());
        $this->assertContains('<p>Here is my form</p><form', $body);
        $this->assertContains('</form><p>Thank you for filling it out</p>', $body);

        $this->assertNotContains('<p>$UserDefinedForm</p>', $body);
        $this->assertNotContains('<p></p>', $body);
        $this->assertNotContains('</p><p>Thank you for filling it out</p>', $body);
    }

    public function testEmailAddressValidation()
    {
        $this->logInWithPermission('ADMIN');

        // test invalid email addresses fail validation
        $recipient = $this->objFromFixture(
            EmailRecipient::class,
            'invalid-recipient-list'
        );
        $result = $recipient->validate();
        $this->assertFalse($result->isValid());
        $this->assertNotEmpty($result->getMessages());
        $this->assertContains('filtered.example.com', $result->getMessages()[0]['message']);
        $this->assertNotContains('filtered2@example.com', $result->getMessages()[0]['message']);

        // test valid email addresses pass validation
        $recipient = $this->objFromFixture(
            EmailRecipient::class,
            'valid-recipient-list'
        );
        $result = $recipient->validate();
        $this->assertTrue($result->isValid());
        $this->assertEmpty($result->getMessages());
    }
}
