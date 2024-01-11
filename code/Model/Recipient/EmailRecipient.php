<?php

namespace SilverStripe\UserForms\Model\Recipient;

use SilverStripe\Assets\FileFinder;
use SilverStripe\CMS\Controllers\CMSMain;
use SilverStripe\CMS\Controllers\CMSPageEditController;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Control\Controller;
use SilverStripe\Control\Email\Email;
use SilverStripe\Core\Manifest\ModuleResource;
use SilverStripe\Core\Manifest\ModuleResourceLoader;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldGroup;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldButtonRow;
use SilverStripe\Forms\GridField\GridFieldConfig;
use SilverStripe\Forms\GridField\GridFieldDeleteAction;
use SilverStripe\Forms\GridField\GridFieldToolbarHeader;
use SilverStripe\Forms\HTMLEditor\HTMLEditorField;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\TabSet;
use SilverStripe\Forms\TextareaField;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataList;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\DB;
use SilverStripe\ORM\FieldType\DBField;
use SilverStripe\ORM\HasManyList;
use SilverStripe\ORM\ValidationResult;
use SilverStripe\Security\Member;
use SilverStripe\UserForms\Model\EditableFormField;
use SilverStripe\UserForms\Model\EditableFormField\EditableEmailField;
use SilverStripe\UserForms\Model\EditableFormField\EditableMultipleOptionField;
use SilverStripe\UserForms\Model\EditableFormField\EditableTextField;
use SilverStripe\UserForms\Model\UserDefinedForm;
use SilverStripe\UserForms\UserForm;
use SilverStripe\View\Requirements;
use Symbiote\GridFieldExtensions\GridFieldAddNewInlineButton;
use Symbiote\GridFieldExtensions\GridFieldEditableColumns;

/**
 * A Form can have multiply members / emails to email the submission
 * to and custom subjects
 *
 * @package userforms
 * @property string $CustomRulesCondition
 * @property string $EmailAddress
 * @property string $EmailBody
 * @property string $EmailBodyHtml
 * @property string $EmailFrom
 * @property string $EmailReplyTo
 * @property string $EmailSubject
 * @property string $EmailTemplate
 * @property int $FormID
 * @property int $HideFromData
 * @property int $SendPlain
 * @property int $SendEmailFromFieldID
 * @property int $SendEmailSubjectFieldID
 * @property int $SendEmailToFieldID
 * @method HasManyList<EmailRecipientCondition> CustomRules()
 * @method DataObject Form()
 * @method EditableFormField SendEmailFromField()
 * @method EditableFormField SendEmailSubjectField()
 * @method EditableFormField SendEmailToField()
 */
class EmailRecipient extends DataObject
{
    private static $db = [
        'EmailAddress' => 'Varchar(200)',
        'EmailSubject' => 'Varchar(200)',
        'EmailFrom' => 'Varchar(200)',
        'EmailReplyTo' => 'Varchar(200)',
        'EmailBody' => 'Text',
        'EmailBodyHtml' => 'HTMLText',
        'EmailTemplate' => 'Varchar',
        'SendPlain' => 'Boolean',
        'HideFormData' => 'Boolean',
        'HideInvisibleFields' => 'Boolean',
        'CustomRulesCondition' => 'Enum("And,Or")'
    ];

    private static $has_one = [
        'Form' => DataObject::class,
        'SendEmailFromField' => EditableFormField::class,
        'SendEmailToField' => EditableFormField::class,
        'SendEmailSubjectField' => EditableFormField::class
    ];

    private static $has_many = [
        'CustomRules' => EmailRecipientCondition::class,
    ];

    private static $owns = [
        'CustomRules',
    ];

    private static $cascade_deletes = [
        'CustomRules',
    ];

    private static $summary_fields = [
        'EmailAddress',
        'EmailSubject',
        'EmailFrom'
    ];

    private static $table_name = 'UserDefinedForm_EmailRecipient';

    /**
     * Disable versioned GridField to ensure that it doesn't interfere with {@link UserFormRecipientItemRequest}
     *
     * {@inheritDoc}
     */
    private static $versioned_gridfield_extensions = false;

    /**
     * Setting this to true will allow you to select "risky" fields as
     * email recipient, such as free-text entry fields.
     *
     * It's advisable to leave this off.
     *
     * @config
     * @var bool
     */
    private static $allow_unbound_recipient_fields = false;

    public function requireDefaultRecords()
    {
        parent::requireDefaultRecords();

        // make sure to migrate the class across (prior to v5.x)
        DB::query("UPDATE \"UserDefinedForm_EmailRecipient\" SET \"FormClass\" = 'Page' WHERE \"FormClass\" IS NULL");
    }

    public function onBeforeWrite()
    {
        parent::onBeforeWrite();

        // email addresses have trim() applied to them during validation for a slightly nicer UX
        // apply trim() here too before saving to the database
        $this->EmailAddress = trim($this->EmailAddress ?? '');
        $this->EmailFrom = trim($this->EmailFrom ?? '');
        $this->EmailReplyTo = trim($this->EmailReplyTo ?? '');
    }

    public function summaryFields()
    {
        $fields = parent::summaryFields();
        if (isset($fields['EmailAddress'])) {
            $fields['EmailAddress'] = _t('SilverStripe\\UserForms\\Model\\UserDefinedForm.EMAILADDRESS', 'Email');
        }
        if (isset($fields['EmailSubject'])) {
            $fields['EmailSubject'] = _t('SilverStripe\\UserForms\\Model\\UserDefinedForm.EMAILSUBJECT', 'Subject');
        }
        if (isset($fields['EmailFrom'])) {
            $fields['EmailFrom'] = _t('SilverStripe\\UserForms\\Model\\UserDefinedForm.EMAILFROM', 'From');
        }
        return $fields;
    }

    /**
     * Get instance of UserForm when editing in getCMSFields
     *
     * @return UserDefinedForm|UserForm|null
     */
    protected function getFormParent()
    {
        // If polymorphic relationship is actually defined, use it
        if ($this->FormID && $this->FormClass) {
            $formClass = $this->FormClass;
            return $formClass::get()->byID($this->FormID);
        }

        // Revert to checking for a form from the session
        // LeftAndMain::sessionNamespace is protected.
        $sessionNamespace = $this->config()->get('session_namespace') ?: CMSMain::class;

        $formID = Controller::curr()->getRequest()->getSession()->get($sessionNamespace . '.currentPage');
        if ($formID) {
            return UserDefinedForm::get()->byID($formID);
        }
    }

    public function getTitle()
    {
        if ($this->EmailAddress) {
            return $this->EmailAddress;
        }
        if ($this->EmailSubject) {
            return $this->EmailSubject;
        }
        return parent::getTitle();
    }

    /**
     * Generate a gridfield config for editing filter rules
     *
     * @return GridFieldConfig
     */
    protected function getRulesConfig()
    {
        if (!$this->getFormParent()) {
            return null;
        }
        $formFields = $this->getFormParent()->Fields();

        $config = GridFieldConfig::create()
            ->addComponents(
                new GridFieldButtonRow('before'),
                new GridFieldToolbarHeader(),
                new GridFieldAddNewInlineButton(),
                new GridFieldDeleteAction(),
                $columns = new GridFieldEditableColumns()
            );

        $columns->setDisplayFields(array(
            'ConditionFieldID' => function ($record, $column, $grid) use ($formFields) {
                return DropdownField::create($column, false, $formFields->map('ID', 'Title'));
            },
            'ConditionOption' => function ($record, $column, $grid) {
                $options = EmailRecipientCondition::config()->condition_options;
                return DropdownField::create($column, false, $options);
            },
            'ConditionValue' => function ($record, $column, $grid) {
                return TextField::create($column);
            }
        ));

        return $config;
    }

    /**
     * @return FieldList
     */
    public function getCMSFields()
    {
        Requirements::javascript('silverstripe/userforms:client/dist/js/userforms-cms.js');

        // Build fieldlist
        $fields = FieldList::create(Tabset::create('Root')->addExtraClass('EmailRecipientForm'));

        if (!$this->getFormParent()) {
            $fields->addFieldToTab('Root.EmailDetails', $this->getUnsavedFormLiteralField());
        }

        // Configuration fields
        $fields->addFieldsToTab('Root.EmailDetails', [
            $this->getSubjectCMSFields(),
            $this->getEmailToCMSFields(),
            $this->getEmailFromCMSFields(),
            $this->getEmailReplyToCMSFields(),
        ]);

        $fields->fieldByName('Root.EmailDetails')->setTitle(_t(__CLASS__ . '.EMAILDETAILSTAB', 'Email Details'));

        // Only show the preview link if the recipient has been saved.
        if (!empty($this->EmailTemplate)) {
            $request = Controller::curr()->getRequest();

            $pageEditController = singleton(CMSPageEditController::class);
            $pageEditController->getRequest()->setSession($request->getSession());

            $currentUrl = $request->getURL();
            // If used in a regular page context, will have "/edit" on the end, if used in a trait context
            // it won't. Strip that off in case. It may also have "ItemEditForm" on the end instead if this is
            // an AJAX request, e.g. saving a GridFieldDetailForm
            $remove = ['/edit', '/ItemEditForm'];
            foreach ($remove as $badSuffix) {
                $badSuffixLength = strlen($badSuffix ?? '');
                if (substr($currentUrl ?? '', -$badSuffixLength) === $badSuffix) {
                    $currentUrl = substr($currentUrl ?? '', 0, -$badSuffixLength);
                }
            }
            $previewUrl = Controller::join_links($currentUrl, 'preview');

            $preview = sprintf(
                '<p><a href="%s" target="_blank" class="btn btn-outline-secondary">%s</a></p><em>%s</em>',
                $previewUrl,
                _t('SilverStripe\\UserForms\\Model\\UserDefinedForm.PREVIEW_EMAIL', 'Preview email'),
                _t(
                    'SilverStripe\\UserForms\\Model\\UserDefinedForm.PREVIEW_EMAIL_DESCRIPTION',
                    'Note: Unsaved changes will not appear in the preview.'
                )
            );
        } else {
            $preview = sprintf(
                '<p class="alert alert-warning">%s</p>',
                _t(
                    'SilverStripe\\UserForms\\Model\\UserDefinedForm.PREVIEW_EMAIL_UNAVAILABLE',
                    'You can preview this email once you have saved the Recipient.'
                )
            );
        }

        // Email templates
        $fields->addFieldsToTab('Root.EmailContent', [
            CheckboxField::create(
                'HideFormData',
                _t('SilverStripe\\UserForms\\Model\\UserDefinedForm.HIDEFORMDATA', 'Hide form data from email?')
            ),
            CheckboxField::create(
                'HideInvisibleFields',
                _t('SilverStripe\\UserForms\\Model\\UserDefinedForm.HIDEINVISIBLEFIELDS', 'Hide invisible fields from email?')
            ),
            CheckboxField::create(
                'SendPlain',
                _t(
                    'SilverStripe\\UserForms\\Model\\UserDefinedForm.SENDPLAIN',
                    'Send email as plain text? (HTML will be stripped)'
                )
            ),
            HTMLEditorField::create(
                'EmailBodyHtml',
                _t('SilverStripe\\UserForms\\Model\\UserDefinedForm.EMAILBODYHTML', 'Body')
            )
                ->addExtraClass('toggle-html-only'),
            TextareaField::create(
                'EmailBody',
                _t('SilverStripe\\UserForms\\Model\\UserDefinedForm.EMAILBODY', 'Body')
            )
                ->addExtraClass('toggle-plain-only'),
            LiteralField::create('EmailPreview', $preview)
        ]);

        $templates = $this->getEmailTemplateDropdownValues();

        if ($templates) {
            $fields->insertBefore(
                'EmailBodyHtml',
                DropdownField::create(
                    'EmailTemplate',
                    _t('SilverStripe\\UserForms\\Model\\UserDefinedForm.EMAILTEMPLATE', 'Email template'),
                    $templates
                )->addExtraClass('toggle-html-only')
            );
        }

        $fields->fieldByName('Root.EmailContent')->setTitle(_t(__CLASS__ . '.EMAILCONTENTTAB', 'Email Content'));

        // Custom rules for sending this field
        $grid = GridField::create(
            'CustomRules',
            _t('SilverStripe\\UserForms\\Model\\EditableFormField.CUSTOMRULES', 'Custom Rules'),
            $this->CustomRules(),
            $this->getRulesConfig()
        );
        $grid->setDescription(_t(
            'SilverStripe\\UserForms\\Model\\UserDefinedForm.RulesDescription',
            'Emails will only be sent to the recipient if the custom rules are met. If no rules are defined, '
            . 'this recipient will receive notifications for every submission.'
        ));

        $fields->addFieldsToTab('Root.CustomRules', [
            DropdownField::create(
                'CustomRulesCondition',
                _t('SilverStripe\\UserForms\\Model\\UserDefinedForm.SENDIF', 'Send condition'),
                [
                    'Or' => _t(
                        'SilverStripe\\UserForms\\Model\\UserDefinedForm.SENDIFOR',
                        'Any conditions are true'
                    ),
                    'And' => _t(
                        'SilverStripe\\UserForms\\Model\\UserDefinedForm.SENDIFAND',
                        'All conditions are true'
                    )
                ]
            ),
            $grid
        ]);

        $fields->fieldByName('Root.CustomRules')->setTitle(_t(__CLASS__ . '.CUSTOMRULESTAB', 'Custom Rules'));

        $this->extend('updateCMSFields', $fields);
        return $fields;
    }

    /**
     * Return whether a user can create an object of this type
     *
     * @param Member $member
     * @param array $context Virtual parameter to allow context to be passed in to check
     * @return bool
     */
    public function canCreate($member = null, $context = [])
    {
        // Check parent page
        $parent = $this->getCanCreateContext(func_get_args());
        if ($parent) {
            return $parent->canEdit($member);
        }

        // Fall back to secure admin permissions
        return parent::canCreate($member);
    }

    /**
     * Helper method to check the parent for this object
     *
     * @param array $args List of arguments passed to canCreate
     * @return SiteTree Parent page instance
     */
    protected function getCanCreateContext($args)
    {
        // Inspect second parameter to canCreate for a 'Parent' context
        if (isset($args[1][Form::class])) {
            return $args[1][Form::class];
        }
        // Hack in currently edited page if context is missing
        if (Controller::has_curr() && Controller::curr() instanceof CMSMain) {
            return Controller::curr()->currentPage();
        }

        // No page being edited
        return null;
    }

    public function canView($member = null)
    {
        if ($form = $this->getFormParent()) {
            return $form->canView($member);
        }
        return parent::canView($member);
    }

    public function canEdit($member = null)
    {
        if ($form = $this->getFormParent()) {
            return $form->canEdit($member);
        }

        return parent::canEdit($member);
    }

    /**
     * @param Member
     *
     * @return boolean
     */
    public function canDelete($member = null)
    {
        return $this->canEdit($member);
    }

    /**
     * Determine if this recipient may receive notifications for this submission
     *
     * @param array $data
     * @param Form $form
     * @return bool
     */
    public function canSend($data, $form)
    {
        // Skip if no rules configured
        $customRules = $this->CustomRules();
        if (!$customRules->count()) {
            return true;
        }

        // Check all rules
        $isAnd = $this->CustomRulesCondition === 'And';
        foreach ($customRules as $customRule) {
            $matches = $customRule->matches($data);
            if ($isAnd && !$matches) {
                return false;
            }
            if (!$isAnd && $matches) {
                return true;
            }
        }

        // Once all rules are checked
        return $isAnd;
    }

    /**
     * Make sure the email template saved against the recipient exists on the file system.
     *
     * @param string
     *
     * @return boolean
     */
    public function emailTemplateExists($template = '')
    {
        $t = ($template ? $template : $this->EmailTemplate);

        return array_key_exists($t, (array) $this->getEmailTemplateDropdownValues());
    }

    /**
     * Get the email body for the current email format
     *
     * @return string
     */
    public function getEmailBodyContent()
    {
        if ($this->SendPlain) {
            return DBField::create_field('HTMLText', $this->EmailBody)->Plain();
        }
        return DBField::create_field('HTMLText', $this->EmailBodyHtml);
    }

    /**
     * Gets a list of email templates suitable for populating the email template dropdown.
     *
     * @return array
     */
    public function getEmailTemplateDropdownValues()
    {
        $templates = [];

        $finder = new FileFinder();
        $finder->setOption('name_regex', '/^.*\.ss$/');

        $parent = $this->getFormParent();

        if (!$parent) {
            return [];
        }

        $emailTemplateDirectory = $parent->config()->get('email_template_directory');
        $templateDirectory = ModuleResourceLoader::resourcePath($emailTemplateDirectory);

        if (!$templateDirectory) {
            return [];
        }

        $found = $finder->find(BASE_PATH . DIRECTORY_SEPARATOR . $templateDirectory);

        foreach ($found as $key => $value) {
            $template = pathinfo($value ?? '');
            $absoluteFilename = $template['dirname'] . DIRECTORY_SEPARATOR . $template['filename'];

            // Optionally remove vendor/ path prefixes
            $resource = ModuleResourceLoader::singleton()->resolveResource($emailTemplateDirectory);
            if ($resource instanceof ModuleResource && $resource->getModule()) {
                $prefixToStrip = $resource->getModule()->getPath();
            } else {
                $prefixToStrip = BASE_PATH;
            }
            $templatePath = substr($absoluteFilename ?? '', strlen($prefixToStrip ?? '') + 1);

            // Optionally remove "templates/" ("templates\" on Windows respectively) prefixes
            if (preg_match('#(?<=templates' . preg_quote(DIRECTORY_SEPARATOR, '#') . ').*$#', $templatePath ?? '', $matches)) {
                $templatePath = $matches[0];
            }

            $templates[$templatePath] = $template['filename'];
        }

        return $templates;
    }

    /**
     * Validate that valid email addresses are being used
     *
     * @return ValidationResult
     */
    public function validate()
    {
        $result = parent::validate();
        $checkEmail = [
            'EmailAddress' => 'EMAILADDRESSINVALID',
            'EmailFrom' => 'EMAILFROMINVALID',
            'EmailReplyTo' => 'EMAILREPLYTOINVALID',
        ];
        foreach ($checkEmail as $check => $translation) {
            if ($this->$check) {
                //may be a comma separated list of emails
                $addresses = explode(',', $this->$check ?? '');
                foreach ($addresses as $address) {
                    $trimAddress = trim($address ?? '');
                    if ($trimAddress && !Email::is_valid_address($trimAddress)) {
                        $error = _t(
                            __CLASS__.".$translation",
                            "Invalid email address $trimAddress"
                        );
                        $result->addError($error . " ($trimAddress)");
                    }
                }
            }
        }

        // if there is no from address and no fallback, you'll have errors if this isn't defined
        if (!$this->EmailFrom && empty(Email::getSendAllEmailsFrom()) && empty(Email::config()->get('admin_email'))) {
            $result->addError(_t(__CLASS__.".EMAILFROMREQUIRED", '"Email From" address is required'));
        }

        // Sending will also fail if there's no recipient defined
        if (!$this->EmailAddress && !$this->SendEmailToFieldID) {
            $result->addError(_t(__CLASS__.".EMAILTOREQUIRED", '"Send email to" address or field is required'));
        }

        return $result;
    }

    /**
     * @return FieldGroup|TextField
     */
    protected function getSubjectCMSFields()
    {
        $subjectTextField = TextField::create(
            'EmailSubject',
            _t('SilverStripe\\UserForms\\Model\\UserDefinedForm.TYPESUBJECT', 'Type subject')
        )
            ->setAttribute('style', 'min-width: 400px;');

        if ($this->getFormParent() && $this->getValidSubjectFields()) {
            return FieldGroup::create(
                $subjectTextField,
                DropdownField::create(
                    'SendEmailSubjectFieldID',
                    _t(
                        'SilverStripe\\UserForms\\Model\\UserDefinedForm.SELECTAFIELDTOSETSUBJECT',
                        '.. or select a field to use as the subject'
                    ),
                    $this->getValidSubjectFields()->map('ID', 'Title')
                )->setEmptyString('')
            )
                ->setTitle(_t('SilverStripe\\UserForms\\Model\\UserDefinedForm.EMAILSUBJECT', 'Email subject'));
        } else {
            return $subjectTextField;
        }
    }

    /**
     * @return FieldGroup|TextField
     */
    protected function getEmailToCMSFields()
    {
        $emailToTextField = TextField::create(
            'EmailAddress',
            _t('SilverStripe\\UserForms\\Model\\UserDefinedForm.TYPETO', 'Type to address')
        )
            ->setAttribute('style', 'min-width: 400px;');

        if ($this->getFormParent() && $this->getValidEmailToFields()) {
            return FieldGroup::create(
                $emailToTextField,
                DropdownField::create(
                    'SendEmailToFieldID',
                    _t(
                        'SilverStripe\\UserForms\\Model\\UserDefinedForm.ORSELECTAFIELDTOUSEASTO',
                        '.. or select a field to use as the to address'
                    ),
                    $this->getValidEmailToFields()->map('ID', 'Title')
                )->setEmptyString(' ')
            )
                ->setTitle(_t('SilverStripe\\UserForms\\Model\\UserDefinedForm.SENDEMAILTO', 'Send email to'))
                ->setDescription(_t(
                    'SilverStripe\\UserForms\\Model\\UserDefinedForm.SENDEMAILTO_DESCRIPTION',
                    'You may enter multiple email addresses as a comma separated list.'
                ));
        } else {
            return $emailToTextField;
        }
    }

    /**
     * @return TextField
     */
    protected function getEmailFromCMSFields()
    {
        return TextField::create(
            'EmailFrom',
            _t('SilverStripe\\UserForms\\Model\\UserDefinedForm.FROMADDRESS', 'Send email from')
        )
            ->setDescription(_t(
                'SilverStripe\\UserForms\\Model\\UserDefinedForm.EmailFromContent',
                "The from address allows you to set who the email comes from. On most servers this " .
                "will need to be set to an email address on the same domain name as your site. " .
                "For example on yoursite.com the from address may need to be something@yoursite.com. " .
                "You can however, set any email address you wish as the reply to address."
            ));
    }

    /**
     * @return FieldGroup|TextField
     */
    protected function getEmailReplyToCMSFields()
    {
        $replyToTextField = TextField::create('EmailReplyTo', _t(
            'SilverStripe\\UserForms\\Model\\UserDefinedForm.TYPEREPLY',
            'Type reply address'
        ))
            ->setAttribute('style', 'min-width: 400px;');
        if ($this->getFormParent() && $this->getValidEmailFromFields()) {
            return FieldGroup::create(
                $replyToTextField,
                DropdownField::create(
                    'SendEmailFromFieldID',
                    _t(
                        'SilverStripe\\UserForms\\Model\\UserDefinedForm.ORSELECTAFIELDTOUSEASFROM',
                        '.. or select a field to use as reply to address'
                    ),
                    $this->getValidEmailFromFields()->map('ID', 'Title')
                )->setEmptyString(' ')
            )
                ->setTitle(_t(
                    'SilverStripe\\UserForms\\Model\\UserDefinedForm.REPLYADDRESS',
                    'Email for reply to'
                ))
                ->setDescription(_t(
                    'SilverStripe\\UserForms\\Model\\UserDefinedForm.REPLYADDRESS_DESCRIPTION',
                    'The email address which the recipient is able to \'reply\' to.'
                ));
        } else {
            return $replyToTextField;
        }
    }

    /**
     * @return DataList<EditableMultipleOptionField>|null
     */
    protected function getMultiOptionFields()
    {
        if (!$form = $this->getFormParent()) {
            return null;
        }
        return EditableMultipleOptionField::get()->filter('ParentID', $form->ID);
    }

    /**
     * @return ArrayList<EditableFormField>|null
     */
    protected function getValidSubjectFields()
    {
        if (!$form = $this->getFormParent()) {
            return null;
        }
        // For the subject, only one-line entry boxes make sense
        $validSubjectFields = ArrayList::create(
            EditableTextField::get()
                ->filter('ParentID', $form->ID)
                ->exclude('Rows:GreaterThan', 1)
                ->toArray()
        );
        $validSubjectFields->merge($this->getMultiOptionFields());
        return $validSubjectFields;
    }

    /**
     * @return DataList<EditableEmailField>|null
     */
    protected function getValidEmailFromFields()
    {
        if (!$form = $this->getFormParent()) {
            return null;
        }

        // if they have email fields then we could send from it
        return EditableEmailField::get()->filter('ParentID', $form->ID);
    }

    /**
     * @return ArrayList<EditableFormField>|DataList<EditableFormField>|null
     */
    protected function getValidEmailToFields()
    {
        if (!$this->getFormParent()) {
            return null;
        }

        // Check valid email-recipient fields
        if ($this->config()->get('allow_unbound_recipient_fields')) {
            // To address can only be email fields or multi option fields
            $validEmailToFields = ArrayList::create($this->getValidEmailFromFields()->toArray());
            $validEmailToFields->merge($this->getMultiOptionFields());
            return $validEmailToFields;
        } else {
            // To address cannot be unbound, so restrict to pre-defined lists
            return $this->getMultiOptionFields();
        }
    }

    protected function getUnsavedFormLiteralField()
    {
        return LiteralField::create(
            'UnsavedFormMessage',
            sprintf(
                '<p class="alert alert-warning">%s</p>',
                _t(
                    'SilverStripe\\UserForms\\Model\\UserDefinedForm.EMAIL_RECIPIENT_UNSAVED_FORM',
                    'You will be able to select from valid form fields after saving this record.'
                )
            )
        );
    }
}
