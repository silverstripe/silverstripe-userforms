<?php

class UserDefinedForm_EmailRecipientTest extends SapphireTest
{
    protected static $fixture_file = 'UserDefinedForm_EmailRecipientTest.yml';

    public function testShortcodesAreRenderedInHtmlEmailPreviewContent()
    {
        $page = $this->objFromFixture('SiteTree', 'about_us');

        $recipient = UserDefinedForm_EmailRecipient::create();
        $recipient->SendPlain = false;
        $recipient->EmailBodyHtml = '<p>Some email content. About us: [sitetree_link,id=' . $page->ID . '].</p>';

        $result = $recipient->getEmailBodyContent();
        $this->assertContains('/about-us/', $result);
    }
}
