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

class UserDefinedFormControllerCustomMergeFieldTest extends FunctionalTest
{
    protected static $fixture_file = '../UserFormsTest.yml';

    protected static $disable_themes = true;

    protected static $required_extensions = [
        UserDefinedFormController::class => [CustomMergeFieldTestExtension::class],
    ];

    protected function setUp(): void
    {
        parent::setUp();
        TestAssetStore::activate('AssetStoreTest');
        Config::modify()->merge(SSViewer::class, 'themes', ['startup-theme', '$default']);
    }

    protected function tearDown(): void
    {
        TestAssetStore::reset();
        CustomMergeFieldTestExtension::$value = '';
        parent::tearDown();
    }

    public function testCustomMergeFieldIsSubstituted(): void
    {
        $email = $this->submitWithCustomValue('Subject $DevVar', 'Body {$DevVar}', 'dev-provided-value');
        $this->assertNotNull($email);
        $this->assertSame('Subject dev-provided-value', $email['Subject']);
        $this->assertStringContainsString('Body dev-provided-value', $email['Content']);
    }

    public function testCustomMergeFieldValueIsNotEvaluatedAsTemplate(): void
    {
        $victim = new Member();
        $victim->Email = 'victim@example.com';
        $victim->Password = 'awesome-bicycle-chicken-delivery';
        $victim->write();
        $payload = '$List(\'SilverStripe\\Security\\Member\')'
            . '.filter(\'Email\', \'victim@example.com\').first()'
            . '.changePassword(\'injected-horse-battery-staple-xyz\')';
        $this->submitWithCustomValue('Subject $DevVar', 'Body $DevVar', $payload);
        $victim = Member::get()->byID($victim->ID);
        $result = (new MemberAuthenticator())->checkPassword($victim, 'awesome-bicycle-chicken-delivery');
        $this->assertTrue($result->isValid());
    }

    public function testCustomMergeFieldPlainStringIsEscaped(): void
    {
        $email = $this->submitWithCustomValue('Subject', 'Body $DevVar', '<b>bold</b>');
        $this->assertNotNull($email);
        $this->assertStringContainsString('Body &lt;b&gt;bold&lt;/b&gt;', $email['Content']);
        $this->assertStringNotContainsString('Body <b>bold</b>', $email['Content']);
    }

    public function testCustomDottedMergeFieldIsSubstituted(): void
    {
        $email = $this->submitWithCustomValue('Subject $Site.Name', 'Body {$Site.Name}', 'dotted-value');
        $this->assertNotNull($email);
        $this->assertSame('Subject dotted-value', $email['Subject']);
        $this->assertStringContainsString('Body dotted-value', $email['Content']);
    }

    public function testUnknownTokenIsLeftUnchanged(): void
    {
        $email = $this->submitWithCustomValue('Subject $Unknown.Token here', 'Body $Unknown', 'ignored');
        $this->assertNotNull($email);
        $this->assertStringContainsString('Subject $Unknown.Token here', $email['Subject']);
        $this->assertStringContainsString('Body $Unknown', $email['Content']);
    }

    private function submitWithCustomValue(string $subject, string $bodyHtml, string $value): ?array
    {
        CustomMergeFieldTestExtension::$value = $value;
        $form = $this->objFromFixture(UserDefinedForm::class, 'basic-form-page');
        $this->actWithPermission('ADMIN', function () use ($form) {
            $form->publishRecursive();
        });
        $recipient = $this->objFromFixture(EmailRecipient::class, 'recipient-1');
        $recipient->EmailSubject = $subject;
        $recipient->EmailBodyHtml = $bodyHtml;
        $recipient->write();
        $this->autoFollowRedirection = false;
        $this->clearEmails();
        $this->get($form->URLSegment);
        $field = $this->objFromFixture(EditableTextField::class, 'basic-text');
        $this->submitForm('UserForm_Form_' . $form->ID, null, [$field->Name => 'Basic Value']);
        return $this->findEmail('test@example.com', 'no-reply@example.com');
    }
}
