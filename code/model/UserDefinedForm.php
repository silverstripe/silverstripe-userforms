<?php

/**
 * @package userforms
 */

class UserDefinedForm extends Page
{
    
    /**
     * @var string
     */
    private static $icon = 'userforms/images/sitetree_icon.png';

    /**
     * @var string
     */
    private static $description = 'Adds a customizable form.';

    /**
     * @var string Required Identifier
     */
    private static $required_identifier = null;

    /**
     * @var string
     */
    private static $email_template_directory = 'userforms/templates/email/';

    /**
     * Should this module automatically upgrade on dev/build?
     *
     * @config
     * @var bool
     */
    private static $upgrade_on_build = true;

    /**
     * Built in extensions required by this page
     * @config
     * @var array
     */
    private static $extensions = array(
        'UserFormFieldEditorExtension'
    );

    /**
     * @var array Fields on the user defined form page.
     */
    private static $db = array(
        "SubmitButtonText" => "Varchar",
        "ClearButtonText" => "Varchar",
        "OnCompleteMessage" => "HTMLText",
        "ShowClearButton" => "Boolean",
        'DisableSaveSubmissions' => 'Boolean',
        'EnableLiveValidation' => 'Boolean',
        'HideFieldLabels' => 'Boolean',
        'DisplayErrorMessagesAtTop' => 'Boolean',
        'DisableAuthenicatedFinishAction' => 'Boolean',
        'DisableCsrfSecurityToken' => 'Boolean'
    );

    /**
     * @var array Default values of variables when this page is created
     */
    private static $defaults = array(
        'Content' => '$UserDefinedForm',
        'DisableSaveSubmissions' => 0,
        'OnCompleteMessage' => '<p>Thanks, we\'ve received your submission.</p>'
    );

    /**
     * @var array
     */
    private static $has_many = array(
        "Submissions" => "SubmittedForm",
        "EmailRecipients" => "UserDefinedForm_EmailRecipient"
    );

    /**
     * @var array
     * @config
     */
    private static $casting = array(
        'ErrorContainerID' => 'Text'
    );

    /**
     * Error container selector which matches the element for grouped messages
     *
     * @var string
     * @config
     */
    private static $error_container_id = 'error-container';

    /**
     * The configuration used to determine whether a confirmation message is to
     * appear when navigating away from a partially completed form.
     *
     * @var boolean
     * @config
     */
    private static $enable_are_you_sure = true;

    /**
     * @var bool
     * @config
     */
    private static $recipients_warning_enabled = false;

    /**
     * Temporary storage of field ids when the form is duplicated.
     * Example layout: array('EditableCheckbox3' => 'EditableCheckbox14')
     * @var array
     */
    protected $fieldsFromTo = array();

    /**
     * @return FieldList
     */
     public function getCMSFields()
     {
         Requirements::css(USERFORMS_DIR . '/css/UserForm_cms.css');

         $self = $this;

         $this->beforeUpdateCMSFields(function ($fields) use ($self) {

            // define tabs
            $fields->findOrMakeTab('Root.FormOptions', _t('UserDefinedForm.CONFIGURATION', 'Configuration'));
            $fields->findOrMakeTab('Root.Recipients', _t('UserDefinedForm.RECIPIENTS', 'Recipients'));
            $fields->findOrMakeTab('Root.Submissions', _t('UserDefinedForm.SUBMISSIONS', 'Submissions'));

            // text to show on complete
            $onCompleteFieldSet = new CompositeField(
                $label = new LabelField('OnCompleteMessageLabel', _t('UserDefinedForm.ONCOMPLETELABEL', 'Show on completion')),
                $editor = new HtmlEditorField('OnCompleteMessage', '', _t('UserDefinedForm.ONCOMPLETEMESSAGE', $self->OnCompleteMessage))
            );

            $onCompleteFieldSet->addExtraClass('field');

            $editor->setRows(3);
            $label->addExtraClass('left');

            // Define config for email recipients
            $emailRecipientsConfig = GridFieldConfig_RecordEditor::create(10);
            $emailRecipientsConfig->getComponentByType('GridFieldAddNewButton')
                ->setButtonName(
                    _t('UserDefinedForm.ADDEMAILRECIPIENT', 'Add Email Recipient')
                );

            // who do we email on submission
            $emailRecipients = new GridField(
                'EmailRecipients',
                _t('UserDefinedForm.EMAILRECIPIENTS', 'Email Recipients'),
                $self->EmailRecipients(),
                $emailRecipientsConfig
            );
            $emailRecipients
                ->getConfig()
                ->getComponentByType('GridFieldDetailForm')
                ->setItemRequestClass('UserFormRecipientItemRequest');

            $fields->addFieldsToTab('Root.FormOptions', $onCompleteFieldSet);
            $fields->addFieldToTab('Root.Recipients', $emailRecipients);
            $fields->addFieldsToTab('Root.FormOptions', $self->getFormOptions());


            // view the submissions
            $submissions = new GridField(
                'Submissions',
                _t('UserDefinedForm.SUBMISSIONS', 'Submissions'),
                 $self->Submissions()->sort('Created', 'DESC')
            );

            // make sure a numeric not a empty string is checked against this int column for SQL server
            $parentID = (!empty($self->ID)) ? (int) $self->ID : 0;

            // get a list of all field names and values used for print and export CSV views of the GridField below.
            $columnSQL = <<<SQL
SELECT "SubmittedFormField"."Name" as "Name", "SubmittedFormField"."Title" as "Title", COALESCE("EditableFormField"."Sort", 999) AS "Sort"
FROM "SubmittedFormField"
LEFT JOIN "SubmittedForm" ON "SubmittedForm"."ID" = "SubmittedFormField"."ParentID"
LEFT JOIN "EditableFormField" ON "EditableFormField"."Title" = "SubmittedFormField"."Title" AND "EditableFormField"."ParentID" = '$parentID'
WHERE "SubmittedForm"."ParentID" = '$parentID'
ORDER BY "Sort", "Title"
SQL;
            // Sanitise periods in title
            $columns = array();
            foreach (DB::query($columnSQL)->map() as $name => $title) {
                $columns[$name] = trim(strtr($title, '.', ' '));
            }

            $config = new GridFieldConfig();
            $config->addComponent(new GridFieldToolbarHeader());
            $config->addComponent($sort = new GridFieldSortableHeader());
            $config->addComponent($filter = new UserFormsGridFieldFilterHeader());
            $config->addComponent(new GridFieldDataColumns());
            $config->addComponent(new GridFieldEditButton());
            $config->addComponent(new GridFieldDeleteAction());
            $config->addComponent(new GridFieldPageCount('toolbar-header-right'));
            $config->addComponent($pagination = new GridFieldPaginator(25));
            $config->addComponent(new GridFieldDetailForm());
            $config->addComponent(new GridFieldButtonRow('after'));
            $config->addComponent($export = new GridFieldExportButton('buttons-after-left'));
            $config->addComponent($print = new GridFieldPrintButton('buttons-after-left'));

            /**
             * Support for {@link https://github.com/colymba/GridFieldBulkEditingTools}
             */
            if (class_exists('GridFieldBulkManager')) {
                $config->addComponent(new GridFieldBulkManager());
            }

            $sort->setThrowExceptionOnBadDataType(false);
            $filter->setThrowExceptionOnBadDataType(false);
            $pagination->setThrowExceptionOnBadDataType(false);

            // attach every column to the print view form
            $columns['Created'] = 'Created';
            $filter->setColumns($columns);

            // print configuration

            $print->setPrintHasHeader(true);
            $print->setPrintColumns($columns);

            // export configuration
            $export->setCsvHasHeader(true);
            $export->setExportColumns($columns);

            $submissions->setConfig($config);
            $fields->addFieldToTab('Root.Submissions', $submissions);
            $fields->addFieldToTab('Root.FormOptions', new CheckboxField('DisableSaveSubmissions', _t('UserDefinedForm.SAVESUBMISSIONS', 'Disable Saving Submissions to Server')));

        });

         $fields = parent::getCMSFields();

         if ($this->EmailRecipients()->Count() == 0 && static::config()->recipients_warning_enabled) {
             $fields->addFieldToTab("Root.Main", new LiteralField("EmailRecipientsWarning",
                "<p class=\"message warning\">" . _t("UserDefinedForm.NORECIPIENTS",
                "Warning: You have not configured any recipients. Form submissions may be missed.")
                . "</p>"), "Title");
         }

         return $fields;
     }

    /**
     * Allow overriding the EmailRecipients on a {@link DataExtension}
     * so you can customise who receives an email.
     * Converts the RelationList to an ArrayList so that manipulation
     * of the original source data isn't possible.
     *
     * @return ArrayList
     */
    public function FilteredEmailRecipients($data = null, $form = null)
    {
        $recipients = new ArrayList($this->EmailRecipients()->toArray());

        // Filter by rules
        $recipients = $recipients->filterByCallback(function ($recipient) use ($data, $form) {
            return $recipient->canSend($data, $form);
        });

        $this->extend('updateFilteredEmailRecipients', $recipients, $data, $form);

        return $recipients;
    }

    /**
     * Custom options for the form. You can extend the built in options by
     * using {@link updateFormOptions()}
     *
     * @return FieldList
     */
    public function getFormOptions()
    {
        $submit = ($this->SubmitButtonText) ? $this->SubmitButtonText : _t('UserDefinedForm.SUBMITBUTTON', 'Submit');
        $clear = ($this->ClearButtonText) ? $this->ClearButtonText : _t('UserDefinedForm.CLEARBUTTON', 'Clear');

        $options = new FieldList(
            new TextField("SubmitButtonText", _t('UserDefinedForm.TEXTONSUBMIT', 'Text on submit button:'), $submit),
            new TextField("ClearButtonText", _t('UserDefinedForm.TEXTONCLEAR', 'Text on clear button:'), $clear),
            new CheckboxField("ShowClearButton", _t('UserDefinedForm.SHOWCLEARFORM', 'Show Clear Form Button'), $this->ShowClearButton),
            new CheckboxField("EnableLiveValidation", _t('UserDefinedForm.ENABLELIVEVALIDATION', 'Enable live validation')),
            new CheckboxField("HideFieldLabels", _t('UserDefinedForm.HIDEFIELDLABELS', 'Hide field labels')),
            new CheckboxField("DisplayErrorMessagesAtTop", _t('UserDefinedForm.DISPLAYERRORMESSAGESATTOP', 'Display error messages above the form?')),
            new CheckboxField('DisableCsrfSecurityToken', _t('UserDefinedForm.DISABLECSRFSECURITYTOKEN', 'Disable CSRF Token')),
            new CheckboxField('DisableAuthenicatedFinishAction', _t('UserDefinedForm.DISABLEAUTHENICATEDFINISHACTION', 'Disable Authentication on finish action'))
        );

        $this->extend('updateFormOptions', $options);

        return $options;
    }

    /**
     * Get the HTML id of the error container displayed above the form.
     *
     * @return string
     */
    public function getErrorContainerID()
    {
        return $this->config()->error_container_id;
    }

    public function requireDefaultRecords()
    {
        parent::requireDefaultRecords();

        if (!$this->config()->upgrade_on_build) {
            return;
        }

        // Perform migrations
        Injector::inst()
            ->create('UserFormsUpgradeService')
            ->setQuiet(true)
            ->run();

        DB::alteration_message('Migrated userforms', 'changed');
    }


    /**
     * Validate formfields
     */
    public function getCMSValidator()
    {
        return new UserFormValidator();
    }
}

/**
 * Controller for the {@link UserDefinedForm} page type.
 *
 * @package userforms
 */

class UserDefinedForm_Controller extends Page_Controller
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

        // load the jquery
        $lang = i18n::get_lang_from_locale(i18n::get_locale());
        Requirements::css(USERFORMS_DIR . '/css/UserForm.css');
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
        if ($this->HideFieldLabels) {
            Requirements::javascript(USERFORMS_DIR . '/thirdparty/Placeholders.js/Placeholders.min.js');
        }

        // Bind a confirmation message when navigating away from a partially completed form.
        $page = $this->data();
        if ($page::config()->enable_are_you_sure) {
            Requirements::javascript(USERFORMS_DIR . '/thirdparty/jquery.are-you-sure/jquery.are-you-sure.js');
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
        $form = UserForm::create($this);
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
        $watchLoad = array();

        if ($this->Fields()) {
            foreach ($this->Fields() as $field) {
                $holderSelector = $field->getSelectorHolder();

                // Is this Field Show by Default
                if (!$field->ShowOnLoad) {
                    $default .= "{$holderSelector}.hide().trigger('userform.field.hide');\n";
                }

                // Check for field dependencies / default
                foreach ($field->EffectiveDisplayRules() as $rule) {

                    // Get the field which is effected
                    $formFieldWatch = EditableFormField::get()->byId($rule->ConditionFieldID);

                    // Skip deleted fields
                    if (!$formFieldWatch) {
                        continue;
                    }

                    $fieldToWatch = $formFieldWatch->getSelectorField($rule);
                    $fieldToWatchOnLoad = $formFieldWatch->getSelectorField($rule, true);

                    // show or hide?
                    $view = ($rule->Display == 'Hide') ? 'hide' : 'show';
                    $opposite = ($view == "show") ? "hide" : "show";

                    // what action do we need to keep track of. Something nicer here maybe?
                    // @todo encapulsation
                    $action = "change";

                    if ($formFieldWatch instanceof EditableTextField) {
                        $action = "keyup";
                    }

                    // is this field a special option field
                    $checkboxField = false;
                    $radioField = false;

                    if (in_array($formFieldWatch->ClassName, array('EditableCheckboxGroupField', 'EditableCheckbox'))) {
                        $action = "click";
                        $checkboxField = true;
                    } elseif ($formFieldWatch->ClassName == "EditableRadioField") {
                        $radioField = true;
                    }

                    // and what should we evaluate
                    switch ($rule->ConditionOption) {
                        case 'IsNotBlank':
                            $expression = ($checkboxField || $radioField) ? '$(this).is(":checked")' :'$(this).val() != ""';

                            break;
                        case 'IsBlank':
                            $expression = ($checkboxField || $radioField) ? '!($(this).is(":checked"))' : '$(this).val() == ""';

                            break;
                        case 'HasValue':
                            if ($checkboxField) {
                                $expression = '$(this).prop("checked")';
                            } elseif ($radioField) {
                                // We cannot simply get the value of the radio group, we need to find the checked option first.
                                $expression = '$(this).parents(".field, .control-group").find("input:checked").val()=="'. $rule->FieldValue .'"';
                            } else {
                                $expression = '$(this).val() == "'. $rule->FieldValue .'"';
                            }

                            break;
                        case 'ValueLessThan':
                            $expression = '$(this).val() < parseFloat("'. $rule->FieldValue .'")';

                            break;
                        case 'ValueLessThanEqual':
                            $expression = '$(this).val() <= parseFloat("'. $rule->FieldValue .'")';

                            break;
                        case 'ValueGreaterThan':
                            $expression = '$(this).val() > parseFloat("'. $rule->FieldValue .'")';

                            break;
                        case 'ValueGreaterThanEqual':
                            $expression = '$(this).val() >= parseFloat("'. $rule->FieldValue .'")';

                            break;
                        default: // ==HasNotValue
                            if ($checkboxField) {
                                $expression = '!$(this).prop("checked")';
                            } elseif ($radioField) {
                                // We cannot simply get the value of the radio group, we need to find the checked option first.
                                $expression = '$(this).parents(".field, .control-group").find("input:checked").val()!="'. $rule->FieldValue .'"';
                            } else {
                                $expression = '$(this).val() != "'. $rule->FieldValue .'"';
                            }

                            break;
                    }

                    if (!isset($watch[$fieldToWatch])) {
                        $watch[$fieldToWatch] = array();
                    }

                    $watch[$fieldToWatch][] = array(
                        'expression' => $expression,
                        'holder_selector' => $holderSelector,
                        'view' => $view,
                        'opposite' => $opposite,
                        'action' => $action
                    );

                    $watchLoad[$fieldToWatchOnLoad] = true;
                }
            }
        }

        if ($watch) {
            foreach ($watch as $key => $values) {
                $logic = array();
                $actions = array();

                foreach ($values as $rule) {
                    // Assign action
                    $actions[$rule['action']] = $rule['action'];

                    // Assign behaviour
                    $expression = $rule['expression'];
                    $holder = $rule['holder_selector'];
                    $view = $rule['view']; // hide or show
                    $opposite = $rule['opposite'];
                    // Generated javascript for triggering visibility
                    $logic[] = <<<"EOS"
if({$expression}) {
	{$holder}
		.{$view}()
		.trigger('userform.field.{$view}');
} else {
	{$holder}
		.{$opposite}()
		.trigger('userform.field.{$opposite}');
}
EOS;
                }

                $logic = implode("\n", $logic);
                $rules .= $key.".each(function() {\n
	$(this).data('userformConditions', function() {\n
		$logic\n
	}); \n
});\n";
                foreach ($actions as $action) {
                    $rules .= $key.".$action(function() {
	$(this).data('userformConditions').call(this);\n
});\n";
                }
            }
        }

        if ($watchLoad) {
            foreach ($watchLoad as $key => $value) {
                $rules .= $key.".each(function() {
	$(this).data('userformConditions').call(this);\n
});\n";
            }
        }

        // Only add customScript if $default or $rules is defined
        if ($default  || $rules) {
            Requirements::customScript(<<<JS
				(function($) {
					$(document).ready(function() {
						$default

						$rules
					})
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
        $submittedForm = Object::create('SubmittedForm');
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
                if (in_array("EditableFileField", $field->getClassAncestry())) {
                    if (isset($_FILES[$field->Name])) {
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
    private function getMergeFieldsMap($fields = array())
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

        return $this->customise(array(
            'Content' => $this->customise(array(
                'Submission' => $submission,
                'Link' => $referrer
            ))->renderWith('ReceivedFormSubmission'),
            'Form' => '',
        ));
    }
}
