<?php

namespace SilverStripe\UserForms\Tests\Model\Recipient;

use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\UserForms\Model\Recipient\EmailRecipient;

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
        $this->assertContains('/about-us/', $result);

        $recipient->SendPlain = true;
        $recipient->EmailBody = 'Some email content. About us: [sitetree_link,id=' . $page->ID . '].';

        $result = $recipient->getEmailBodyContent();
        $this->assertContains('/about-us/', $result);
    }

    /**
     * @expectedException \SilverStripe\ORM\ValidationException
     * @expectedExceptionMessage "Send email to" address or field is required
     */
    public function testEmptyRecipientFailsValidation()
    {
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
}
