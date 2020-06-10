<?php

namespace SilverStripe\UserForms\Control;

use PageController;
use Psr\Log\LoggerInterface;
use SilverStripe\AssetAdmin\Controller\AssetAdmin;
use SilverStripe\Admin\LeftAndMain;
use SilverStripe\Assets\File;
use SilverStripe\Assets\Folder;
use SilverStripe\Assets\Upload;
use SilverStripe\Control\Controller;
use SilverStripe\Control\Email\Email;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse_Exception;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Core\Manifest\ModuleLoader;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\HiddenField;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\OptionsetField;
use SilverStripe\Forms\Schema\FormSchema;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\TreeDropdownField;
use SilverStripe\i18n\i18n;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\FieldType\DBField;
use SilverStripe\ORM\ValidationException;
use SilverStripe\ORM\ValidationResult;
use SilverStripe\Security\Group;
use SilverStripe\Security\InheritedPermissions;
use SilverStripe\Security\Permission;
use SilverStripe\Security\PermissionFailureException;
use SilverStripe\Security\Security;
use SilverStripe\UserForms\Extension\UserFormFileExtension;
use SilverStripe\UserForms\Form\UserForm;
use SilverStripe\UserForms\Model\EditableFormField;
use SilverStripe\UserForms\Model\EditableFormField\EditableFileField;
use SilverStripe\UserForms\Model\Submission\SubmittedForm;
use SilverStripe\UserForms\Model\UserDefinedForm;
use SilverStripe\Versioned\Versioned;
use SilverStripe\View\ArrayData;
use SilverStripe\View\Requirements;
use SilverStripe\View\SSViewer;
use SilverStripe\View\ViewableData;
use Swift_RfcComplianceException;

/**
 * Controller for the {@link UserDefinedForm} page type.
 *
 * @package userforms
 */
class UserDefinedFormController extends PageController
{
    private static $finished_anchor = '#uff';

    private static $allowed_actions = [
        'index',
        'ping',
        'Form',
        'finished',
        'confirmfolderform' => 'CMS_ACCESS_CMSMain',
        'confirmfolder' => 'CMS_ACCESS_CMSMain',
        'getfoldergrouppermissions' => 'CMS_ACCESS_CMSMain',
    ];

    /** @var string The name of the folder where form submissions will be placed by default */
    private static $form_submissions_folder = 'Form-submissions';

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
            Requirements::javascript('//code.jquery.com/jquery-3.4.1.min.js');
            Requirements::javascript(
                'silverstripe/userforms:client/thirdparty/jquery-validate/jquery.validate.min.js'
            );
            Requirements::javascript('silverstripe/admin:client/dist/js/i18n.js');
            Requirements::add_i18n_javascript('silverstripe/userforms:client/lang');
            Requirements::javascript('silverstripe/userforms:client/dist/js/userforms.js');

            $this->addUserFormsValidatei18n();

            // Bind a confirmation message when navigating away from a partially completed form.
            if ($page::config()->get('enable_are_you_sure')) {
                Requirements::javascript(
                    'silverstripe/userforms:client/thirdparty/jquery.are-you-sure/jquery.are-you-sure.js'
                );
            }
        }
    }

    /**
     * Add the necessary jQuery validate i18n translation files, either by locale or by langauge,
     * e.g. 'en_NZ' or 'en'. This adds "methods_abc.min.js" as well as "messages_abc.min.js" from the
     * jQuery validate thirdparty library.
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
                $localisationCandidate = "client/thirdparty/jquery-validate/localization/{$candidateType}_{$candidate}.min.js";

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
            $hasLocation = stristr($this->Content, '$UserDefinedForm');
            if ($hasLocation) {
                /** @see Requirements_Backend::escapeReplacement */
                $formEscapedForRegex = addcslashes($form->forTemplate(), '\\$');
                $content = preg_replace(
                    '/(<p[^>]*>)?\\$UserDefinedForm(<\\/p>)?/i',
                    $formEscapedForRegex,
                    $this->Content
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
        /** @skipUpgrade */
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
            /** @var EditableFormField $field */
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

            if (!empty($data[$field->Name])) {
                if (in_array(EditableFileField::class, $field->getClassAncestry())) {
                    if (!empty($_FILES[$field->Name]['name'])) {
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
                            return;
                        }
                        /** @var AssetContainer|File $file */
                        $file = $upload->getFile();
                        $file->ShowInSearch = 0;
                        $file->UserFormUpload = UserFormFileExtension::USER_FORM_UPLOAD_TRUE;
                        $file->write();

                        // generate image thumbnail to show in asset-admin
                        // you can run userforms without asset-admin, so need to ensure asset-admin is installed
                        if (class_exists(AssetAdmin::class)) {
                            AssetAdmin::singleton()->generateThumbnails($file);
                        }

                        // write file to form field
                        $submittedField->UploadedFileID = $file->ID;

                        // attach a file only if lower than 1MB
                        if ($file->getAbsoluteSize() < 1024 * 1024 * 1) {
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

        $emailData = [
            'Sender' => Security::getCurrentUser(),
            'HideFormData' => false,
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

                if ($attachments) {
                    foreach ($attachments as $file) {
                        /** @var File $file */
                        if ((int) $file->ID === 0) {
                            continue;
                        }

                        $email->addAttachmentFromData(
                            $file->getString(),
                            $file->getFilename(),
                            $file->getMimeType()
                        );
                    }
                }

                if (!$recipient->SendPlain && $recipient->emailTemplateExists()) {
                    $email->setHTMLTemplate($recipient->EmailTemplate);
                }

                // Add specific template data for the current recipient
                $emailData['HideFormData'] =  (bool) $recipient->HideFormData;
                // Include any parsed merge field references from the CMS editor - this is already escaped
                $emailData['Body'] = SSViewer::execute_string($recipient->getEmailBodyContent(), $mergeFields);

                // Push the template data to the Email's data
                foreach ($emailData as $key => $value) {
                    $email->addData($key, $value);
                }

                // check to see if they are a dynamic reply to. eg based on a email field a user selected
                $emailFrom = $recipient->SendEmailFromField();
                if ($emailFrom && $emailFrom->exists()) {
                    $submittedFormField = $submittedFields->find('Name', $recipient->SendEmailFromField()->Name);

                    if ($submittedFormField && is_string($submittedFormField->Value)) {
                        $email->setReplyTo(explode(',', $submittedFormField->Value));
                    }
                } elseif ($recipient->EmailReplyTo) {
                    $email->setReplyTo(explode(',', $recipient->EmailReplyTo));
                }

                // check for a specified from; otherwise fall back to server defaults
                if ($recipient->EmailFrom) {
                    $email->setFrom(explode(',', $recipient->EmailFrom));
                }

                // check to see if they are a dynamic reciever eg based on a dropdown field a user selected
                $emailTo = $recipient->SendEmailToField();

                try {
                    if ($emailTo && $emailTo->exists()) {
                        $submittedFormField = $submittedFields->find('Name', $recipient->SendEmailToField()->Name);

                        if ($submittedFormField && is_string($submittedFormField->Value)) {
                            $email->setTo(explode(',', $submittedFormField->Value));
                        } else {
                            $email->setTo(explode(',', $recipient->EmailAddress));
                        }
                    } else {
                        $email->setTo(explode(',', $recipient->EmailAddress));
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

                    if ($submittedFormField && trim($submittedFormField->Value)) {
                        $email->setSubject($submittedFormField->Value);
                    } else {
                        $email->setSubject(SSViewer::execute_string($recipient->EmailSubject, $mergeFields));
                    }
                } else {
                    $email->setSubject(SSViewer::execute_string($recipient->EmailSubject, $mergeFields));
                }

                $this->extend('updateEmail', $email, $recipient, $emailData);

                if ((bool)$recipient->SendPlain) {
                    $body = strip_tags($recipient->getEmailBodyContent()) . "\n";
                    if (isset($emailData['Fields']) && !$emailData['HideFormData']) {
                        foreach ($emailData['Fields'] as $field) {
                            $body .= $field->Title . ': ' . $field->Value . " \n";
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
                    $randHash = md5($randNum);
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
     * @return ViewableData
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
                    $securityID = md5($this->getRequest()->getSession()->get('FormProcessedNum'));
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
     * Returns a TextField for entering a folder name.
     * @param string $folder The current folder to set the field to
     * @param string $title The title of the text field
     * @return TextField
     */
    private static function getRestrictedAccessField(string $folder, string $title)
    {
        /** @var TextField $textField */
        $textField = TextField::create('CreateFolder', '');

        /** @var Folder $formSubmissionsFolder */
        $formSubmissionsFolder = Folder::find($folder);
        $textField->setDescription(EditableFileField::getFolderPermissionString($formSubmissionsFolder));
        $textField->addExtraClass('pt-2');
        $textField->setSchemaData([
            'data' => [
                'prefix' => static::config()->get('form_submissions_folder') . '/',
            ],
            'attributes' => [
                'placeholder' => $title
            ]
        ]);

        return $textField;
    }

    /**
     * This returns a Confirm Folder form used to verify the upload folder for EditableFileFields
     *
     * @return ViewableData
     */
    public function confirmfolderform()
    {
        $request = $this->getRequest();
        $id = $request->requestVar('ID');
        if (!$id) {
            throw new HTTPResponse_Exception(_t(__CLASS__.'.INVALID_REQUEST', 'This request was invalid.'), 400);
        }
        $userFormID = $request->requestVar('UserFormID');
        if (!$userFormID) {
            throw new HTTPResponse_Exception(_t(__CLASS__.'.INVALID_REQUEST', 'This request was invalid.'), 400);
        }
        $userForm = UserDefinedForm::get()->byID($userFormID);
        if (!$userForm) {
            $userForm = Versioned::get_by_stage(UserDefinedForm::class, Versioned::DRAFT)->byID($userFormID);
        }
        if (!$userForm) {
            throw new HTTPResponse_Exception(_t(__CLASS__.'.INVALID_REQUEST', 'This request was invalid.'), 400);
        }

        if (!$userForm->canEdit()) {
            throw new PermissionFailureException();
        }

        $editableFormField = EditableFormField::get()->filter(['ID' => $id, 'ParentID' => $userFormID])->first();
        if (!$editableFormField) {
            $editableFormField = Versioned::get_by_stage(EditableFormField::class, Versioned::DRAFT)
                ->filter(['ID' => $id, 'ParentID' => $userFormID])->first();
        }

        $folderId = 0;
        if (!$editableFormField) {
            throw new HTTPResponse_Exception(_t(__CLASS__.'.INVALID_REQUEST', 'This request was invalid.'), 400);
        }

        if ($editableFormField instanceof EditableFileField) {
            $folderId = $editableFormField->FolderID;
        }
        /** @var Folder $folder */
        $folder = Folder::get()->byID($folderId);
        if (!$folder) {
            $folder = $this->getFormSubmissionFolder();
        }

        $fields = FieldList::create();

        $labelA = LiteralField::create('LabelA', _t(__CLASS__.'.CONFIRM_FOLDER_LABEL_A', 'Files that your users upload should be stored carefully to reduce the risk of exposing sensitive data. Ensure the folder you select can only be viewed by appropriate parties. Folder permissions can be managed within the Files area.'));
        $labelA->addExtraClass(' mb-2');
        $fields->push($labelA);

        $labelB = LiteralField::create('LabelB', _t(__CLASS__.'.CONFIRM_FOLDER_LABEL_B', 'The folder selected will become the default for this form. This can be changed on an individual basis in the <i>File upload field.</i>'));
        $labelB->addExtraClass(' mb-3');
        $fields->push($labelB);

        $fields->push(static::getRestrictedAccessField($this->config()->get('form_submissions_folder'), $userForm->Title));

        $options = OptionsetField::create('FolderOptions', _t(__CLASS__.'.FOLDER_OPTIONS_TITLE', 'Form folder options'), [
            "new" => _t(__CLASS__.'.FOLDER_OPTIONS_NEW', 'Create a new folder (recommended)'),
            "existing" => _t(__CLASS__.'.FOLDER_OPTIONS_EXISTING', 'Use an existing folder')
        ], "new");
        $fields->push($options);


        $treeView = TreeDropdownField::create(
            'FolderID',
            '',
            Folder::class
        )->setValue($folder->ID);
        $treeView->addExtraClass('pt-1');
        $treeView->setDescription(EditableFileField::getFolderPermissionString($folder));
        $fields->push($treeView);


        $fields->push(HiddenField::create('ID', 'ID', $editableFormField->ID));

        $submitAction = FormAction::create('confirmfolder', _t(__CLASS__.'.FORM_ACTION_CONFIRM', 'Save and continue'));
        $submitAction->setUseButtonTag(false);
        $submitAction->addExtraClass('btn');
        $submitAction->addExtraClass('btn-primary');

        $cancelAction = FormAction::create("cancel", _t('SilverStripe\\CMS\\Controllers\\CMSMain.Cancel', "Cancel"))
            ->addExtraClass('btn-secondary')
            ->setUseButtonTag(true);

        $form = Form::create($this, 'ConfirmFolderForm', $fields, FieldList::create($submitAction, $cancelAction));
        $form->setFormAction("UserDefinedFormController/confirmfolder");
        $form->addExtraClass('form--no-dividers');


        if (!$editableFormField instanceof EditableFileField) {
            $editableFormField = $editableFormField->newClassInstance(EditableFileField::class);
            $editableFormField->write();
        }
        $treeView->setSchemaData([
            'data' => [
                'urlTree' => "admin/pages/edit/EditForm/$userFormID/field/Fields/item/$id/ItemEditForm/field/FolderID/tree"
            ]
        ]);


        // create the schema response
        $parts = $this->getRequest()->getHeader(LeftAndMain::SCHEMA_HEADER);
        $schemaID = $this->getRequest()->getURL();
        $data = FormSchema::singleton()
            ->getMultipartSchema($parts, $schemaID, $form);

        // return the schema response
        $response = HTTPResponse::create(json_encode($data));
        $response->addHeader('Content-Type', 'application/json');
        return $response;
    }

    /**
     * Sets the selected folder as the upload folder for an EditableFileField
     * @return HTTPResponse
     * @throws ValidationException
     */
    public function confirmfolder()
    {
        if (!Permission::checkMember(null, "CMS_ACCESS_AssetAdmin")) {
            throw new PermissionFailureException();
        }

        $request = $this->getRequest();

        // retrieve the EditableFileField
        $id = $request->requestVar('ID');
        if (!$id) {
            throw new HTTPResponse_Exception(_t(__CLASS__.'.INVALID_REQUEST', 'This request was invalid.'), 400);
        }
        /** @var EditableFileField $editableFileField */
        $editableFormField = EditableFormField::get()->byID($id);
        if (!$editableFormField) {
            $editableFormField = Versioned::get_by_stage(EditableFormField::class, Versioned::DRAFT)->byID($id);
        }
        if (!$editableFormField) {
            throw new HTTPResponse_Exception(_t(__CLASS__.'.INVALID_REQUEST', 'This request was invalid.'), 400);
        }
        // change the class if it is incorrect
        if (!$editableFormField instanceof EditableFileField) {
            $editableFormField = $editableFormField->newClassInstance(EditableFileField::class);
        }
        if (!$editableFormField) {
            throw new HTTPResponse_Exception(_t(__CLASS__.'.INVALID_REQUEST', 'This request was invalid.'), 400);
        }
        $editableFileField = $editableFormField;

        if (!$editableFileField->canEdit()) {
            throw new PermissionFailureException();
        }

        // check if we're creating a new folder or using an existing folder
        $option = $request->requestVar('FolderOptions');
        if ($option === 'existing') {
            // set existing folder
            $folderID = $request->requestVar('FolderID');
            if ($folderID != 0) {
                $folder = Folder::get()->byID($folderID);
                if (!$folder) {
                    throw new HTTPResponse_Exception(_t(__CLASS__.'.INVALID_REQUEST', 'This request was invalid.'), 400);
                }
            }
        } else {
            // create the folder
            $createFolder = $request->requestVar('CreateFolder') ?: $editableFormField->Parent()->Title;
            $folder = $this->getFormSubmissionFolder($createFolder);
        }

        // assign the folder
        $editableFileField->FolderID = isset($folder) ? $folder->ID : 0;
        $editableFileField->write();

        // respond
        $response = HTTPResponse::create(json_encode([]));
        $response->addHeader('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @return HTTPResponse
     */
    public function getfoldergrouppermissions()
    {
        $folderID = $this->getRequest()->requestVar('FolderID');
        if ($folderID) {
            /** @var Folder $folder */
            $folder = Folder::get()->byID($folderID);
            if (!$folder) {
                throw new HTTPResponse_Exception(_t(__CLASS__.'.INVALID_REQUEST', 'This request was invalid.'), 400);
            }
            if (!$folder->canView()) {
                throw new PermissionFailureException();
            }
        } else {
            $folder = null;
        }

        // respond
        $response = HTTPResponse::create(json_encode(EditableFileField::getFolderPermissionString($folder)));
        $response->addHeader('Content-Type', 'application/json');
        return $response;
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
    $("{$target}").find('.hide').removeClass('hide');
EOS;
        }

        return $result;
    }

    /**
     * @throws ValidationException
     */
    private static function updateFormSubmissionFolderPermissions()
    {
        // ensure the FormSubmissions folder is only accessible to Administrators
        $formSubmissionsFolder = Folder::find(self::config()->get('form_submissions_folder'));
        $formSubmissionsFolder->CanViewType = InheritedPermissions::ONLY_THESE_USERS;
        $formSubmissionsFolder->ViewerGroups()->removeAll();
        $formSubmissionsFolder->ViewerGroups()->add(Group::get_one(Group::class, ['"Code"' => 'administrators']));
        $formSubmissionsFolder->write();
    }

    /**
     * Returns the form submission folder or a sub folder if provided.
     * Creates the form submission folder if it doesn't exist.
     * Updates the form submission folder permissions if it is created.
     * @param string $subFolder Sub-folder to be created or returned.
     * @return Folder
     * @throws ValidationException
     */
    public static function getFormSubmissionFolder(string $subFolder = null): ?Folder
    {
        $folderPath = self::config()->get('form_submissions_folder');
        if ($subFolder) {
            $folderPath .= '/' . $subFolder;
        }
        $formSubmissionsFolderExists = !!Folder::find(self::config()->get('form_submissions_folder'));
        $folder = Folder::find_or_make($folderPath);

        // Set default permissions if this is the first time we create the form submission folder
        if (!$formSubmissionsFolderExists) {
            self::updateFormSubmissionFolderPermissions();
            // Make sure we return the folder with the latest permission
            $folder = Folder::find($folderPath);
        }

        return $folder;
    }
}
