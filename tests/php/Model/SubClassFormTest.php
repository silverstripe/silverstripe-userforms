<?php

namespace SilverStripe\UserForms\Tests\Model;

use SilverStripe\Control\Email\Email;
use SilverStripe\Dev\FunctionalTest;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldDataColumns;
use SilverStripe\UserForms\Extension\UserFormFieldEditorExtension;
use SilverStripe\UserForms\Model\UserDefinedForm;
use SilverStripe\UserForms\Tests\Model\SubClassFormTest\SubClassForm;

/**
 * @package userforms
 */
class SubClassFormTest extends FunctionalTest
{
    protected $usesTransactions = false;

    protected static $fixture_file = 'SubClassFormTest.yml';

    protected static $required_extensions = [
        UserDefinedForm::class => [UserFormFieldEditorExtension::class],
    ];

    protected static $extra_dataobjects = [
        SubClassForm::class,
    ];

    protected function setUp(): void
    {
        parent::setUp();
        Email::config()->update('admin_email', 'no-reply@example.com');
    }

    public function testGetCMSFieldsShowInSummary()
    {
        $this->logInWithPermission('ADMIN');
        $form = $this->objFromFixture(SubClassForm::class, 'summary-rules-form');

        $fields = $form->getCMSFields();

        $this->assertInstanceOf(GridField::class, $fields->dataFieldByName('Submissions'));

        $submissionsgrid = $fields->dataFieldByName('Submissions');
        $gridFieldDataColumns = $submissionsgrid->getConfig()->getComponentByType(GridFieldDataColumns::class);

        $summaryFields = $gridFieldDataColumns->getDisplayFields($submissionsgrid);

        $this->assertContains('SummaryShow', array_keys($summaryFields ?? []), 'Summary field not showing displayed field');
        $this->assertNotContains('SummaryHide', array_keys($summaryFields ?? []), 'Summary field showing displayed field');
    }
}
