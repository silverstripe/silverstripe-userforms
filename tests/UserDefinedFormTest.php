<?php

/**
 * @package userforms
 */
class UserDefinedFormTest extends FunctionalTest
{

    public static $fixture_file = 'UserDefinedFormTest.yml';

    public function testRollbackToVersion()
    {
        $this->markTestSkipped(
            'UserDefinedForm::rollback() has not been implemented completely'
        );

        // @todo
        $this->logInWithPermission('ADMIN');
        $form = $this->objFromFixture('UserDefinedForm', 'basic-form-page');

        $form->SubmitButtonText = 'Button Text';
        $form->write();
        $form->doPublish();
        $origVersion = $form->Version;

        $form->SubmitButtonText = 'Updated Button Text';
        $form->write();
        $form->doPublish();

        // check published site
        $updated = Versioned::get_one_by_stage("UserDefinedForm", "Stage", "\"UserDefinedForm\".\"ID\" = $form->ID");
        $this->assertEquals($updated->SubmitButtonText, 'Updated Button Text');

        $form->doRollbackTo($origVersion);

        $orignal = Versioned::get_one_by_stage("UserDefinedForm", "Stage", "\"UserDefinedForm\".\"ID\" = $form->ID");
        $this->assertEquals($orignal->SubmitButtonText, 'Button Text');
    }

    public function testGetCMSFields()
    {
        $this->logInWithPermission('ADMIN');
        $form = $this->objFromFixture('UserDefinedForm', 'basic-form-page');

        $fields = $form->getCMSFields();

        $this->assertTrue($fields->dataFieldByName('Fields') !== null);
        $this->assertTrue($fields->dataFieldByName('EmailRecipients') != null);
        $this->assertTrue($fields->dataFieldByName('Submissions') != null);
        $this->assertTrue($fields->dataFieldByName('OnCompleteMessage') != null);
    }

    public function testEmailRecipientPopup()
    {
        $this->logInWithPermission('ADMIN');

        $form = $this->objFromFixture('UserDefinedForm', 'basic-form-page');

        $popup = new UserDefinedForm_EmailRecipient();
        $popup->FormID = $form->ID;

        $fields = $popup->getCMSFields();

        $this->assertTrue($fields->dataFieldByName('EmailSubject') !== null);
        $this->assertTrue($fields->dataFieldByName('EmailFrom') !== null);
        $this->assertTrue($fields->dataFieldByName('EmailAddress') !== null);
        $this->assertTrue($fields->dataFieldByName('HideFormData') !== null);
        $this->assertTrue($fields->dataFieldByName('SendPlain') !== null);
        $this->assertTrue($fields->dataFieldByName('EmailBody') !== null);

        // add an email field, it should now add a or from X address picker
        $email = $this->objFromFixture('EditableEmailField', 'email-field');
        $form->Fields()->add($email);

        $popup->write();

        $fields = $popup->getCMSFields();
        $this->assertThat($fields->dataFieldByName('SendEmailToFieldID'), $this->isInstanceOf('DropdownField'));

        // if the front end has checkboxs or dropdown they can select from that can also be used to send things
        $dropdown = $this->objFromFixture('EditableDropdown', 'department-dropdown');
        $form->Fields()->add($dropdown);

        $fields = $popup->getCMSFields();
        $this->assertTrue($fields->dataFieldByName('SendEmailToFieldID') !== null);

        $popup->delete();
    }

    public function testGetEmailBodyContent()
    {
        $recipient = new UserDefinedForm_EmailRecipient();

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
        $recipient = new UserDefinedForm_EmailRecipient();

        $defaultValues = array('SubmittedFormEmail' => 'SubmittedFormEmail');

        $this->assertEquals($recipient->getEmailTemplateDropdownValues(), $defaultValues);
    }

    public function testEmailTemplateExists()
    {
        $recipient = new UserDefinedForm_EmailRecipient();

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
        $form = $this->objFromFixture('UserDefinedForm', 'basic-form-page');

        $this->logInWithPermission('ADMIN');
        foreach ($form->EmailRecipients() as $recipient) {
            $this->assertTrue($recipient->canEdit());
            $this->assertTrue($recipient->canDelete());
        }

        $member = Member::currentUser();
        $member->logOut();

        $this->logInWithPermission('SITETREE_VIEW_ALL');
        foreach ($form->EmailRecipients() as $recipient) {
            $this->assertFalse($recipient->canEdit());
            $this->assertFalse($recipient->canDelete());
        }
    }

    public function testPublishing()
    {
        $this->logInWithPermission('ADMIN');

        $form = $this->objFromFixture('UserDefinedForm', 'basic-form-page');
        $form->write();

        $form->doPublish();

        $live = Versioned::get_one_by_stage("UserDefinedForm", "Live", "\"UserDefinedForm_Live\".\"ID\" = $form->ID");

        $this->assertNotNull($live);
        $this->assertEquals(2, $live->Fields()->Count()); // one page and one field

        $dropdown = $this->objFromFixture('EditableDropdown', 'basic-dropdown');
        $form->Fields()->add($dropdown);

        $stage = Versioned::get_one_by_stage("UserDefinedForm", "Stage", "\"UserDefinedForm\".\"ID\" = $form->ID");
        $this->assertEquals(3, $stage->Fields()->Count());

        // should not have published the dropdown
        $liveDropdown = Versioned::get_one_by_stage("EditableFormField", "Live", "\"EditableFormField_Live\".\"ID\" = $dropdown->ID");
        $this->assertNull($liveDropdown);

        // when publishing it should have added it
        $form->doPublish();

        $live = Versioned::get_one_by_stage("UserDefinedForm", "Live", "\"UserDefinedForm_Live\".\"ID\" = $form->ID");
        $this->assertEquals(3, $live->Fields()->Count());

        // edit the title
        $text = $form->Fields()->limit(1, 1)->First();
        $text->Title = 'Edited title';
        $text->write();

        $liveText = Versioned::get_one_by_stage("EditableFormField", "Live", "\"EditableFormField_Live\".\"ID\" = $text->ID");
        $this->assertFalse($liveText->Title == $text->Title);

        $form->doPublish();

        $liveText = Versioned::get_one_by_stage("EditableFormField", "Live", "\"EditableFormField_Live\".\"ID\" = $text->ID");
        $this->assertTrue($liveText->Title == $text->Title);

        // Add a display rule to the dropdown
        $displayRule = new EditableCustomRule();
        $displayRule->ParentID = $dropdown->ID;
        $displayRule->ConditionFieldID = $text->ID;
        $displayRule->write();
        $ruleID = $displayRule->ID;

        // Not live
        $liveRule = Versioned::get_one_by_stage("EditableCustomRule", "Live", "\"EditableCustomRule_Live\".\"ID\" = $ruleID");
        $this->assertEmpty($liveRule);

        // Publish form, it's now live
        $form->doPublish();
        $liveRule = Versioned::get_one_by_stage("EditableCustomRule", "Live", "\"EditableCustomRule_Live\".\"ID\" = $ruleID");
        $this->assertNotEmpty($liveRule);

        // Remove rule
        $displayRule->delete();

        // Live rule still exists
        $liveRule = Versioned::get_one_by_stage("EditableCustomRule", "Live", "\"EditableCustomRule_Live\".\"ID\" = $ruleID");
        $this->assertNotEmpty($liveRule);

        // Publish form, it should remove this rule
        $form->doPublish();
        $liveRule = Versioned::get_one_by_stage("EditableCustomRule", "Live", "\"EditableCustomRule_Live\".\"ID\" = $ruleID");
        $this->assertEmpty($liveRule);
    }

    public function testUnpublishing()
    {
        $this->logInWithPermission('ADMIN');
        $form = $this->objFromFixture('UserDefinedForm', 'basic-form-page');
        $form->write();
        $this->assertEquals(0, DB::query("SELECT COUNT(*) FROM \"EditableFormField_Live\"")->value());
        $form->doPublish();

        // assert that it exists and has a field
        $live = Versioned::get_one_by_stage("UserDefinedForm", "Live", "\"UserDefinedForm_Live\".\"ID\" = $form->ID");

        $this->assertTrue(isset($live));
        $this->assertEquals(2, DB::query("SELECT COUNT(*) FROM \"EditableFormField_Live\"")->value());

        // unpublish
        $form->doUnpublish();

        $this->assertNull(Versioned::get_one_by_stage("UserDefinedForm", "Live", "\"UserDefinedForm_Live\".\"ID\" = $form->ID"));
        $this->assertEquals(0, DB::query("SELECT COUNT(*) FROM \"EditableFormField_Live\"")->value());
    }

    public function testDoRevertToLive()
    {
        $this->logInWithPermission('ADMIN');
        $form = $this->objFromFixture('UserDefinedForm', 'basic-form-page');
        $field = $form->Fields()->First();

        $field->Title = 'Title';
        $field->write();

        $form->doPublish();

        $field->Title = 'Edited title';
        $field->write();

        // check that the published version is not updated
        $live = Versioned::get_one_by_stage("EditableFormField", "Live", "\"EditableFormField_Live\".\"ID\" = $field->ID");
        $this->assertEquals('Title', $live->Title);

        // revert back to the live data
        $form->doRevertToLive();
        $form->flushCache();

        $check = Versioned::get_one_by_stage("EditableFormField", "Stage", "\"EditableFormField\".\"ID\" = $field->ID");

        $this->assertEquals('Title', $check->Title);
    }

    public function testDuplicatingForm()
    {
        $this->logInWithPermission('ADMIN');
        $form = $this->objFromFixture('UserDefinedForm', 'basic-form-page');

        $duplicate = $form->duplicate();

        $this->assertEquals($form->Fields()->Count(), $duplicate->Fields()->Count());
        $this->assertEquals($form->EmailRecipients()->Count(), $form->EmailRecipients()->Count());

        // can't compare object since the dates/ids change
        $this->assertEquals($form->Fields()->First()->Title, $duplicate->Fields()->First()->Title);

        // Test duplicate with group
        $form2 = $this->objFromFixture('UserDefinedForm', 'page-with-group');
        $form2Validator = new UserFormValidator();
        $form2Validator->setForm(new Form(new Controller(), 'Form', new FieldList(), new FieldList()));
        $this->assertTrue($form2Validator->php($form2->toMap()));

        // Check field groups exist
        $form2GroupStart = $form2->Fields()->filter('ClassName', 'EditableFieldGroup')->first();
        $form2GroupEnd = $form2->Fields()->filter('ClassName', 'EditableFieldGroupEnd')->first();
        $this->assertEquals($form2GroupEnd->ID, $form2GroupStart->EndID);

        // Duplicate this
        $form3 = $form2->duplicate();
        $form3Validator = new UserFormValidator();
        $form3Validator->setForm(new Form(new Controller(), 'Form', new FieldList(), new FieldList()));
        $this->assertTrue($form3Validator->php($form3->toMap()));

        // Check field groups exist
        $form3GroupStart = $form3->Fields()->filter('ClassName', 'EditableFieldGroup')->first();
        $form3GroupEnd = $form3->Fields()->filter('ClassName', 'EditableFieldGroupEnd')->first();
        $this->assertEquals($form3GroupEnd->ID, $form3GroupStart->EndID);
        $this->assertNotEquals($form2GroupEnd->ID, $form3GroupStart->EndID);
    }

    public function testFormOptions()
    {
        $this->logInWithPermission('ADMIN');
        $form = $this->objFromFixture('UserDefinedForm', 'basic-form-page');

        $fields = $form->getFormOptions();
        $submit = $fields->fieldByName('SubmitButtonText');
        $reset = $fields->fieldByName('ShowClearButton');

        $this->assertEquals($submit->Title(), 'Text on submit button:');
        $this->assertEquals($reset->Title(), 'Show Clear Form Button');
    }

    public function testEmailRecipientFilters()
    {
        $form = $this->objFromFixture('UserDefinedForm', 'filtered-form-page');

        // Check unfiltered recipients
        $result0 = $form
            ->EmailRecipients()
            ->sort('EmailAddress')
            ->column('EmailAddress');
        $this->assertEquals(
            array(
                'filtered1@example.com',
                'filtered2@example.com',
                'unfiltered@example.com'
            ),
            $result0
        );

        // check filters based on given data
        $result1 = $form->FilteredEmailRecipients(
            array(
                'your-name' => 'Value',
                'address' => '',
                'street' => 'Anything',
                'city' => 'Matches Not Equals',
                'colours' => array('Red') // matches 2
            ), null
        )
            ->sort('EmailAddress')
            ->column('EmailAddress');
        $this->assertEquals(
            array(
                'filtered2@example.com',
                'unfiltered@example.com'
            ),
            $result1
        );

        // Check all positive matches
        $result2 = $form->FilteredEmailRecipients(
            array(
                'your-name' => '',
                'address' => 'Anything',
                'street' => 'Matches Equals',
                'city' => 'Anything',
                'colours' => array('Red', 'Blue') // matches 2
            ), null
        )
            ->sort('EmailAddress')
            ->column('EmailAddress');
        $this->assertEquals(
            array(
                'filtered1@example.com',
                'filtered2@example.com',
                'unfiltered@example.com'
            ),
            $result2
        );


        $result3 = $form->FilteredEmailRecipients(
            array(
                'your-name' => 'Should be blank but is not',
                'address' => 'Anything',
                'street' => 'Matches Equals',
                'city' => 'Anything',
                'colours' => array('Blue')
            ), null
        )->column('EmailAddress');
        $this->assertEquals(
            array(
                'unfiltered@example.com'
            ),
            $result3
        );


        $result4 = $form->FilteredEmailRecipients(
            array(
                'your-name' => '',
                'address' => 'Anything',
                'street' => 'Wrong value for this field',
                'city' => '',
                'colours' => array('Blue', 'Green')
            ), null
        )->column('EmailAddress');
        $this->assertEquals(
            array(
                'unfiltered@example.com'
            ),
            $result4
        );
    }

    public function testIndex()
    {
        // Test that the $UserDefinedForm is stripped out
        $page = $this->objFromFixture('UserDefinedForm', 'basic-form-page');
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
}
