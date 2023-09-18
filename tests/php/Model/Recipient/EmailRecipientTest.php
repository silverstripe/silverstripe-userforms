<?php

namespace SilverStripe\UserForms\Tests\Model\Recipient;

use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\UserForms\Model\Recipient\EmailRecipient;
use SilverStripe\UserForms\Model\UserDefinedForm;

class EmailRecipientTest extends SapphireTest
{
    protected static $fixture_file = 'EmailRecipientTest.yml';

    public function testShortcodesAreRenderedInEmailPreviewContent()
    {
        $page = $this->objFromFixture(SiteTree::class, 'about_us');

        $recipient = EmailRecipient::create();
        $recipient->SendPlain = false;
        $recipient->EmailBodyHtml = '<p>Some email content. About us: [sitetree_link,id=' . $page->ID . '].</p>';

        $result = $recipient->getEmailBodyContent();
        $this->assertStringContainsString('/about-us', $result);

        $recipient->SendPlain = true;
        $recipient->EmailBody = 'Some email content. About us: [sitetree_link,id=' . $page->ID . '].';

        $result = $recipient->getEmailBodyContent();
        $this->assertStringContainsString('/about-us', $result);
    }

    public function testEmptyRecipientFailsValidation()
    {
        $this->expectException(\SilverStripe\ORM\ValidationException::class);
        $this->expectExceptionMessage('"Send email to" address or field is required');
        $recipient = new EmailRecipient();
        $recipient->EmailFrom = 'test@example.com';
        $recipient->write();
    }

    public function testEmailAddressesTrimmed()
    {
        $recipient = new EmailRecipient();
        $recipient->EmailAddress = 'test1@example.com  ';
        $recipient->EmailFrom = 'test2@example.com  ';
        $recipient->EmailReplyTo = 'test3@example.com  ';
        $recipient->write();
        $this->assertSame('test1@example.com', $recipient->EmailAddress);
        $this->assertSame('test2@example.com', $recipient->EmailFrom);
        $this->assertSame('test3@example.com', $recipient->EmailReplyTo);
    }

    public function testGetEmailTemplateDropdownValues()
    {
        $form = new UserDefinedForm();
        $form->write();
        $recipient = new EmailRecipient();
        $recipient->FormID = $form->ID;
        $recipient->FormClass = UserDefinedForm::class;
        $ds = DIRECTORY_SEPARATOR;
        $expected = [
            "email{$ds}SubmittedFormEmail" => 'SubmittedFormEmail',
            "email{$ds}SubmittedFormEmailPlain" => 'SubmittedFormEmailPlain'
        ];
        $this->assertSame($expected, $recipient->getEmailTemplateDropdownValues());
    }
}
