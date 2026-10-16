<?php

namespace SilverStripe\UserForms\Tests\Control;

use SilverStripe\Assets\Dev\TestAssetStore;
use SilverStripe\Core\Config\Config;
use SilverStripe\Dev\FunctionalTest;
use SilverStripe\Security\Member;
use SilverStripe\Security\MemberAuthenticator\MemberAuthenticator;
use SilverStripe\UserForms\Control\UserDefinedFormController;
use SilverStripe\UserForms\Model\EditableFormField\EditableTextField;
use SilverStripe\UserForms\Model\Recipient\EmailRecipient;
use SilverStripe\UserForms\Model\UserDefinedForm;
use SilverStripe\View\SSViewer;

class UserDefinedFormControllerTemplateInjectionTest extends FunctionalTest
{
    protected static $fixture_file = '../UserFormsTest.yml';

    protected static $disable_themes = true;

    protected function setUp(): void
    {
        parent::setUp();
        TestAssetStore::activate('AssetStoreTest');
        Config::modify()->merge(SSViewer::class, 'themes', ['startup-theme', '$default']);
    }

    protected function tearDown(): void
    {
        TestAssetStore::reset();
        parent::tearDown();
    }

    public function testRecipientSubjectDoesNotExecuteTemplateMethodCalls(): void
    {
        $form = $this->objFromFixture(UserDefinedForm::class, 'basic-form-page');
        $this->actWithPermission('ADMIN', function () use ($form) {
            $form->publishRecursive();
        });
        $victim = new Member();
        $victim->Email = 'victim@example.com';
        $victim->Password = 'awesome-bicycle-chicken-delivery';
        $victim->write();
        $recipient = $this->objFromFixture(EmailRecipient::class, 'recipient-1');
        $payload = '$List(\'SilverStripe\\Security\\Member\')'
            . '.filter(\'Email\', \'victim@example.com\').first()'
            . '.changePassword(\'injected-horse-battery-staple-xyz\')';
        $recipient->EmailSubject = $payload;
        $recipient->write();
        $this->autoFollowRedirection = false;
        $this->clearEmails();
        $this->get($form->URLSegment);
        $field = $this->objFromFixture(EditableTextField::class, 'basic-text');
        $this->submitForm('UserForm_Form_' . $form->ID, null, [$field->Name => 'Basic Value']);
        $email = $this->findEmail('test@example.com', 'no-reply@example.com');
        $this->assertNotNull($email);
        $this->assertStringContainsString('changePassword(', $email['Subject']);
        $victim = Member::get()->byID($victim->ID);
        $result = (new MemberAuthenticator())->checkPassword($victim, 'awesome-bicycle-chicken-delivery');
        $this->assertTrue($result->isValid());
    }
}
