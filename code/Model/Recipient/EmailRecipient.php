<?php

namespace SilverStripe\UserForms\Model\Recipient;

use SilverStripe\Assets\FileFinder;
use SilverStripe\CMS\Controllers\CMSMain;
use SilverStripe\CMS\Controllers\CMSPageEditController;
use SilverStripe\Control\Controller;
use SilverStripe\Control\Email\Email;
use SilverStripe\Control\Session;
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
use SilverStripe\ORM\DataObject;
use SilverStripe\UserForms\Model\EditableFormField\EditableEmailField;
use SilverStripe\UserForms\Model\EditableFormField;
use SilverStripe\UserForms\Model\EditableFormField\EditableMultipleOptionField;
use SilverStripe\UserForms\Model\EditableFormField\EditableTextField;
use SilverStripe\UserForms\Model\Recipient\EmailRecipientCondition;
use SilverStripe\UserForms\Model\UserDefinedForm;
use SilverStripe\View\Requirements;
use Symbiote\GridFieldExtensions\GridFieldAddNewInlineButton;
use Symbiote\GridFieldExtensions\GridFieldEditableColumns;

/**
 * A Form can have multiply members / emails to email the submission
 * to and custom subjects
 *
 * @package userforms
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
        'CustomRulesCondition' => 'Enum("And,Or")'
    ];

    private static $has_one = [
        'Form' => UserDefinedForm::class,
        'SendEmailFromField' => EditableFormField::class,
        'SendEmailToField' => EditableFormField::class,
        'SendEmailSubjectField' => EditableFormField::class
    ];

    private static $has_many = [
        'CustomRules' => EmailRecipientCondition::class
    ];

    private static $summary_fields = [
        'EmailAddress',
        'EmailSubject',
        'EmailFrom'
    ];

    private static $table_name = 'UserDefinedForm_EmailRecipient';

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

    public function summaryFields()
    {
        $fields = parent::summaryFields();
        if (isset($fields['EmailAddress'])) {
            $fields['EmailAddress'] = _t('SilverStripe\\UserForms\\Model\\UserDefinedForm.EMAILADDRESS', Email::class);
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
     * Get instance of UserDefinedForm when editing in getCMSFields
     *
     * @return UserDefinedFrom
     */
    protected function getFormParent()
    {
        $formID = $this->FormID
            ? $this->FormID
            : Session::get('CMSMain.currentPage');
        return UserDefinedForm::get()->byID($formID);
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
        Requirements::javascript(USERFORMS_DIR . '/javascript/Recipient.js');

        // Determine optional field values
        $form = $this->getFormParent();

        // predefined choices are also candidates
        $multiOptionFields = EditableMultipleOptionField::get()->filter('ParentID', $form->ID);

        // if they have email fields then we could send from it
        $validEmailFromFields = EditableEmailField::get()->filter('ParentID', $form->ID);

        // For the subject, only one-line entry boxes make sense
        $validSubjectFields = ArrayList::create(
            EditableTextField::get()
                ->filter('ParentID', $form->ID)
                ->exclude('Rows:GreaterThan', 1)
                ->toArray()
        );
        $validSubjectFields->merge($multiOptionFields);


        // Check valid email-recipient fields
        if ($this->config()->get('allow_unbound_recipient_fields')) {
            // To address can only be email fields or multi option fields
            $validEmailToFields = ArrayList::create($validEmailFromFields->toArray());
            $validEmailToFields->merge($multiOptionFields);
        } else {
            // To address cannot be unbound, so restrict to pre-defined lists
            $validEmailToFields = $multiOptionFields;
        }

        // Build fieldlist
        $fields = FieldList::create(Tabset::create('Root')->addExtraClass('EmailRecipientForm'));

        // Configuration fields
        $fields->addFieldsToTab('Root.EmailDetails', [
            // Subject
            FieldGroup::create(
                TextField::create(
                    'EmailSubject',
                    _t('SilverStripe\\UserForms\\Model\\UserDefinedForm.TYPESUBJECT', 'Type subject')
                )
                    ->setAttribute('style', 'min-width: 400px;'),
                DropdownField::create(
                    'SendEmailSubjectFieldID',
                    _t(
                        'SilverStripe\\UserForms\\Model\\UserDefinedForm.SELECTAFIELDTOSETSUBJECT',
                        '.. or select a field to use as the subject'
                    ),
                    $validSubjectFields->map('ID', 'Title')
                )->setEmptyString('')
            )
                ->setTitle(_t('SilverStripe\\UserForms\\Model\\UserDefinedForm.EMAILSUBJECT', 'Email subject')),

            // To
            FieldGroup::create(
                TextField::create(
                    'EmailAddress',
                    _t('SilverStripe\\UserForms\\Model\\UserDefinedForm.TYPETO', 'Type to address')
                )
                    ->setAttribute('style', 'min-width: 400px;'),
                DropdownField::create(
                    'SendEmailToFieldID',
                    _t(
                        'SilverStripe\\UserForms\\Model\\UserDefinedForm.ORSELECTAFIELDTOUSEASTO',
                        '.. or select a field to use as the to address'
                    ),
                    $validEmailToFields->map('ID', 'Title')
                )->setEmptyString(' ')
            )
                ->setTitle(_t('SilverStripe\\UserForms\\Model\\UserDefinedForm.SENDEMAILTO', 'Send email to'))
                ->setDescription(_t(
                    'SilverStripe\\UserForms\\Model\\UserDefinedForm.SENDEMAILTO_DESCRIPTION',
                    'You may enter multiple email addresses as a comma separated list.'
                )),


            // From
            TextField::create(
                'EmailFrom',
                _t('SilverStripe\\UserForms\\Model\\UserDefinedForm.FROMADDRESS', 'Send email from')
            )
                ->setDescription(_t(
                    'SilverStripe\\UserForms\\Model\\UserDefinedForm.EmailFromContent',
                    "The from address allows you to set who the email comes from. On most servers this ".
                    "will need to be set to an email address on the same domain name as your site. ".
                    "For example on yoursite.com the from address may need to be something@yoursite.com. ".
                    "You can however, set any email address you wish as the reply to address."
                )),


            // Reply-To
            FieldGroup::create(
                TextField::create('EmailReplyTo', _t(
                    'SilverStripe\\UserForms\\Model\\UserDefinedForm.TYPEREPLY',
                    'Type reply address'
                ))
                    ->setAttribute('style', 'min-width: 400px;'),
                DropdownField::create(
                    'SendEmailFromFieldID',
                    _t(
                        'SilverStripe\\UserForms\\Model\\UserDefinedForm.ORSELECTAFIELDTOUSEASFROM',
                        '.. or select a field to use as reply to address'
                    ),
                    $validEmailFromFields->map('ID', 'Title')
                )->setEmptyString(' ')
            )
                ->setTitle(_t(
                    'SilverStripe\\UserForms\\Model\\UserDefinedForm.REPLYADDRESS',
                    'Email for reply to'
                ))
                ->setDescription(_t(
                    'SilverStripe\\UserForms\\Model\\UserDefinedForm.REPLYADDRESS_DESCRIPTION',
                    'The email address which the recipient is able to \'reply\' to.'
                ))
        ]);

        $fields->fieldByName('Root.EmailDetails')->setTitle(_t(__CLASS.'.EMAILDETAILSTAB', 'Email Details'));

        // Only show the preview link if the recipient has been saved.
        if (!empty($this->EmailTemplate)) {
            $preview = sprintf(
                '<p><a href="%s" target="_blank" class="ss-ui-button">%s</a></p><em>%s</em>',
                Controller::join_links(
                    singleton(CMSPageEditController::class)->getEditForm()->FormAction(),
                    "field/EmailRecipients/item/{$this->ID}/preview"
                ),
                _t('SilverStripe\\UserForms\\Model\\UserDefinedForm.PREVIEW_EMAIL', 'Preview email'),
                _t(
                    'SilverStripe\\UserForms\\Model\\UserDefinedForm.PREVIEW_EMAIL_DESCRIPTION',
                    'Note: Unsaved changes will not appear in the preview.'
                )
            );
        } else {
            $preview = sprintf(
                '<em>%s</em>',
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
                'SendPlain',
                _t(
                    'SilverStripe\\UserForms\\Model\\UserDefinedForm.SENDPLAIN',
                    'Send email as plain text? (HTML will be stripped)'
                )
            ),
            DropdownField::create(
                'EmailTemplate',
                _t('SilverStripe\\UserForms\\Model\\UserDefinedForm.EMAILTEMPLATE', 'Email template'),
                $this->getEmailTemplateDropdownValues()
            )->addExtraClass('toggle-html-only'),
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
        ));

        $fields->fieldByName('Root.EmailContent')->setTitle(_t(__CLASS.'.EMAILCONTENTTAB', 'Email Content'));

        // Custom rules for sending this field
        $grid = GridField::create(
            'CustomRules',
            _t('SilverStripe\\UserForms\\Model\\EditableFormField.CUSTOMRULES', 'Custom Rules'),
            $this->CustomRules(),
            $this->getRulesConfig()
        );
        $grid->setDescription(_t(
            'SilverStripe\\UserForms\\Model\\UserDefinedForm.RulesDescription',
            'Emails will only be sent to the recipient if the custom rules are met. If no rules are defined, this receipient will receive notifications for every submission.'
        ));
        $fields->addFieldsToTab('Root.CustomRules', [
            DropdownField::create(
                'CustomRulesCondition',
                _t('SilverStripe\\UserForms\\Model\\UserDefinedForm.SENDIF', 'Send condition'),
                [
                    'Or' => _t('SilverStripe\\UserForms\\Model\\UserDefinedForm.SENDIFOR', 'Any conditions are true'),
                    'And' => _t('SilverStripe\\UserForms\\Model\\UserDefinedForm.SENDIFAND', 'All conditions are true')
                ]
            ),
            $grid
        ]);

        $fields->fieldByName('Root.CustomRules')->setTitle(_t(__CLASS.'.CUSTOMRULESTAB', 'Custom Rules'));

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

    /**
     * @param Member
     *
     * @return boolean
     */
    public function canView($member = null)
    {
        return $this->Form()->canView($member);
    }

    /**
     * @param Member
     *
     * @return boolean
     */
    public function canEdit($member = null)
    {
        return $this->Form()->canEdit($member);
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

    /*
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
            /** @var EmailRecipientCondition  $customRule */
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

        return in_array($t, $this->getEmailTemplateDropdownValues());
    }

    /**
     * Get the email body for the current email format
     *
     * @return string
     */
    public function getEmailBodyContent()
    {
        if ($this->SendPlain) {
            return DBField::create_field('HTMLText', $this->EmailBody)->NoHTML();
        }
        return DBField::create_field('HTMLText', $this->EmailBodyHtml)->RAW();
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

        $found = $finder->find(BASE_PATH . '/' . UserDefinedForm::config()->get('email_template_directory'));

        foreach ($found as $key => $value) {
            $template = pathinfo($value);

            $templates[$template['filename']] = $template['filename'];
        }

        return $templates;
    }

    /**
     * Validate that valid email addresses are being used
     *
     * @return ValidationResult
     */
    public function validate() {
        $result = parent::validate();
        $checkEmail = [
            'EmailAddress' => 'EMAILADDRESSINVALID',
            'EmailFrom' => 'EMAILFROMINVALID',
            'EmailReplyTo' => 'EMAILREPLYTOINVALID',
        ];
        foreach ($checkEmail as $check => $translation) {
            if ($this->$check) {
                //may be a comma separated list of emails
                $addresses = explode(',', $this->$check);
                foreach ($addresses as $address) {
                    $trimAddress = trim($address);
                    if ($trimAddress && !Email::is_valid_address($trimAddress)) {
                        $error = _t(__CLASS.".$translation",
                                "Invalid email address $trimAddress");
                        $result->error($error . " ($trimAddress)");
                    }
                }
            }
        }
        return $result;
    }
}
