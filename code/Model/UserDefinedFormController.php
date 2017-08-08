<?php

namespace SilverStripe\UserForms\Model;

use PageController;





use Object;











use SilverStripe\View\Requirements;
use SilverStripe\i18n\i18n;
use SilverStripe\ORM\FieldType\DBField;
use SilverStripe\UserForms\Form\UserForm;
use SilverStripe\Forms\Form;
use SilverStripe\Control\Controller;
use SilverStripe\UserForms\Model\Submission\SubmittedForm;
use SilverStripe\Security\Member;
use SilverStripe\ORM\ArrayList;
use SilverStripe\UserForms\Model\EditableFormField\EditableFileField;
use SilverStripe\Assets\Upload;
use SilverStripe\Assets\File;
use SilverStripe\ORM\ValidationException;
use SilverStripe\UserForms\Model\Recipient\UserFormRecipientEmail;
use SilverStripe\Control\HTTP;
use SilverStripe\View\SSViewer;
use SilverStripe\Control\Session;
use SilverStripe\View\ArrayData;



/**
 * Controller for the {@link UserDefinedForm} page type.
 *
 * @package userforms
 */

class UserDefinedFormController extends PageController
{

    private static $finished_anchor = '#uff';

    private static $allowed_actions = array(
        'index',
        'ping',
        'Form',
        'finished'
    );

    public function init()
    {
        parent::init();

        $page = $this->data();

        // load the css
        if (!$page->config()->block_default_userforms_css) {
            Requirements::css(USERFORMS_DIR . '/css/UserForm.css');
        }

        // load the jquery
        if (!$page->config()->block_default_userforms_js) {
        $lang = i18n::get_lang_from_locale(i18n::get_locale());
        Requirements::javascript(FRAMEWORK_DIR .'/thirdparty/jquery/jquery.js');
        Requirements::javascript(USERFORMS_DIR . '/thirdparty/jquery-validate/jquery.validate.min.js');
        Requirements::add_i18n_javascript(USERFORMS_DIR . '/javascript/lang');
        Requirements::javascript(USERFORMS_DIR . '/javascript/UserForm.js');

        Requirements::javascript(
            USERFORMS_DIR . "/thirdparty/jquery-validate/localization/messages_{$lang}.min.js"
        );
        Requirements::javascript(
            USERFORMS_DIR . "/thirdparty/jquery-validate/localization/methods_{$lang}.min.js"
        );

        // Bind a confirmation message when navigating away from a partially completed form.
        if ($page::config()->enable_are_you_sure) {
            Requirements::javascript(USERFORMS_DIR . '/thirdparty/jquery.are-you-sure/jquery.are-you-sure.js');
        }
    }
    }

    /**
     * Using $UserDefinedForm in the Content area of the page shows
     * where the form should be rendered into. If it does not exist
     * then default back to $Form.
     *
     * @return array
     */
    public function index()
    {
        if ($this->Content && $form = $this->Form()) {
            $hasLocation = stristr($this->Content, '$UserDefinedForm');
            if ($hasLocation) {
                $content = preg_replace('/(<p[^>]*>)?\\$UserDefinedForm(<\\/p>)?/i', $form->forTemplate(), $this->Content);
                return array(
                    'Content' => DBField::create_field('HTMLText', $content),
                    'Form' => ""
                );
            }
        }

        return array(
            'Content' => DBField::create_field('HTMLText', $this->Content),
            'Form' => $this->Form()
        );
    }

    /**
     * Keep the session alive for the user.
     *
     * @return int
     */
    public function ping()
    {
        return 1;
    }

    /**
     * Get the form for the page. Form can be modified by calling {@link updateForm()}
     * on a UserDefinedForm extension.
     *
     * @return Forms
     */
    public function Form()
    {
        $form = UserForm::create($this, 'Form_' . $this->ID);
        $form->setFormAction(Controller::join_links($this->Link(), Form::class));
        $this->generateConditionalJavascript();
        return $form;
    }

    /**
     * Generate the javascript for the conditional field show / hiding logic.
     *
     * @return void
     */
    public function generateConditionalJavascript()
    {
        $default = "";
        $rules = "";

        $watch = array();

        if ($this->Fields()) {
            /** @var EditableFormField $field */
            foreach ($this->Fields() as $field) {
                if ($result = $field->formatDisplayRules()) {
                    $watch[] = $result;
                }
            }
        }
        if ($watch) {
            $rules .= $this->buildWatchJS($watch);
        }

        // Only add customScript if $default or $rules is defined
        if ($rules) {
            Requirements::customScript(<<<JS
                (function($) {
                    $(document).ready(function() {
                        {$rules}
                    });
                })(jQuery);
JS
, 'UserFormsConditional');
        }
    }

    /**
     * Process the form that is submitted through the site
     *
     * {@see UserForm::validate()} for validation step prior to processing
     *
     * @param array $data
     * @param Form $form
     *
     * @return Redirection
     */
    public function process($data, $form)
    {
        $submittedForm = Object::create(SubmittedForm::class);
        $submittedForm->SubmittedByID = ($id = Member::currentUserID()) ? $id : 0;
        $submittedForm->ParentID = $this->ID;

        // if saving is not disabled save now to generate the ID
        if (!$this->DisableSaveSubmissions) {
            $submittedForm->write();
        }

        $attachments = array();
        $submittedFields = new ArrayList();

        foreach ($this->Fields() as $field) {
            if (!$field->showInReports()) {
                continue;
            }

            $submittedField = $field->getSubmittedFormField();
            $submittedField->ParentID = $submittedForm->ID;
            $submittedField->Name = $field->Name;
            $submittedField->Title = $field->getField('Title');

            // save the value from the data
            if ($field->hasMethod('getValueFromData')) {
                $submittedField->Value = $field->getValueFromData($data);
            } else {
                if (isset($data[$field->Name])) {
                    $submittedField->Value = $data[$field->Name];
                }
            }

            if (!empty($data[$field->Name])) {
                if (in_array(EditableFileField::class, $field->getClassAncestry())) {
                    if (!empty($_FILES[$field->Name]['name'])) {
                        $foldername = $field->getFormField()->getFolderName();

                        // create the file from post data
                        $upload = new Upload();
                        $file = new File();
                        $file->ShowInSearch = 0;
                        try {
                            $upload->loadIntoFile($_FILES[$field->Name], $file, $foldername);
                        } catch (ValidationException $e) {
                            $validationResult = $e->getResult();
                            $form->addErrorMessage($field->Name, $validationResult->message(), 'bad');
                            Controller::curr()->redirectBack();
                            return;
                        }

                        // write file to form field
                        $submittedField->UploadedFileID = $file->ID;

                        // attach a file only if lower than 1MB
                        if ($file->getAbsoluteSize() < 1024*1024*1) {
                            $attachments[] = $file;
                        }
                    }
                }
            }

            $submittedField->extend('onPopulationFromField', $field);

            if (!$this->DisableSaveSubmissions) {
                $submittedField->write();
            }

            $submittedFields->push($submittedField);
        }

        $emailData = array(
            "Sender" => Member::currentUser(),
            "Fields" => $submittedFields
        );

        $this->extend('updateEmailData', $emailData, $attachments);

        // email users on submit.
        if ($recipients = $this->FilteredEmailRecipients($data, $form)) {
            foreach ($recipients as $recipient) {
                $email = new UserFormRecipientEmail($submittedFields);
                $mergeFields = $this->getMergeFieldsMap($emailData['Fields']);

                if ($attachments) {
                    foreach ($attachments as $file) {
                        if ($file->ID != 0) {
                            $email->attachFile(
                                $file->Filename,
                                $file->Filename,
                                HTTP::get_mime_type($file->Filename)
                            );
                        }
                    }
                }

                $parsedBody = SSViewer::execute_string($recipient->getEmailBodyContent(), $mergeFields);

                if (!$recipient->SendPlain && $recipient->emailTemplateExists()) {
                    $email->setTemplate($recipient->EmailTemplate);
                }

                $email->populateTemplate($recipient);
                $email->populateTemplate($emailData);
                $email->setFrom($recipient->EmailFrom);
                $email->setBody($parsedBody);
                $email->setTo($recipient->EmailAddress);
                $email->setSubject($recipient->EmailSubject);

                if ($recipient->EmailReplyTo) {
                    $email->setReplyTo($recipient->EmailReplyTo);
                }

                // check to see if they are a dynamic reply to. eg based on a email field a user selected
                if ($recipient->SendEmailFromField()) {
                    $submittedFormField = $submittedFields->find('Name', $recipient->SendEmailFromField()->Name);

                    if ($submittedFormField && is_string($submittedFormField->Value)) {
                        $email->setReplyTo($submittedFormField->Value);
                    }
                }
                // check to see if they are a dynamic reciever eg based on a dropdown field a user selected
                if ($recipient->SendEmailToField()) {
                    $submittedFormField = $submittedFields->find('Name', $recipient->SendEmailToField()->Name);

                    if ($submittedFormField && is_string($submittedFormField->Value)) {
                        $email->setTo($submittedFormField->Value);
                    }
                }

                // check to see if there is a dynamic subject
                if ($recipient->SendEmailSubjectField()) {
                    $submittedFormField = $submittedFields->find('Name', $recipient->SendEmailSubjectField()->Name);

                    if ($submittedFormField && trim($submittedFormField->Value)) {
                        $email->setSubject($submittedFormField->Value);
                    }
                }

                $this->extend('updateEmail', $email, $recipient, $emailData);

                if ($recipient->SendPlain) {
                    $body = strip_tags($recipient->getEmailBodyContent()) . "\n";
                    if (isset($emailData['Fields']) && !$recipient->HideFormData) {
                        foreach ($emailData['Fields'] as $Field) {
                            $body .= $Field->Title .': '. $Field->Value ." \n";
                        }
                    }

                    $email->setBody($body);
                    $email->sendPlain();
                } else {
                    $email->send();
                }
            }
        }

        $submittedForm->extend('updateAfterProcess');

        Session::clear("FormInfo.{$form->FormName()}.errors");
        Session::clear("FormInfo.{$form->FormName()}.data");

        $referrer = (isset($data['Referrer'])) ? '?referrer=' . urlencode($data['Referrer']) : "";

        // set a session variable from the security ID to stop people accessing
        // the finished method directly.
        if (!$this->DisableAuthenicatedFinishAction) {
            if (isset($data['SecurityID'])) {
                Session::set('FormProcessed', $data['SecurityID']);
            } else {
                // if the form has had tokens disabled we still need to set FormProcessed
                // to allow us to get through the finshed method
                if (!$this->Form()->getSecurityToken()->isEnabled()) {
                    $randNum = rand(1, 1000);
                    $randHash = md5($randNum);
                    Session::set('FormProcessed', $randHash);
                    Session::set('FormProcessedNum', $randNum);
                }
            }
        }

        if (!$this->DisableSaveSubmissions) {
            Session::set('userformssubmission'. $this->ID, $submittedForm->ID);
        }

        return $this->redirect($this->Link('finished') . $referrer . $this->config()->finished_anchor);
    }

    /**
     * Allows the use of field values in email body.
     *
     * @param ArrayList fields
     * @return ArrayData
     */
    protected function getMergeFieldsMap($fields = array())
    {
        $data = new ArrayData(array());

        foreach ($fields as $field) {
            $data->setField($field->Name, DBField::create_field('Text', $field->Value));
        }

        return $data;
    }

    /**
     * This action handles rendering the "finished" message, which is
     * customizable by editing the ReceivedFormSubmission template.
     *
     * @return ViewableData
     */
    public function finished()
    {
        $submission = Session::get('userformssubmission'. $this->ID);

        if ($submission) {
            $submission = SubmittedForm::get()->byId($submission);
        }

        $referrer = isset($_GET['referrer']) ? urldecode($_GET['referrer']) : null;

        if (!$this->DisableAuthenicatedFinishAction) {
            $formProcessed = Session::get('FormProcessed');

            if (!isset($formProcessed)) {
                return $this->redirect($this->Link() . $referrer);
            } else {
                $securityID = Session::get('SecurityID');
                // make sure the session matches the SecurityID and is not left over from another form
                if ($formProcessed != $securityID) {
                    // they may have disabled tokens on the form
                    $securityID = md5(Session::get('FormProcessedNum'));
                    if ($formProcessed != $securityID) {
                        return $this->redirect($this->Link() . $referrer);
                    }
                }
            }

            Session::clear('FormProcessed');
        }

        $data = array(
                'Submission' => $submission,
                'Link' => $referrer
        );

        $this->extend('updateReceivedFormSubmissionData', $data);

        return $this->customise(array(
            'Content' => $this->customise($data)->renderWith('ReceivedFormSubmission'),
            'Form' => '',
        ));
    }

    /**
     * Outputs the required JS from the $watch input
     *
     * @param array $watch
     *
     * @return string
     */
    protected function buildWatchJS($watch)
    {
        $result = '';
        foreach ($watch as $key => $rule) {
            $events = implode(' ', $rule['events']);
            $selectors = implode(', ', $rule['selectors']);
            $conjunction = $rule['conjunction'];
            $operations = implode(" {$conjunction} ", $rule['operations']);
            $target = $rule['targetFieldID'];

            $result .= <<<EOS
\n
    $('.userform').on('{$events}',
    "{$selectors}",
    function (){
        if ({$operations}) {
            $('{$target}').{$rule['view']};
        } else {
            $('{$target}').{$rule['opposite']};
        }
    });
    $("{$target}").find('.hide').removeClass('hide');
EOS;
        }

        return $result;
    }
}
