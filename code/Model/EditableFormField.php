<?php

namespace SilverStripe\UserForms\Model;

use SilverStripe\CMS\Controllers\CMSMain;
use SilverStripe\CMS\Controllers\CMSPageEditController;
use SilverStripe\Control\Controller;
use SilverStripe\Core\ClassInfo;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Manifest\ModuleLoader;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\FormField;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldButtonRow;
use SilverStripe\Forms\GridField\GridFieldConfig;
use SilverStripe\Forms\GridField\GridFieldDeleteAction;
use SilverStripe\Forms\GridField\GridFieldToolbarHeader;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\ReadonlyField;
use SilverStripe\Forms\SegmentField;
use SilverStripe\Forms\TabSet;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\DB;
use SilverStripe\ORM\FieldType\DBField;
use SilverStripe\ORM\FieldType\DBVarchar;
use SilverStripe\ORM\HasManyList;
use SilverStripe\Core\Validation\ValidationException;
use SilverStripe\UserForms\Extension\UserFormFieldEditorExtension;
use SilverStripe\UserForms\Model\EditableFormField\EditableFieldGroup;
use SilverStripe\UserForms\Model\EditableFormField\EditableFieldGroupEnd;
use SilverStripe\UserForms\Model\EditableFormField\EditableFormStep;
use SilverStripe\UserForms\Model\Submission\SubmittedFormField;
use SilverStripe\UserForms\Modifier\DisambiguationSegmentFieldModifier;
use SilverStripe\UserForms\Modifier\UnderscoreSegmentFieldModifier;
use SilverStripe\Versioned\Versioned;
use Symbiote\GridFieldExtensions\GridFieldAddNewInlineButton;
use Symbiote\GridFieldExtensions\GridFieldEditableColumns;

/**
 * Represents the base class of a editable form field
 * object like {@link EditableTextField}.
 *
 * @package userforms
 *
 * @property string $CustomErrorMessage
 * @property string $Default
 * @property string $DisplayRulesConjunction
 * @property string $ExtraClass
 * @property string $Name
 * @property int $ParentID
 * @property string $Placeholder
 * @property string $RightTitle
 * @property bool $Required
 * @property int $ShowInSummary
 * @property int $ShowOnLoad
 * @property int $Sort
 * @mixin Versioned
 * @method HasManyList<EditableCustomRule> DisplayRules()
 * @method DataObject Parent()
 */
class EditableFormField extends DataObject
{
    /**
     * Set to true to hide from class selector
     *
     * @config
     * @var bool
     */
    private static $hidden = false;

    /**
     * Define this field as abstract (not inherited)
     *
     * @config
     * @var bool
     */
    private static $abstract = true;

    /**
     * Flag this field type as non-data (e.g. literal, header, html)
     *
     * @config
     * @var bool
     */
    private static $literal = false;

    /**
     * Default sort order
     *
     * @config
     * @var string
     */
    private static $default_sort = '"Sort"';

    /**
     * A list of CSS classes that can be added
     *
     * @var array
     */
    public static $allowed_css = [];

    /**
     * Set this to true to enable placeholder field for any given class
     * @config
     * @var bool
     */
    private static $has_placeholder = false;

    /**
     * @config
     * @var array
     */
    private static $summary_fields = [
        'Title'
    ];

    /**
     * @config
     * @var array
     */
    private static $db = [
        'Name' => 'Varchar',
        'Title' => 'Varchar(255)',
        'Default' => 'Varchar(255)',
        'Sort' => 'Int',
        'Required' => 'Boolean',
        'CustomErrorMessage' => 'Varchar(255)',
        'ExtraClass' => 'Text',
        'RightTitle' => 'Varchar(255)',
        'ShowOnLoad' => 'Boolean(1)',
        'ShowInSummary' => 'Boolean',
        'Placeholder' => 'Varchar(255)',
        'DisplayRulesConjunction' => 'Enum("And,Or","Or")',
    ];

    private static $table_name = 'EditableFormField';

    private static $defaults = [
        'ShowOnLoad' => true,
    ];

    private static $indexes = [
        'Name' => 'Name',
    ];


    /**
     * @config
     * @var array
     */
    private static $has_one = [
        'Parent' => DataObject::class,
    ];

    /**
     * Built in extensions required
     *
     * @config
     * @var array
     */
    private static $extensions = [
        Versioned::class . "('Stage', 'Live')"
    ];

    /**
     * @config
     * @var array
     */
    private static $has_many = [
        'DisplayRules' => EditableCustomRule::class . '.Parent'
    ];

    private static $owns = [
        'DisplayRules',
    ];

    private static $cascade_deletes = [
        'DisplayRules',
    ];

    private static $cascade_duplicates = [
        'DisplayRules',
    ];

    /**
     * This is protected rather that private so that it's unit testable
     */
    protected static $isDisplayedRecursionProtection = [];

    /**
     * @var bool
     */
    protected $readonly;

    /**
     * Property holds the JS event which gets fired for this type of element
     *
     * @var string
     */
    protected $jsEventHandler = 'change';

    /**
     * Returns the jsEventHandler property for the current object. Bearing in mind it could've been overridden.
     * @return string
     */
    public function getJsEventHandler()
    {
        return $this->jsEventHandler;
    }

    /**
     * Set the visibility of an individual form field
     *
     * @param bool
     * @return $this
     */
    public function setReadonly($readonly = true)
    {
        $this->readonly = $readonly;
        return $this;
    }

    /**
     * Returns whether this field is readonly
     *
     * @return bool
     */
    private function isReadonly()
    {
        return $this->readonly;
    }

    /**
     * @return FieldList
     */
    public function getCMSFields()
    {
        $fields = FieldList::create(TabSet::create('Root'));

        // If created with (+) button
        if ($this->ClassName === EditableFormField::class) {
            $fieldClasses = $this->getEditableFieldClasses();
            $fields->addFieldsToTab('Root.Main', [
                DropdownField::create('ClassName', _t(__CLASS__.'.TYPE', 'Type'), $fieldClasses)
                    ->setEmptyString(_t(__CLASS__ . '.TYPE_EMPTY', 'Select field type'))
            ]);
            return $fields;
        }

        // Main tab
        $fields->addFieldsToTab(
            'Root.Main',
            [
                ReadonlyField::create(
                    'Type',
                    _t(__CLASS__.'.TYPE', 'Type'),
                    $this->i18n_singular_name()
                ),
                CheckboxField::create('ShowInSummary', _t(__CLASS__.'.SHOWINSUMMARY', 'Show in summary gridfield')),
                LiteralField::create(
                    'MergeField',
                    '<div class="form-group field readonly">' .
                        '<label class="left form__field-label" for="Form_ItemEditForm_MergeField">'
                            . _t(__CLASS__.'.MERGEFIELDNAME', 'Merge field')
                        . '</label>'
                        . '<div class="form__field-holder">'
                            . '<span class="readonly" id="Form_ItemEditForm_MergeField">$' . $this->Name . '</span>'
                        . '</div>'
                    . '</div>'
                ),
                TextField::create('Title', _t(__CLASS__.'.TITLE', 'Title')),
                TextField::create('Default', _t(__CLASS__.'.DEFAULT', 'Default value')),
                TextField::create('RightTitle', _t(__CLASS__.'.RIGHTTITLE', 'Right title')),
                SegmentField::create('Name', _t(__CLASS__.'.NAME', 'Name'))->setModifiers([
                    UnderscoreSegmentFieldModifier::create()->setDefault('FieldName'),
                    DisambiguationSegmentFieldModifier::create(),
                ])->setPreview($this->Name)
            ]
        );
        $fields->fieldByName('Root.Main')->setTitle(_t('SilverStripe\\CMS\\Model\\SiteTree.TABMAIN', 'Main'));

        // Custom settings
        if (!empty(EditableFormField::$allowed_css)) {
            $cssList = [];
            foreach (EditableFormField::$allowed_css as $k => $v) {
                if (!is_array($v)) {
                    $cssList[$k]=$v;
                } elseif ($k === $this->ClassName) {
                    $cssList = array_merge($cssList, $v);
                }
            }

            $fields->addFieldToTab(
                'Root.Main',
                DropdownField::create(
                    'ExtraClass',
                    _t(__CLASS__.'.EXTRACLASS_TITLE', 'Extra Styling/Layout'),
                    $cssList
                )->setDescription(_t(
                    __CLASS__.'.EXTRACLASS_SELECT',
                    'Select from the list of allowed styles'
                ))
            );
        } else {
            $fields->addFieldToTab(
                'Root.Main',
                TextField::create(
                    'ExtraClass',
                    _t(__CLASS__.'.EXTRACLASS_Title', 'Extra CSS classes')
                )->setDescription(_t(
                    __CLASS__.'.EXTRACLASS_MULTIPLE',
                    'Separate each CSS class with a single space'
                ))
            );
        }

        // Validation
        $validationFields = $this->getFieldValidationOptions();
        if ($validationFields && $validationFields->count()) {
            $fields->addFieldsToTab('Root.Validation', $validationFields->toArray());
            $fields->fieldByName('Root.Validation')->setTitle(_t(__CLASS__.'.VALIDATION', 'Validation'));
        }

        // Add display rule fields
        $displayFields = $this->getDisplayRuleFields();
        if ($displayFields && $displayFields->count()) {
            $fields->addFieldsToTab('Root.DisplayRules', $displayFields->toArray());
        }

        // Placeholder
        if ($this->config()->has_placeholder) {
            $fields->addFieldToTab(
                'Root.Main',
                TextField::create(
                    'Placeholder',
                    _t(__CLASS__.'.PLACEHOLDER', 'Placeholder')
                )
            );
        }

        $this->extend('updateCMSFields', $fields);

        return $fields;
    }


    public function requireDefaultRecords()
    {
        parent::requireDefaultRecords();

        // make sure to migrate the class across (prior to v5.x)
        DB::query("UPDATE \"EditableFormField\" SET \"ParentClass\" = 'Page' WHERE \"ParentClass\" IS NULL");
        if (EditableFormField::has_extension(Versioned::class)) {
            DB::query("UPDATE \"EditableFormField_Live\" SET \"ParentClass\" = 'Page' WHERE \"ParentClass\" IS NULL");
            DB::query("UPDATE \"EditableFormField_Versions\" SET \"ParentClass\" = 'Page' WHERE \"ParentClass\" IS NULL");
        }
    }

    /**
     * Return fields to display on the 'Display Rules' tab
     *
     * @return FieldList
     */
    protected function getDisplayRuleFields()
    {
        $allowedClasses = array_keys($this->getEditableFieldClasses(false) ?? []);
        $editableColumns = new GridFieldEditableColumns();
        $editableColumns->setDisplayFields([
            'ConditionFieldID' => function ($record, $column, $grid) use ($allowedClasses) {
                    return DropdownField::create($column, '', EditableFormField::get()->filter([
                            'ParentID' => $this->ParentID,
                            'ClassName' => $allowedClasses,
                        ])->exclude([
                            'ID' => $this->ID,
                        ])->map('ID', 'Title'));
            },
            'ConditionOption' => function ($record, $column, $grid) {
                $options = Config::inst()->get(EditableCustomRule::class, 'condition_options');

                return DropdownField::create($column, '', $options);
            },
            'FieldValue' => function ($record, $column, $grid) {
                return TextField::create($column);
            }
        ]);

        // Custom rules
        $customRulesConfig = GridFieldConfig::create()
            ->addComponents(
                $editableColumns,
                new GridFieldButtonRow(),
                new GridFieldToolbarHeader(),
                new GridFieldAddNewInlineButton(),
                new GridFieldDeleteAction()
            );

        return new FieldList(
            DropdownField::create(
                'ShowOnLoad',
                _t(__CLASS__.'.INITIALVISIBILITY', 'Initial visibility'),
                [
                    1 => 'Show',
                    0 => 'Hide',
                ]
            ),
            DropdownField::create(
                'DisplayRulesConjunction',
                _t(__CLASS__.'.DISPLAYIF', 'Toggle visibility when'),
                [
                    'Or'  => _t('SilverStripe\\UserForms\\Model\\UserDefinedForm.SENDIFOR', 'Any conditions are true'),
                    'And' => _t('SilverStripe\\UserForms\\Model\\UserDefinedForm.SENDIFAND', 'All conditions are true'),
                ]
            ),
            GridField::create(
                'DisplayRules',
                _t(__CLASS__.'.CUSTOMRULES', 'Custom Rules'),
                $this->DisplayRules(),
                $customRulesConfig
            )
        );
    }

    protected function onBeforeWrite()
    {
        parent::onBeforeWrite();

        $formField = $this->getFormField();
        if ($formField && !$formField->hasData()) {
            $this->Required = false;
        }

        // Set a field name.
        if (!$this->Name) {
            // New random name
            $this->Name = $this->generateName();
        } elseif ($this->Name === 'Field') {
            throw new ValidationException('Field name cannot be "Field"');
        }

        if (!$this->Sort && $this->ParentID) {
            $parentID = $this->ParentID;
            $this->Sort = EditableFormField::get()
                ->filter('ParentID', $parentID)
                ->max('Sort') + 1;
        }
    }

    /**
     * Generate a new non-conflicting Name value
     *
     * @return string
     */
    protected function generateName()
    {
        do {
            // Generate a new random name after this class (handles namespaces)
            $classNamePieces = explode('\\', static::class);
            $class = array_pop($classNamePieces);
            $entropy = substr(sha1(uniqid()), 0, 5);
            $name = "{$class}_{$entropy}";

            // Check if it conflicts
            $exists = EditableFormField::get()->filter('Name', $name)->count() > 0;
        } while ($exists);
        return $name;
    }

    /**
     * Flag indicating that this field will set its own error message via data-msg='' attributes
     *
     * @return bool
     */
    public function getSetsOwnError()
    {
        return false;
    }

    /**
     * Return whether a user can delete this form field
     * based on whether they can edit the page
     *
     * @param Member $member
     * @return bool
     */
    public function canDelete($member = null)
    {
        return $this->canEdit($member);
    }

    /**
     * Return whether a user can edit this form field
     * based on whether they can edit the page
     *
     * @param Member $member
     * @return bool
     */
    public function canEdit($member = null)
    {
        $parent = $this->Parent();
        if ($parent && $parent->exists()) {
            return $parent->canEdit($member) && !$this->isReadonly();
        } elseif (!$this->exists() && Controller::has_curr()) {
            // This is for GridFieldOrderableRows support as it checks edit permissions on
            // singleton of the class. Allows editing of User Defined Form pages by
            // 'Content Authors' and those with permission to edit the UDF page. (ie. CanEditType/EditorGroups)
            // This is to restore User Forms 2.x backwards compatibility.
            $controller = Controller::curr();
            if ($controller && $controller instanceof CMSPageEditController) {
                $parent = $controller->getRecord($controller->currentRecordID());
                // Only allow this behaviour on pages using UserFormFieldEditorExtension, such
                // as UserDefinedForm page type.
                if ($parent && $parent->hasExtension(UserFormFieldEditorExtension::class)) {
                    return $parent->canEdit($member);
                }
            }
        }

        // Fallback to secure admin permissions
        return parent::canEdit($member);
    }

    /**
     * Return whether a user can view this form field
     * based on whether they can view the page, regardless of the ReadOnly status of the field
     *
     * @param Member $member
     * @return bool
     */
    public function canView($member = null)
    {
        $parent = $this->Parent();
        if ($parent && $parent->exists()) {
            return $parent->canView($member);
        }

        return true;
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
        if (isset($args[1]['Parent'])) {
            return $args[1]['Parent'];
        }
        // Hack in currently edited page if context is missing
        if (Controller::has_curr() && Controller::curr() instanceof CMSMain) {
            return Controller::curr()->currentRecord();
        }

        // No page being edited
        return null;
    }

    /**
     * checks whether record is new, copied from SiteTree
     */
    public function isNew()
    {
        if (empty($this->ID)) {
            return true;
        }

        if (is_numeric($this->ID)) {
            return false;
        }

        return stripos($this->ID ?? '', 'new') === 0;
    }

    /**
     * Set the allowed css classes for the extraClass custom setting
     *
     * @param array $allowed The permissible CSS classes to add
     */
    public function setAllowedCss(array $allowed)
    {
        if (is_array($allowed)) {
            foreach ($allowed as $k => $v) {
                EditableFormField::$allowed_css[$k] = (!is_null($v)) ? $v : $k;
            }
        }
    }

    /**
     * Get the path to the icon for this field type, relative to the site root.
     *
     * @return string
     */
    public function getIcon()
    {
        $classNamespaces = explode("\\", static::class);
        $shortClass = end($classNamespaces);

        $resource = ModuleLoader::getModule('silverstripe/userforms')
            ->getResource('images/' . strtolower($shortClass ?? '') . '.png');

        if (!$resource->exists()) {
            return '';
        }

        return $resource->getURL();
    }

    /**
     * Return whether or not this field has addable options
     * such as a dropdown field or radio set
     *
     * @return bool
     */
    public function getHasAddableOptions()
    {
        return false;
    }

    /**
     * Return whether or not this field needs to show the extra
     * options dropdown list
     *
     * @return bool
     */
    public function showExtraOptions()
    {
        return true;
    }

    /**
     * Find the numeric indicator (1.1.2) that represents it's nesting value
     *
     * Only useful for fields attached to a current page, and that contain other fields such as pages
     * or groups
     *
     * @return string
     */
    public function getFieldNumber()
    {
        // Check if exists
        if (!$this->exists()) {
            return null;
        }
        // Check parent
        $form = $this->Parent();
        if (!$form || !$form->exists() || !($fields = $form->Fields())) {
            return null;
        }

        $prior = 0; // Number of prior group at this level
        $stack = []; // Current stack of nested groups, where the top level = the page
        foreach ($fields->map('ID', 'ClassName') as $id => $className) {
            if ($className === EditableFormStep::class) {
                $priorPage = empty($stack) ? $prior : $stack[0];
                $stack = array($priorPage + 1);
                $prior = 0;
            } elseif ($className === EditableFieldGroup::class) {
                $stack[] = $prior + 1;
                $prior = 0;
            } elseif ($className === EditableFieldGroupEnd::class) {
                $prior = array_pop($stack);
            }
            if ($id == $this->ID) {
                return implode('.', $stack);
            }
        }
        return null;
    }

    public function getCMSTitle()
    {
        return $this->i18n_singular_name() . ' (' . $this->Title . ')';
    }

    /**
     * Append custom validation fields to the default 'Validation'
     * section in the editable options view
     *
     * @return FieldList
     */
    public function getFieldValidationOptions()
    {
        $fields = new FieldList(
            CheckboxField::create('Required', _t(__CLASS__.'.REQUIRED', 'Is this field Required?'))
                ->setDescription(_t(__CLASS__.'.REQUIRED_DESCRIPTION', 'Please note that conditional fields can\'t be required')),
            TextField::create('CustomErrorMessage', _t(__CLASS__.'.CUSTOMERROR', 'Custom Error Message'))
        );

        $this->extend('updateFieldValidationOptions', $fields);

        return $fields;
    }

    /**
     * Return a FormField to appear on the front end. Implement on
     * your subclass.
     *
     * @return FormField
     */
    public function getFormField()
    {
        user_error("Please implement a getFormField() on your EditableFormClass ". $this->ClassName, E_USER_ERROR);
    }

    /**
     * Updates a formfield with extensions
     *
     * @param FormField $field
     */
    public function doUpdateFormField($field)
    {
        $this->extend('beforeUpdateFormField', $field);
        $this->updateFormField($field);
        $this->extend('afterUpdateFormField', $field);
    }

    /**
     * Updates a formfield with the additional metadata specified by this field
     *
     * @param FormField $field
     */
    protected function updateFormField($field)
    {
        // set the error / formatting messages
        $field->setCustomValidationMessage($this->getErrorMessage()->RAW());

        // set the right title on this field
        if ($this->RightTitle) {
            $field->setRightTitle($this->RightTitle);
        }

        // if this field is required add some
        if ($this->Required) {
            // Required validation can conflict so add the Required validation messages as input attributes
            $errorMessage = $this->getErrorMessage()->HTML();
            $field->addExtraClass('requiredField');
            $field->setAttribute('data-rule-required', 'true');
            $field->setAttribute('data-msg-required', $errorMessage);

            if ($identifier = UserDefinedForm::config()->required_identifier) {
                $title = $field->Title() . " <span class='required-identifier'>". $identifier . "</span>";
                $field->setTitle(DBField::create_field('HTMLText', $title));
            }
        }

        // if this field has an extra class
        if ($this->ExtraClass) {
            $field->addExtraClass($this->ExtraClass);
        }

        // if ShowOnLoad is false hide the field
        if (!$this->ShowOnLoad) {
            $field->addExtraClass($this->ShowOnLoadNice());
        }

        // if this field has a placeholder
        if (strlen($this->Placeholder ?? '') >= 0) {
            $field->setAttribute('placeholder', $this->Placeholder);
        }
    }

    /**
     * Return the instance of the submission field class
     *
     * @return SubmittedFormField
     */
    public function getSubmittedFormField()
    {
        return SubmittedFormField::create();
    }


    /**
     * Show this form field (and its related value) in the reports and in emails.
     *
     * @return bool
     */
    public function showInReports()
    {
        return true;
    }

    /**
     * Return the error message for this field. Either uses the custom
     * one (if provided) or the default SilverStripe message
     *
     * @return DBVarchar
     */
    public function getErrorMessage()
    {
        $title = strip_tags("'". ($this->Title ? $this->Title : $this->Name) . "'");
        $standard = _t(__CLASS__ . '.FIELDISREQUIRED', '{name} is required', ['name' => $title]);

        // only use CustomErrorMessage if it has a non empty value
        $errorMessage = (!empty($this->CustomErrorMessage)) ? $this->CustomErrorMessage : $standard;

        return DBField::create_field('Varchar', $errorMessage);
    }

    /**
     * Get the formfield to use when editing this inline in gridfield
     *
     * @param string $column name of column
     * @param array $fieldClasses List of allowed classnames if this formfield has a selectable class
     * @return FormField
     */
    public function getInlineClassnameField($column, $fieldClasses)
    {
        return DropdownField::create($column, false, $fieldClasses);
    }

    /**
     * Get the formfield to use when editing the title inline
     *
     * @param string $column
     * @return FormField
     */
    public function getInlineTitleField($column)
    {
        return TextField::create($column, false)
            ->setAttribute('placeholder', _t(__CLASS__.'.TITLE', 'Title'))
            ->setAttribute('data-placeholder', _t(__CLASS__.'.TITLE', 'Title'));
    }

    /**
     * Get the JS expression for selecting the holder for this field
     *
     * @return string
     */
    public function getSelectorHolder()
    {
        return sprintf('$("%s")', $this->getSelectorOnly());
    }

    /**
     * Returns only the JS identifier of a string, less the $(), which can be inserted elsewhere, for example when you
     * want to perform selections on multiple selectors
     * @return string
     */
    public function getSelectorOnly()
    {
        return "#{$this->Name}";
    }

    /**
     * Gets the JS expression for selecting the value for this field
     *
     * @param EditableCustomRule $rule Custom rule this selector will be used with
     * @param bool $forOnLoad Set to true if this will be invoked on load
     *
     * @return string
     */
    public function getSelectorField(EditableCustomRule $rule, $forOnLoad = false)
    {
        return sprintf("$(%s)", $this->getSelectorFieldOnly());
    }

    /**
     * @return string
     */
    public function getSelectorFieldOnly()
    {
        return "[name='{$this->Name}']";
    }


    /**
     * Get the list of classes that can be selected and used as data-values
     *
     * @param $includeLiterals Set to false to exclude non-data fields
     * @return array
     */
    public function getEditableFieldClasses($includeLiterals = true)
    {
        $classes = ClassInfo::getValidSubClasses(EditableFormField::class);

        // Remove classes we don't want to display in the dropdown.
        $editableFieldClasses = [];
        foreach ($classes as $class) {
            // Skip abstract / hidden classes
            if (Config::inst()->get($class, 'abstract', Config::UNINHERITED)
                || Config::inst()->get($class, 'hidden')
            ) {
                continue;
            }

            if (!$includeLiterals && Config::inst()->get($class, 'literal')) {
                continue;
            }

            $singleton = singleton($class);
            if (!$singleton->canCreate()) {
                continue;
            }

            $editableFieldClasses[$class] = $singleton->i18n_singular_name();
        }

        asort($editableFieldClasses);
        return $editableFieldClasses;
    }

    /**
     * @return EditableFormField\Validator
     */
    public function getCMSValidator()
    {
        return EditableFormField\Validator::create()
            ->setRecord($this);
    }

    /**
     * Extracts info from DisplayRules into array so UserDefinedForm->buildWatchJS can run through it.
     * @return array|null
     */
    public function formatDisplayRules()
    {
        $holderSelector = $this->getSelectorOnly();
        $result = [
            'targetFieldID' => $holderSelector,
            'conjunction'   => $this->DisplayRulesConjunctionNice(),
            'selectors'     => [],
            'events'        => [],
            'operations'    => [],
            'initialState'  => $this->ShowOnLoadNice(),
            'view'          => [],
            'opposite'      => [],
        ];

        // Check for field dependencies / default
        foreach ($this->DisplayRules() as $rule) {
            // Get the field which is effected
            $formFieldWatch = DataObject::get_by_id(EditableFormField::class, $rule->ConditionFieldID);
            // Skip deleted fields
            if (!$formFieldWatch) {
                continue;
            }

            $fieldToWatch = $formFieldWatch->getSelectorFieldOnly();

            $expression = $rule->buildExpression();
            if (!in_array($fieldToWatch, $result['selectors'] ?? [])) {
                $result['selectors'][] = $fieldToWatch;
            }
            if (!in_array($expression['event'], $result['events'] ?? [])) {
                $result['events'][] = $expression['event'];
            }
            $result['operations'][] = $expression['operation'];

            // View/Show should read
            $result['view'] = $rule->toggleDisplayText($result['initialState']);
            $result['opposite'] = $rule->toggleDisplayText($result['initialState'], true);
            $result['holder'] = $this->getSelectorHolder();
            $result['holder_event'] = $rule->toggleDisplayEvent($result['initialState']);
            $result['holder_event_opposite'] = $rule->toggleDisplayEvent($result['initialState'], true);
        }

        return (count($result['selectors'] ?? [])) ? $result : null;
    }

    /**
     * Used to prevent infinite recursion when checking a CMS user has setup two or more fields to have
     * their display rules dependent on one another
     *
     * There will be several thousand calls to isDisplayed before memory is likely to be hit, so 100
     * calls is a reasonable limit that ensures that this doesn't prevent legit use cases from being
     * identified as recursion
     */
    private function checkIsDisplayedRecursionProtection(): bool
    {
        $count = count(array_filter(static::$isDisplayedRecursionProtection, fn($id) => $id === $this->ID));
        return $count < 100;
    }

    /**
     * Check if this EditableFormField is displayed based on its DisplayRules and the provided data.
     * @param array $data
     * @return bool
     */
    public function isDisplayed(array $data)
    {
        static::$isDisplayedRecursionProtection[] = $this->ID;
        $displayRules = $this->DisplayRules();

        if ($displayRules->count() === 0) {
            // If no display rule have been defined, isDisplayed equals the ShowOnLoad property
            return $this->ShowOnLoad;
        }

        $conjunction = $this->DisplayRulesConjunctionNice();

        // && start with true and find and condition that doesn't satisfy
        // || start with false and find and condition that satisfies
        $conditionsSatisfied = ($conjunction === '&&');

        foreach ($displayRules as $rule) {
            $controllingField = $rule->ConditionField();

            // recursively check - if any of the dependant fields are hidden, assume the rule can not be satisfied
            $ruleSatisfied = $this->checkIsDisplayedRecursionProtection()
            && $controllingField->isDisplayed($data)
            && $rule->validateAgainstFormData($data);

            if ($conjunction === '||' && $ruleSatisfied) {
                $conditionsSatisfied = true;
                break;
            }
            if ($conjunction === '&&' && !$ruleSatisfied) {
                $conditionsSatisfied = false;
                break;
            }
        }

        // initially displayed - condition fails || initially hidden, condition passes
        $startDisplayed = $this->ShowOnLoad;
        return ($startDisplayed xor $conditionsSatisfied);
    }


    /**
     * Replaces the set DisplayRulesConjunction with their JS logical operators
     * @return string
     */
    public function DisplayRulesConjunctionNice()
    {
        return (strtolower($this->DisplayRulesConjunction ?? '') === 'or') ? '||' : '&&';
    }

    /**
     * Replaces boolean ShowOnLoad with its JS string equivalent
     * @return string
     */
    public function ShowOnLoadNice()
    {
        return ($this->ShowOnLoad) ? 'show' : 'hide';
    }

    /**
     * Returns whether this is of type EditableCheckBoxField
     * @return bool
     */
    public function isCheckBoxField()
    {
        return false;
    }

    /**
     * Returns whether this is of type EditableRadioField
     * @return bool
     */
    public function isRadioField()
    {
        return false;
    }

    /**
     * Determined is this is of type EditableCheckboxGroupField
     * @return bool
     */
    public function isCheckBoxGroupField()
    {
        return false;
    }
}
