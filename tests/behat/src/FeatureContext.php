<?php

namespace SilverStripe\UserForms\Tests\Behat\Context;

use SilverStripe\BehatExtension\Context\SilverStripeContext;

class FeatureContext extends SilverStripeContext
{
    /**
     * The preview email button is a hyperlink with target="_blank"
     * Behat won't view the newly opened tab
     *
     * @When /^I preview the email$/
     */
    public function iPreviewTheEmail()
    {
        $js = <<<JS
            document.querySelectorAll('a.btn').forEach(link => {
                if (link.innerHTML.trim() == 'Preview email') {
                    document.location.href = link.href;
                }
            });
JS;
        $result = $this->getSession()->getDriver()->executeScript($js);
        sleep(5);
    }
}
