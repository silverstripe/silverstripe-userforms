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
use SilverStripe\Dev\Deprecation;

/**
 * Defines the user defined functionality to be applied to any {@link DataObject}
 *
 * @mixin UserFormFieldEditorExtension
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
    * @var array
    */
    public function populateDefaults()
    {
        parent::populateDefaults();
        $this->OnCompleteMessage = '<p>' . _t('SilverStripe\\UserForms\\Model\\UserDefinedForm.ONCOMPLETEMESSAGE', 'Thanks, we\'ve received your submission.') . '</p>';
    }

    /**
     * @return FieldList
     */
    public function getCMSFields()
    {
        Requirements::css('silverstripe/userforms:client/dist/styles/userforms-cms.css');

        $this->beforeUpdateCMSFields(function ($fields) {

            // remove
            $fields->removeByName([
                'OnCompleteMessageLabel',
                'OnCompleteMessage',
                'Fields',
                'EmailRecipients'
            ]);

            // define tabs
            $fields->findOrMakeTab('Root.FormOptions')->setTitle(_t('SilverStripe\\UserForms\\Model\\UserDefinedForm.CONFIGURATION', 'Configuration'));
            $fields->findOrMakeTab('Root.Recipients')->setTitle(_t('SilverStripe\\UserForms\\Model\\UserDefinedForm.RECIPIENTS', 'Recipients'));


            // text to show on complete
            $onCompleteFieldSet = CompositeField::create(
                $label = LabelField::create(
                    'OnCompleteMessageLabel',
                    _t('SilverStripe\\UserForms\\Model\\UserDefinedForm.ONCOMPLETELABEL', 'Show on completion')
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
                    _t('SilverStripe\\UserForms\\Model\\UserDefinedForm.ADDEMAILRECIPIENT', 'Add Email Recipient')
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

            $submissions = $this->getSubmissionsGridField();
            $fields->findOrMakeTab('Root.Submissions')->setTitle(_t('SilverStripe\\UserForms\\Model\\UserDefinedForm.SUBMISSIONS', 'Submissions'));
            $fields->addFieldToTab('Root.Submissions', $submissions);
            $fields->addFieldToTab(
                'Root.FormOptions',
                CheckboxField::create(
                    'DisableSaveSubmissions',
                    _t('SilverStripe\\UserForms\\Model\\UserDefinedForm.SAVESUBMISSIONS', 'Disable Saving Submissions to Server')
                )
            );
        });

        $fields = parent::getCMSFields();

        if ($this->EmailRecipients()->Count() == 0 && static::config()->recipients_warning_enabled) {
            $fields->addFieldToTab('Root.Main', LiteralField::create(
                'EmailRecipientsWarning',
                '<p class="alert alert-warning">' . _t(
                    'SilverStripe\\UserForms\\Model\\UserDefinedForm.NORECIPIENTS',
                    'Warning: You have not configured any recipients. Form submissions may be missed.'
                )
                . '</p>'
            ), 'Title');
        }

        return $fields;
    }

    public function getSubmissionsGridField()
    {
        // view the submissions
        // make sure a numeric not a empty string is checked against this int column for SQL server
        $parentID = (!empty($this->ID)) ? (int) $this->ID : 0;

        // get a list of all field names and values used for print and export CSV views of the GridField below.
        $columnSQL = <<<SQL
SELECT DISTINCT
  "SubmittedFormField"."Name" as "Name",
  REPLACE(COALESCE("EditableFormField"."Title", "SubmittedFormField"."Title"), '.', ' ') as "Title",
  COALESCE("EditableFormField"."Sort", 999) AS "Sort"
FROM "SubmittedFormField"
  LEFT JOIN "SubmittedForm" ON "SubmittedForm"."ID" = "SubmittedFormField"."ParentID"
  LEFT JOIN "EditableFormField" ON "EditableFormField"."Name" = "SubmittedFormField"."Name"
WHERE "SubmittedForm"."ParentID" = '$parentID'
  AND "EditableFormField"."ParentID" = '$parentID'
ORDER BY "Sort", "Title"
SQL;

        $columns = DB::query($columnSQL)->map();

        $config = GridFieldConfig::create();
        $config->addComponent(new GridFieldToolbarHeader());
        $config->addComponent($sort = new GridFieldSortableHeader());
        $config->addComponent($filter = new UserFormsGridFieldFilterHeader());
        $config->addComponent(new GridFieldDataColumns());
        $config->addComponent(new GridFieldEditButton());
        $config->addComponent(new GridFieldDeleteAction());
        $config->addComponent(new GridFieldPageCount('toolbar-header-right'));
        $config->addComponent($pagination = new GridFieldPaginator(25));
        $config->addComponent(new GridFieldDetailForm(null, true, false));
        $config->addComponent(new GridFieldButtonRow('after'));
        $config->addComponent($export = new GridFieldExportButton('buttons-after-left'));
        $config->addComponent($print = new GridFieldPrintButton('buttons-after-left'));

        // show user form items in the summary tab
        $summaryarray = SubmittedForm::singleton()->summaryFields();

        foreach (EditableFormField::get()->filter(['ParentID' => $parentID, 'ShowInSummary' => 1]) as $eff) {
            $summaryarray[$eff->Name] = $eff->Title ?: $eff->Name;
        }

        $config->getComponentByType(GridFieldDataColumns::class)->setDisplayFields($summaryarray);

        /**
         * Support for {@link https://github.com/colymba/GridFieldBulkEditingTools}
         */
        if (class_exists(BulkManager::class)) {
            $config->addComponent(new BulkManager);
        }

        Deprecation::withNoReplacement(function () use ($sort, $filter, $pagination) {
            $sort->setThrowExceptionOnBadDataType(false);
            $filter->setThrowExceptionOnBadDataType(false);
            $pagination->setThrowExceptionOnBadDataType(false);
        });

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
        return $submissions;
    }

    /**
     * Allow overriding the EmailRecipients on a {@link DataExtension}
     * so you can customise who receives an email.
     * Converts the RelationList to an ArrayList so that manipulation
     * of the original source data isn't possible.
     *
     * @return ArrayList<EmailRecipient>
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
        $submit = ($this->SubmitButtonText) ? $this->SubmitButtonText : _t('SilverStripe\\UserForms\\Model\\UserDefinedForm.SUBMITBUTTON', 'Submit');
        $clear = ($this->ClearButtonText) ? $this->ClearButtonText : _t('SilverStripe\\UserForms\\Model\\UserDefinedForm.CLEARBUTTON', 'Clear');

        $options = FieldList::create(
            TextField::create('SubmitButtonText', _t('SilverStripe\\UserForms\\Model\\UserDefinedForm.TEXTONSUBMIT', 'Text on submit button:'), $submit),
            TextField::create('ClearButtonText', _t('SilverStripe\\UserForms\\Model\\UserDefinedForm.TEXTONCLEAR', 'Text on clear button:'), $clear),
            CheckboxField::create('ShowClearButton', _t('SilverStripe\\UserForms\\Model\\UserDefinedForm.SHOWCLEARFORM', 'Show Clear Form Button'), $this->ShowClearButton),
            CheckboxField::create('EnableLiveValidation', _t('SilverStripe\\UserForms\\Model\\UserDefinedForm.ENABLELIVEVALIDATION', 'Enable live validation')),
            CheckboxField::create('DisplayErrorMessagesAtTop', _t('SilverStripe\\UserForms\\Model\\UserDefinedForm.DISPLAYERRORMESSAGESATTOP', 'Display error messages above the form?')),
            CheckboxField::create('DisableCsrfSecurityToken', _t('SilverStripe\\UserForms\\Model\\UserDefinedForm.DISABLECSRFSECURITYTOKEN', 'Disable CSRF Token')),
            CheckboxField::create('DisableAuthenicatedFinishAction', _t('SilverStripe\\UserForms\\Model\\UserDefinedForm.DISABLEAUTHENICATEDFINISHACTION', 'Disable Authentication on finish action'))
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
