<?php

namespace SilverStripe\UserForms\Model;

use Page;



use HtmlEditorField;

















use GridFieldBulkManager;







use SilverStripe\UserForms\Extension\UserFormFieldEditorExtension;
use SilverStripe\UserForms\Model\Submission\SubmittedForm;
use SilverStripe\UserForms\Model\Recipient\UserDefinedForm_EmailRecipient;
use SilverStripe\View\Requirements;
use SilverStripe\Forms\LabelField;
use SilverStripe\Forms\CompositeField;
use SilverStripe\Forms\GridField\GridFieldConfig_RecordEditor;
use SilverStripe\Forms\GridField\GridFieldAddNewButton;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldDetailForm;
use SilverStripe\UserForms\Model\Recipient\UserFormRecipientItemRequest;
use SilverStripe\ORM\DB;
use SilverStripe\Forms\GridField\GridFieldConfig;
use SilverStripe\Forms\GridField\GridFieldToolbarHeader;
use SilverStripe\Forms\GridField\GridFieldSortableHeader;
use SilverStripe\UserForms\Form\UserFormsGridFieldFilterHeader;
use SilverStripe\Forms\GridField\GridFieldDataColumns;
use SilverStripe\Forms\GridField\GridFieldEditButton;
use SilverStripe\Forms\GridField\GridFieldDeleteAction;
use SilverStripe\Forms\GridField\GridFieldPageCount;
use SilverStripe\Forms\GridField\GridFieldPaginator;
use SilverStripe\Forms\GridField\GridFieldButtonRow;
use SilverStripe\Forms\GridField\GridFieldExportButton;
use SilverStripe\Forms\GridField\GridFieldPrintButton;
use SilverStripe\UserForms\Model\EditableFormField\EditableFormField;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\LiteralField;
use SilverStripe\ORM\ArrayList;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\UserForms\Task\UserFormsUpgradeService;
use SilverStripe\UserForms\Extension\UserFormValidator;



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
     * Set this to true to disable automatic inclusion of CSS files
     * @config
     * @var bool
     */
    private static $block_default_userforms_css = false;

    /**
     * Set this to true to disable automatic inclusion of JavaScript files
     * @config
     * @var bool
     */
    private static $block_default_userforms_js = false;

    /**
     * Built in extensions required by this page
     * @config
     * @var array
     */
    private static $extensions = array(
        UserFormFieldEditorExtension::class
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
        "Submissions" => SubmittedForm::class,
        "EmailRecipients" => UserDefinedForm_EmailRecipient::class
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
            $emailRecipientsConfig->getComponentByType(GridFieldAddNewButton::class)
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
                ->getComponentByType(GridFieldDetailForm::class)
                ->setItemRequestClass(UserFormRecipientItemRequest::class);

            $fields->addFieldsToTab('Root.FormOptions', $onCompleteFieldSet);
            $fields->addFieldToTab('Root.Recipients', $emailRecipients);
            $fields->addFieldsToTab('Root.FormOptions', $self->getFormOptions());


            // view the submissions
            // make sure a numeric not a empty string is checked against this int column for SQL server
            $parentID = (!empty($self->ID)) ? (int) $self->ID : 0;

            // get a list of all field names and values used for print and export CSV views of the GridField below.
            $columnSQL = <<<SQL
SELECT "SubmittedFormField"."Name" as "Name", COALESCE("EditableFormField"."Title", "SubmittedFormField"."Title") as "Title", COALESCE("EditableFormField"."Sort", 999) AS "Sort"
FROM "SubmittedFormField"
LEFT JOIN "SubmittedForm" ON "SubmittedForm"."ID" = "SubmittedFormField"."ParentID"
LEFT JOIN "EditableFormField" ON "EditableFormField"."Name" = "SubmittedFormField"."Name" AND "EditableFormField"."ParentID" = '$parentID'
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

            // show user form items in the summary tab
            $summaryarray = array(
                'ID' => 'ID',
                'Created' => 'Created',
                'LastEdited' => 'Last Edited'
            );
            foreach(EditableFormField::get()->filter(array("ParentID" => $parentID)) as $eff) {
                if($eff->ShowInSummary) {
                    $summaryarray[$eff->Name] = $eff->Title ?: $eff->Name;
                }
            }

            $config->getComponentByType(GridFieldDataColumns::class)->setDisplayFields($summaryarray);

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
            $columns['SubmittedBy.Email'] = 'Submitter';
            $filter->setColumns($columns);

            // print configuration

            $print->setPrintHasHeader(true);
            $print->setPrintColumns($columns);

            // export configuration
            $export->setCsvHasHeader(true);
            $export->setExportColumns($columns);

            $submissions = GridField::create(
                'Submissions',
                _t('UserDefinedForm.SUBMISSIONS', 'Submissions'),
                $self->Submissions()->sort('Created', 'DESC'),
                $config
            );
            $fields->addFieldToTab('Root.Submissions', $submissions);
            $fields->addFieldToTab(
                'Root.FormOptions',
                CheckboxField::create(
                    'DisableSaveSubmissions',
                    _t('UserDefinedForm.SAVESUBMISSIONS', 'Disable Saving Submissions to Server')
                )
            );
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
            /** @var UserDefinedForm_EmailRecipient $recipient */
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
            ->create(UserFormsUpgradeService::class)
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
