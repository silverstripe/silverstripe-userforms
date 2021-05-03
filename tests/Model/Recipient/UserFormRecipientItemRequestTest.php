<?php

namespace SilverStripe\UserForms\Tests\Model\Recipient;

use SilverStripe\Dev\SapphireTest;
use SilverStripe\ORM\PolymorphicHasManyList;
use SilverStripe\UserForms\Model\Recipient\EmailRecipient;
use SilverStripe\UserForms\Model\Recipient\UserFormRecipientItemRequest;
use SilverStripe\UserForms\Model\UserDefinedForm;

class UserFormRecipientItemRequestTest extends SapphireTest
{
    public function testShowInReportsAffectsPreview()
    {
        // classes where showInReports() returns false
        $namespace = 'SilverStripe\UserForms\Model\EditableFormField';
        $falseClasses = ['EditableFieldGroup', 'EditableFieldGroupEnd', 'EditableFormStep'];
        // some classes where showInReports() returns true (inherits from EditableFormField)
        $trueClasses = ['EditableTextField', 'EditableEmailField', 'EditableDateField'];
        $form = new UserDefinedForm();
        $form->write();
        /** @var PolymorphicHasManyList $fields */
        $fields = $form->Fields();
        foreach (array_merge($falseClasses, $trueClasses) as $class) {
            $fqcn = "$namespace\\$class";
            $obj = new $fqcn();
            $obj->Name = 'My' . $class;
            $obj->write();
            $fields->add($obj);
        }
        $recipient = new EmailRecipient();
        $recipient->EmailAddress = 'to@example.com';
        $recipient->EmailFrom = 'from@example.com';
        $recipient->EmailTemplate = 'email/SubmittedFormEmail';
        $recipient->Form = $form;
        $recipient->write();
        $recipient->setComponent('Form', $form);
        $request = new UserFormRecipientItemRequest(null, null, $recipient, null, '');
        $html = $request->preview()->getValue();
        foreach ($falseClasses as $class) {
            $this->assertNotContains('My' . $class, $html);
        }
        foreach ($trueClasses as $class) {
            $this->assertContains('My' . $class, $html);
        }
    }
}
