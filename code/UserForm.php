<?php

namespace SilverStripe\UserForms;

use Colymba\BulkManager\BulkManager;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\CompositeField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldAddNewButton;
use SilverStripe\Forms\GridField\GridFieldButtonRow;
use SilverStripe\Forms\GridField\GridFieldConfig;
use SilverStripe\Forms\GridField\GridFieldConfig_RecordEditor;
use SilverStripe\Forms\GridField\GridFieldDataColumns;
use SilverStripe\Forms\GridField\GridFieldDeleteAction;
use SilverStripe\Forms\GridField\GridFieldDetailForm;
use SilverStripe\Forms\GridField\GridFieldEditButton;
use SilverStripe\Forms\GridField\GridFieldExportButton;
use SilverStripe\Forms\GridField\GridFieldPageCount;
use SilverStripe\Forms\GridField\GridFieldPaginator;
use SilverStripe\Forms\GridField\GridFieldPrintButton;
use SilverStripe\Forms\GridField\GridFieldSortableHeader;
use SilverStripe\Forms\GridField\GridFieldToolbarHeader;
use SilverStripe\Forms\HTMLEditor\HTMLEditorField;
use SilverStripe\Forms\LabelField;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DB;
use SilverStripe\UserForms\Extension\UserFormFieldEditorExtension;
use SilverStripe\UserForms\Extension\UserFormValidator;
use SilverStripe\UserForms\Form\UserFormsGridFieldFilterHeader;
use SilverStripe\UserForms\Model\Recipient\EmailRecipient;
use SilverStripe\UserForms\Model\Recipient\UserFormRecipientItemRequest;
use SilverStripe\UserForms\Model\Submission\SubmittedForm;
use SilverStripe\UserForms\Model\EditableFormField;
use SilverStripe\View\Requirements;
use SilverStripe\Core\Config\Configurable;

/**
 * Defines the user defined functionality to be applied to any {@link DataObject}
 *
 */
trait UserForm
{
    use Configurable;

    /**
     * Built in extensions required by this page.
     *
     * @config
     * @var array
     */
    private static $extensions = [
        UserFormFieldEditorExtension::class
    ];

    /**
     * @var string Required Identifier
     */
    private static $required_identifier = null;

    /**
     * @var string
     */
    private static $email_template_directory = 'silverstripe/userforms:templates/email/';

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
     * @var array Fields on the user defined form page.
     */
    private static $db = [
        'SubmitButtonText' => 'Varchar',
        'ClearButtonText' => 'Varchar',
        'OnCompleteMessage' => 'HTMLText',
        'ShowClearButton' => 'Boolean',
        'DisableSaveSubmissions' => 'Boolean',
        'EnableLiveValidation' => 'Boolean',
        'DisplayErrorMessagesAtTop' => 'Boolean',
        'DisableAuthenicatedFinishAction' => 'Boolean',
        'DisableCsrfSecurityToken' => 'Boolean'
    ];

    /**
     * @var array Default values of variables when this page is created
     */
    private static $defaults = [
        'Content' => '$UserDefinedForm',
        'DisableSaveSubmissions' => 0,
        'OnCompleteMessage' => '<p>Thanks, we\'ve received your submission.</p>'
    ];

    /**
     * @var array
     */
    private static $has_many = [
        'Submissions' => SubmittedForm::class,
        'EmailRecipients' => EmailRecipient::class
    ];

    private static $cascade_deletes = [
        'EmailRecipients',
    ];

    private static $cascade_duplicates = false;

    /**
     * @var array
     * @config
     */
    private static $casting = [
        'ErrorContainerID' => 'Text'
    ];

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

    private static $non_live_permissions = ['SITETREE_VIEW_ALL'];

    /**
     * Temporary storage of field ids when the form is duplicated.
     * Example layout: array('EditableCheckbox3' => 'EditableCheckbox14')
     * @var array
     */
    protected $fieldsFromTo = [];

    /**
     * @return FieldList
     */
    public function getCMSFields()
    {
        Requirements::css('silverstripe/userforms:client/dist/styles/userforms-cms.css');

        $this->beforeUpdateCMSFields(function ($fields) {

            // remove
            $fields->removeByName('OnCompleteMessageLabel');
            $fields->removeByName('OnCompleteMessage');
            $fields->removeByName('Fields');
            $fields->removeByName('EmailRecipients');

            // define tabs
            $fields->findOrMakeTab('Root.FormOptions', _t(__CLASS__.'.CONFIGURATION', 'Configuration'));
            $fields->findOrMakeTab('Root.Recipients', _t(__CLASS__.'.RECIPIENTS', 'Recipients'));
            $fields->findOrMakeTab('Root.Submissions', _t(__CLASS__.'.SUBMISSIONS', 'Submissions'));


            // text to show on complete
            $onCompleteFieldSet = CompositeField::create(
                $label = LabelField::create(
                    'OnCompleteMessageLabel',
                    _t(__CLASS__.'.ONCOMPLETELABEL', 'Show on completion')
                ),
                $editor = HTMLEditorField::create(
                    'OnCompleteMessage',
                    '',
                    $this->OnCompleteMessage
                )
            );

            $onCompleteFieldSet->addExtraClass('field');

            $editor->setRows(3);
            $label->addExtraClass('left');

            // Define config for email recipients
            $emailRecipientsConfig = GridFieldConfig_RecordEditor::create(10);
            $emailRecipientsConfig->getComponentByType(GridFieldAddNewButton::class)
                ->setButtonName(
                    _t(__CLASS__.'.ADDEMAILRECIPIENT', 'Add Email Recipient')
                );

            // who do we email on submission
            $emailRecipients = GridField::create(
                'EmailRecipients',
                '',
                $this->EmailRecipients(),
                $emailRecipientsConfig
            );
            $emailRecipients
                ->getConfig()
                ->getComponentByType(GridFieldDetailForm::class)
                ->setItemRequestClass(UserFormRecipientItemRequest::class);

            $fields->addFieldsToTab('Root.FormOptions', $onCompleteFieldSet);
            $fields->addFieldToTab('Root.Recipients', $emailRecipients);
            $fields->addFieldsToTab('Root.FormOptions', $this->getFormOptions());


            // view the submissions
            // make sure a numeric not a empty string is checked against this int column for SQL server
            $parentID = (!empty($this->ID)) ? (int) $this->ID : 0;

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

            $config = GridFieldConfig::create();
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

            foreach (EditableFormField::get()->filter(array('ParentID' => $parentID)) as $eff) {
                if ($eff->ShowInSummary) {
                    $summaryarray[$eff->Name] = $eff->Title ?: $eff->Name;
                }
            }

            $config->getComponentByType(GridFieldDataColumns::class)->setDisplayFields($summaryarray);

            /**
             * Support for {@link https://github.com/colymba/GridFieldBulkEditingTools}
             */
            if (class_exists(BulkManager::class)) {
                $config->addComponent(new BulkManager);
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
                '',
                $this->Submissions()->sort('Created', 'DESC'),
                $config
            );
            $fields->addFieldToTab('Root.Submissions', $submissions);
            $fields->addFieldToTab(
                'Root.FormOptions',
                CheckboxField::create(
                    'DisableSaveSubmissions',
                    _t(__CLASS__.'.SAVESUBMISSIONS', 'Disable Saving Submissions to Server')
                )
            );
        });

        $fields = parent::getCMSFields();

        if ($this->EmailRecipients()->Count() == 0 && static::config()->recipients_warning_enabled) {
            $fields->addFieldToTab('Root.Main', LiteralField::create(
                'EmailRecipientsWarning',
                '<p class="message warning">' . _t(
                    __CLASS__.'.NORECIPIENTS',
                    'Warning: You have not configured any recipients. Form submissions may be missed.'
                )
                . '</p>'
            ), 'Title');
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
        $recipients = ArrayList::create($this->EmailRecipients()->toArray());

        // Filter by rules
        $recipients = $recipients->filterByCallback(function ($recipient) use ($data, $form) {
            /** @var EmailRecipient $recipient */
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
        $submit = ($this->SubmitButtonText) ? $this->SubmitButtonText : _t(__CLASS__.'.SUBMITBUTTON', 'Submit');
        $clear = ($this->ClearButtonText) ? $this->ClearButtonText : _t(__CLASS__.'.CLEARBUTTON', 'Clear');

        $options = FieldList::create(
            TextField::create('SubmitButtonText', _t(__CLASS__.'.TEXTONSUBMIT', 'Text on submit button:'), $submit),
            TextField::create('ClearButtonText', _t(__CLASS__.'.TEXTONCLEAR', 'Text on clear button:'), $clear),
            CheckboxField::create('ShowClearButton', _t(__CLASS__.'.SHOWCLEARFORM', 'Show Clear Form Button'), $this->ShowClearButton),
            CheckboxField::create('EnableLiveValidation', _t(__CLASS__.'.ENABLELIVEVALIDATION', 'Enable live validation')),
            CheckboxField::create('DisplayErrorMessagesAtTop', _t(__CLASS__.'.DISPLAYERRORMESSAGESATTOP', 'Display error messages above the form?')),
            CheckboxField::create('DisableCsrfSecurityToken', _t(__CLASS__.'.DISABLECSRFSECURITYTOKEN', 'Disable CSRF Token')),
            CheckboxField::create('DisableAuthenicatedFinishAction', _t(__CLASS__.'.DISABLEAUTHENICATEDFINISHACTION', 'Disable Authentication on finish action'))
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
        return $this->config()->get('error_container_id');
    }

    /**
     * Validate formfields
     */
    public function getCMSValidator()
    {
        return UserFormValidator::create();
    }
}
