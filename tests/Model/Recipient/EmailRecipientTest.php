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
}
