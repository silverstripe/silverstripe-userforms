<?php

namespace SilverStripe\UserForms\Control;

use Exception;
use PageController;
use Psr\Log\LoggerInterface;
use SilverStripe\AssetAdmin\Controller\AssetAdmin;
use SilverStripe\Assets\File;
use SilverStripe\Assets\Upload;
use SilverStripe\Control\Controller;
use SilverStripe\Control\Email\Email;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Core\Manifest\ModuleLoader;
use SilverStripe\Forms\Form;
use SilverStripe\i18n\i18n;
use SilverStripe\Model\List\ArrayList;
use SilverStripe\ORM\FieldType\DBField;
use SilverStripe\Core\Validation\ValidationException;
use SilverStripe\Core\Validation\ValidationResult;
use SilverStripe\Security\Security;
use SilverStripe\UserForms\Extension\UserFormFileExtension;
use SilverStripe\UserForms\Form\UserForm;
use SilverStripe\UserForms\Model\EditableFormField;
use SilverStripe\UserForms\Model\EditableFormField\EditableFileField;
use SilverStripe\UserForms\Model\Submission\SubmittedForm;
use SilverStripe\UserForms\Model\Submission\SubmittedFileField;
use SilverStripe\UserForms\Model\UserDefinedForm;
use SilverStripe\Versioned\Versioned;
use SilverStripe\Model\ArrayData;
use SilverStripe\View\Requirements;
use SilverStripe\Model\ModelData;
use SilverStripe\View\TemplateEngine;
use SilverStripe\View\ViewLayerData;
use Swift_RfcComplianceException;

/**
 * Controller for the {@link UserDefinedForm} page type.
 *
 * @extends PageController<UserDefinedForm>
 */
class UserDefinedFormController extends PageController
{
    private static $finished_anchor = '#uff';

    private static $allowed_actions = [
        'index',
        'ping',
        'Form',
        'finished',
    ];

    /** @var string The name of the folder where form submissions will be placed by default */
    private static $form_submissions_folder = 'Form-submissions';

    private static string $file_upload_stage = Versioned::DRAFT;

    /**
     * Size that an uploaded file must not excede for it to be attached to an email
     * Follows PHP "shorthand bytes" definition rules.
     * @see UserDefinedFormController::parseByteSizeString()
     *
     * @var int
     * @config
     */
    private static $maximum_email_attachment_size = '1M';

    protected function init()
    {
        parent::init();

        $page = $this->data();

        // load the css
        if (!$page->config()->get('block_default_userforms_css')) {
            Requirements::css('silverstripe/userforms:client/dist/styles/userforms.css');
        }

        // load the jquery
        if (!$page->config()->get('block_default_userforms_js')) {
            Requirements::javascript('silverstripe/userforms:client/dist/js/jquery.min.js');
            Requirements::javascript(
                'silverstripe/userforms:client/dist/js/jquery-validation/jquery.validate.min.js'
            );
            Requirements::javascript('silverstripe/admin:client/dist/js/i18n.js');
            Requirements::add_i18n_javascript('silverstripe/userforms:client/lang');
            Requirements::javascript('silverstripe/userforms:client/dist/js/userforms.js');

            $this->addUserFormsValidatei18n();

            // Bind a confirmation message when navigating away from a partially completed form.
            if ($page::config()->get('enable_are_you_sure')) {
                Requirements::javascript(
                    'silverstripe/userforms:client/dist/js/jquery.are-you-sure/jquery.are-you-sure.js'
                );
            }
        }
    }

    /**
     * Add the necessary jQuery validate i18n translation files, either by locale or by langauge,
     * e.g. 'en_NZ' or 'en'. This adds "methods_abc.min.js" as well as "messages_abc.min.js" from the
     * jQuery validate thirdparty library from dist/js.
     */
    protected function addUserFormsValidatei18n()
    {
        $module = ModuleLoader::getModule('silverstripe/userforms');

        $candidates = [
            i18n::getData()->langFromLocale(i18n::config()->get('default_locale')),
            i18n::config()->get('default_locale'),
            i18n::getData()->langFromLocale(i18n::get_locale()),
            i18n::get_locale(),
        ];

        foreach ($candidates as $candidate) {
            foreach (['messages', 'methods'] as $candidateType) {
                $localisationCandidate = "client/dist/js/jquery-validation/localization/{$candidateType}_{$candidate}.min.js";

                $resource = $module->getResource($localisationCandidate);
                if ($resource->exists()) {
                    Requirements::javascript($resource->getRelativePath());
                }
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
    public function index(HTTPRequest $request = null)
    {
        $form = $this->Form();
        if ($this->Content && $form && !$this->config()->disable_form_content_shortcode) {
            $hasLocation = stristr($this->Content ?? '', '$UserDefinedForm');
            if ($hasLocation) {
                /** @see Requirements_Backend::escapeReplacement */
                $formEscapedForRegex = addcslashes($form->forTemplate() ?? '', '\\$');
                $content = preg_replace(
                    '/(<p[^>]*>)?\\$UserDefinedForm(<\\/p>)?/i',
                    $formEscapedForRegex ?? '',
                    $this->Content ?? ''
                );
                return [
                    'Content' => DBField::create_field('HTMLText', $content),
                    'Form' => ''
                ];
            }
        }

        return [
            'Content' => DBField::create_field('HTMLText', $this->Content),
            'Form' => $this->Form()
        ];
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
     * @return Form
     */
    public function Form()
    {
        $form = UserForm::create($this, 'Form_' . $this->ID);
        $form->setFormAction(Controller::join_links($this->Link(), 'Form'));
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
        $rules = '';
        $form = $this->data();
        if (!$form) {
            return;
        }
        $formFields = $form->Fields();

        $watch = [];

        if ($formFields) {
            foreach ($formFields as $field) {
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
                , 'UserFormsConditional-' . $form->ID);
        }
    }

    /**
     * Returns the maximum size uploaded files can be before they're excluded from CMS configured recipient emails
     *
     * @return int size in megabytes
     */
    public function getMaximumAllowedEmailAttachmentSize()
    {
        return $this->parseByteSizeString($this->config()->get('maximum_email_attachment_size'));
    }

    /**
     * Convert file sizes with a single character for unit size to true byte count.
     * Just as with php.ini and e.g. 128M -> 1024 * 1024 * 128 bytes.
     * @see https://www.php.net/manual/en/faq.using.php#faq.using.shorthandbytes
     *
     * @param string $byteSize
     * @return int bytes
     */
    protected function parseByteSizeString($byteSize)
    {
        // kilo, mega, giga
        $validUnits = 'kmg';
        $valid = preg_match("/^(?<number>\d+)((?<unit>[$validUnits])b?)?$/i", $byteSize, $matches);
        if (!$valid) {
            throw new \InvalidArgumentException(
                "Expected a positive integer followed optionally by K, M, or G. Found '$byteSize' instead"
            );
        }
        $power = 0;
        // prepend b for bytes to $validUnits to give correct mapping of ordinal position to exponent
        if (isset($matches['unit'])) {
            $power = stripos("b$validUnits", $matches['unit']);
        }
        return intval($matches['number']) * pow(1024, $power);
    }

    /**
     * Process the form that is submitted through the site
     *
     * {@see UserForm::validate()} for validation step prior to processing
     *
     * @param array $data
     * @param Form $form
     *
     * @return HTTPResponse
     */
    public function process($data, $form)
    {
        $submittedForm = SubmittedForm::create();
        $submittedForm->SubmittedByID = Security::getCurrentUser() ? Security::getCurrentUser()->ID : 0;
        $submittedForm->ParentClass = get_class($this->data());
        $submittedForm->ParentID = $this->ID;

        // if saving is not disabled save now to generate the ID
        if (!$this->DisableSaveSubmissions) {
            $submittedForm->write();
        }

        $attachments = [];
        $submittedFields = ArrayList::create();

        foreach ($this->data()->Fields() as $field) {
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

            // set visibility flag according to display rules
            $submittedField->Displayed = $field->isDisplayed($data);

            if (!empty($data[$field->Name])) {
                if (in_array(EditableFileField::class, $field->getClassAncestry() ?? [])) {
                    if (!empty($_FILES[$field->Name]['name'])) {
                        if (!$field->getFolderExists()) {
                            $field->createProtectedFolder();
                        }

                        $file = Versioned::withVersionedMode(function () use ($field, $form) {
                            $stage = Injector::inst()->get(UserDefinedFormController::class)->config()->get('file_upload_stage');
                            Versioned::set_stage($stage);

                            $foldername = $field->getFormField()->getFolderName();
                            // create the file from post data
                            $upload = Upload::create();
                            try {
                                $upload->loadIntoFile($_FILES[$field->Name], null, $foldername);
                            } catch (ValidationException $e) {
                                $validationResult = $e->getResult();
                                foreach ($validationResult->getMessages() as $message) {
                                    $form->sessionMessage($message['message'], ValidationResult::TYPE_ERROR);
                                }
                                Controller::curr()->redirectBack();
                                return null;
                            }
                            /** @var AssetContainer|File $file */
                            $file = $upload->getFile();
                            $file->ShowInSearch = 0;
                            $file->UserFormUpload = UserFormFileExtension::USER_FORM_UPLOAD_TRUE;
                            $file->write();

                            return $file;
                        });

                        if (is_null($file)) {
                            return;
                        }

                        // generate image thumbnail to show in asset-admin
                        // you can run userforms without asset-admin, so need to ensure asset-admin is installed
                        if (class_exists(AssetAdmin::class)) {
                            AssetAdmin::singleton()->generateThumbnails($file);
                        }

                        // write file to form field
                        $submittedField->UploadedFileID = $file->ID;

                        // attach a file to recipient email only if lower than configured size
                        if ($file->getAbsoluteSize() <= $this->getMaximumAllowedEmailAttachmentSize()) {
                            // using the field name as array index is fine as file upload field only allows one file
                            $attachments[$field->Name] = $file;
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

        $visibleSubmittedFields = $submittedFields->filter('Displayed', true);

        $emailData = [
            'Sender' => Security::getCurrentUser(),
            'HideFormData' => false,
            'SubmittedForm' => $submittedForm,
            'Fields' => $submittedFields,
            'Body' => '',
        ];

        $this->extend('updateEmailData', $emailData, $attachments);

        // email users on submit.
        if ($recipients = $this->FilteredEmailRecipients($data, $form)) {
            foreach ($recipients as $recipient) {
                $email = Email::create()
                    ->setHTMLTemplate('email/SubmittedFormEmail')
                    ->setPlainTemplate('email/SubmittedFormEmailPlain');

                // Merge fields are used for CMS authors to reference specific form fields in email content
                $mergeFields = $this->getMergeFieldsMap($emailData['Fields']);

                if ($attachments && (bool) $recipient->HideFormData === false) {
                    foreach ($attachments as $uploadFieldName => $file) {
                        /** @var File $file */
                        if ((int) $file->ID === 0) {
                            continue;
                        }

                        $canAttachFileForRecipient = true;
                        $this->extend('updateCanAttachFileForRecipient', $canAttachFileForRecipient, $recipient, $uploadFieldName, $file);

                        if ($canAttachFileForRecipient) {
                            $email->addAttachmentFromData(
                                $file->getString(),
                                $file->getFilename(),
                                $file->getMimeType()
                            );
                        }
                    }
                }

                if (!$recipient->SendPlain && $recipient->emailTemplateExists()) {
                    $email->setHTMLTemplate($recipient->EmailTemplate);
                }

                // Add specific template data for the current recipient
                $emailData['HideFormData'] =  (bool) $recipient->HideFormData;
                // Include any parsed merge field references from the CMS editor - this is already escaped
                // This string substitution works for both HTML and plain text emails.
                // $recipient->getEmailBodyContent() will retrieve the relevant version of the email
                $engine = Injector::inst()->create(TemplateEngine::class);
                $emailData['Body'] = $engine->renderString($recipient->getEmailBodyContent(), ViewLayerData::create($mergeFields));
                // only include visible fields if recipient visibility flag is set
                if ((bool) $recipient->HideInvisibleFields) {
                    $emailData['Fields'] = $visibleSubmittedFields;
                }

                // Push the template data to the Email's data
                foreach ($emailData as $key => $value) {
                    $email->addData($key, $value);
                }

                // check to see if they are a dynamic reply to. eg based on a email field a user selected
                $emailFrom = $recipient->SendEmailFromField();
                if ($emailFrom && $emailFrom->exists()) {
                    $submittedFormField = $submittedFields->find('Name', $recipient->SendEmailFromField()->Name);

                    if ($submittedFormField && $submittedFormField->Value && is_string($submittedFormField->Value)) {
                        $emailSendTo = $this->validEmailsToArray($submittedFormField->Value);
                        $email->addReplyTo(...$emailSendTo);
                    }
                } elseif ($recipient->EmailReplyTo) {
                    $emailReplyTo = $this->validEmailsToArray($recipient->EmailReplyTo);
                    $email->addReplyTo(...$emailReplyTo);
                }

                // check for a specified from; otherwise fall back to server defaults
                if ($recipient->EmailFrom) {
                    $email->setFrom($this->validEmailsToArray($recipient->EmailFrom));
                }

                // check to see if they are a dynamic reciever eg based on a dropdown field a user selected
                $emailTo = $recipient->SendEmailToField();

                try {
                    if ($emailTo && $emailTo->exists()) {
                        $submittedFormField = $submittedFields->find('Name', $recipient->SendEmailToField()->Name);

                        if ($submittedFormField && is_string($submittedFormField->Value)) {
                            $email->setTo($this->validEmailsToArray($submittedFormField->Value));
                        } else {
                            $email->setTo($this->validEmailsToArray($recipient->EmailAddress));
                        }
                    } else {
                        $email->setTo($this->validEmailsToArray($recipient->EmailAddress));
                    }
                } catch (Swift_RfcComplianceException $e) {
                    // The sending address is empty and/or invalid. Log and skip sending.
                    $error = sprintf(
                        'Failed to set sender for userform submission %s: %s',
                        $submittedForm->ID,
                        $e->getMessage()
                    );

                    Injector::inst()->get(LoggerInterface::class)->notice($error);

                    continue;
                }

                // check to see if there is a dynamic subject
                $emailSubject = $recipient->SendEmailSubjectField();
                if ($emailSubject && $emailSubject->exists()) {
                    $submittedFormField = $submittedFields->find('Name', $recipient->SendEmailSubjectField()->Name);

                    if ($submittedFormField && trim($submittedFormField->Value ?? '')) {
                        $email->setSubject($submittedFormField->Value);
                    } else {
                        $email->setSubject($engine->renderString($recipient->EmailSubject, ViewLayerData::create($mergeFields)));
                    }
                } else {
                    $email->setSubject($engine->renderString($recipient->EmailSubject, ViewLayerData::create($mergeFields)));
                }

                $this->extend('updateEmail', $email, $recipient, $emailData);

                if ((bool)$recipient->SendPlain) {
                    // decode previously encoded html tags because the email is being sent as text/plain
                    $body = html_entity_decode($emailData['Body'] ?? '') . "\n";
                    if (isset($emailData['Fields']) && !$emailData['HideFormData']) {
                        foreach ($emailData['Fields'] as $field) {
                            if ($field instanceof SubmittedFileField) {
                                $body .= $field->Title . ': ' . $field->ExportValue ." \n";
                            } else {
                                $body .= $field->Title . ': ' . $field->Value . " \n";
                            }
                        }
                    }

                    $email->setBody($body);

                    try {
                        $email->sendPlain();
                    } catch (Exception $e) {
                        Injector::inst()->get(LoggerInterface::class)->error($e);
                    }
                } else {
                    try {
                        $email->send();
                    } catch (Exception $e) {
                        Injector::inst()->get(LoggerInterface::class)->error($e);
                    }
                }
            }
        }

        $submittedForm->extend('updateAfterProcess', $emailData, $attachments);

        $session = $this->getRequest()->getSession();
        $session->clear("FormInfo.{$form->FormName()}.errors");
        $session->clear("FormInfo.{$form->FormName()}.data");

        $referrer = (isset($data['Referrer'])) ? '?referrer=' . urlencode($data['Referrer']) : "";

        // set a session variable from the security ID to stop people accessing
        // the finished method directly.
        if (!$this->DisableAuthenicatedFinishAction) {
            if (isset($data['SecurityID'])) {
                $session->set('FormProcessed', $data['SecurityID']);
            } else {
                // if the form has had tokens disabled we still need to set FormProcessed
                // to allow us to get through the finshed method
                if (!$this->Form()->getSecurityToken()->isEnabled()) {
                    $randNum = rand(1, 1000);
                    $randHash = md5($randNum ?? '');
                    $session->set('FormProcessed', $randHash);
                    $session->set('FormProcessedNum', $randNum);
                }
            }
        }

        if (!$this->DisableSaveSubmissions) {
            $session->set('userformssubmission'. $this->ID, $submittedForm->ID);
        }

        return $this->redirect($this->Link('finished') . $referrer . $this->config()->get('finished_anchor'));
    }

    /**
     * Allows the use of field values in email body.
     *
     * @param ArrayList $fields
     * @return ArrayData
     */
    protected function getMergeFieldsMap($fields = [])
    {
        $data = ArrayData::create([]);

        foreach ($fields as $field) {
            $data->setField($field->Name, DBField::create_field('Text', $field->Value));
        }

        return $data;
    }

    /**
     * This action handles rendering the "finished" message, which is
     * customizable by editing the ReceivedFormSubmission template.
     *
     * @return ModelData
     */
    public function finished()
    {
        $submission = $this->getRequest()->getSession()->get('userformssubmission'. $this->ID);

        if ($submission) {
            $submission = SubmittedForm::get()->byId($submission);
        }

        $referrer = isset($_GET['referrer']) ? urldecode($_GET['referrer']) : null;

        if (!$this->DisableAuthenicatedFinishAction) {
            $formProcessed = $this->getRequest()->getSession()->get('FormProcessed');

            if (!isset($formProcessed)) {
                return $this->redirect($this->Link() . $referrer);
            } else {
                $securityID = $this->getRequest()->getSession()->get('SecurityID');
                // make sure the session matches the SecurityID and is not left over from another form
                if ($formProcessed != $securityID) {
                    // they may have disabled tokens on the form
                    $securityID = md5($this->getRequest()->getSession()->get('FormProcessedNum') ?? '');
                    if ($formProcessed != $securityID) {
                        return $this->redirect($this->Link() . $referrer);
                    }
                }
            }

            $this->getRequest()->getSession()->clear('FormProcessed');
        }

        $data = [
            'Submission' => $submission,
            'Link' => $referrer
        ];

        $this->extend('updateReceivedFormSubmissionData', $data);

        return $this->customise([
            'Content' => $this->customise($data)->renderWith(__CLASS__ . '_ReceivedFormSubmission'),
            'Form' => '',
        ]);
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
            $holder = $rule['holder'];
            $isFormStep = strpos($target ?? '', 'EditableFormStep') !== false;

            $result .= <<<EOS
\n
    $('.userform').on('{$events}',
    "{$selectors}",
    function (){
        if ({$operations}) {
            $('{$target}').{$rule['view']};
            {$holder}.{$rule['view']}.trigger('{$rule['holder_event']}');
        } else {
            $('{$target}').{$rule['opposite']};
            {$holder}.{$rule['opposite']}.trigger('{$rule['holder_event_opposite']}');
        }
    });
EOS;
            if ($isFormStep) {
                // Hide the step jump button if the FormStep has is initially hidden.
                // This is particularly important beacause the next/prev page buttons logic is controlled by
                // the visibility of the FormStep buttons
                // The HTML for the FormStep buttons is defined in the UserFormProgress template
                $id = str_replace('#', '', $target ?? '');
                $result .= <<<EOS
    $('.step-button-wrapper[data-for="{$id}"]').addClass('hide');
EOS;
            } else {
                // If a field's initial state is set to be hidden, a '.hide' class will be added to the field as well
                // as the fieldholder. Afterwards, JS only removes it from the fieldholder, thus the field stays hidden.
                // We'll update update the JS so that the '.hide' class is removed from the field from the beginning,
                // though we need to ensure we don't do this on FormSteps (page breaks) otherwise we'll mistakenly
                // target fields contained within the formstep
                $result .= <<<EOS
    $("{$target}").find('.hide').removeClass('hide');
EOS;
            }
        }

        return $result;
    }

    /**
     * Check validity of email and return array of valid emails
     */
    private function validEmailsToArray(?string $emails): array
    {
        $emailsArray = [];
        $emails = explode(',', $emails ?? '');
        foreach ($emails as $email) {
            $email = trim($email);
            if (Email::is_valid_address($email)) {
                $emailsArray[] = $email;
            }
        }

        return $emailsArray;
    }
}
