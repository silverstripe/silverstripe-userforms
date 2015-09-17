<?php

use SilverStripe\Forms\SegmentField;

/**
 * Represents the base class of a editable form field
 * object like {@link EditableTextField}.
 *
 * @package userforms
 *
 * @property string Name
 *
 * @method DataList DisplayRules() List of EditableCustomRule objects
 */
class EditableFormField extends DataObject {

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
	public static $allowed_css = array();

	/**
	 * @config
	 * @var array
	 */
	private static $summary_fields = array(
		'Title'
	);

	/**
	 * @config
	 * @var array
	 */
	private static $db = array(
		"Name" => "Varchar",
		"Title" => "Varchar(255)",
		"Default" => "Varchar(255)",
		"Sort" => "Int",
		"Required" => "Boolean",
		"CustomErrorMessage" => "Varchar(255)",

		"CustomRules" => "Text", // @deprecated from 2.0
		"CustomSettings" => "Text", // @deprecated from 2.0
		"Migrated" => "Boolean", // set to true when migrated

		"ExtraClass" => "Text", // from CustomSettings
		"RightTitle" => "Varchar(255)", // from CustomSettings
		"ShowOnLoad" => "Boolean(1)", // from CustomSettings
	);

	/**
	 * @config
	 * @var array
	 */
	private static $has_one = array(
		"Parent" => "UserDefinedForm",
	);

	/**
	 * Built in extensions required
	 *
	 * @config
	 * @var array
	 */
	private static $extensions = array(
		"Versioned('Stage', 'Live')"
	);

	/**
	 * @config
	 * @var array
	 */
	private static $has_many = array(
		"DisplayRules" => "EditableCustomRule.Parent" // from CustomRules
	);

	/**
	 * @var bool
	 */
	protected $readonly;

	/**
	 * Set the visibility of an individual form field
	 *
	 * @param bool
	 */
	public function setReadonly($readonly = true) {
		$this->readonly = $readonly;
	}

	/**
	 * Returns whether this field is readonly
	 *
	 * @return bool
	 */
	private function isReadonly() {
		return $this->readonly;
	}

	/**
	 * @return FieldList
	 */
	public function getCMSFields() {
		$fields = new FieldList(new TabSet('Root'));

		// Main tab
		$fields->addFieldsToTab(
			'Root.Main',
			array(
				ReadonlyField::create(
					'Type',
					_t('EditableFormField.TYPE', 'Type'),
					$this->i18n_singular_name()
				),
				LiteralField::create(
					'MergeField',
					_t(
						'EditableFormField.MERGEFIELDNAME',
						'<div class="field readonly">' .
							'<label class="left">Merge field</label>' .
							'<div class="middleColumn">' .
								'<span class="readonly">$' . $this->Name . '</span>' .
							'</div>' .
						'</div>'
					)
				),
				TextField::create('Title'),
				TextField::create('Default', _t('EditableFormField.DEFAULT', 'Default value')),
				TextField::create('RightTitle', _t('EditableFormField.RIGHTTITLE', 'Right title')),
				SegmentField::create('Name')->setModifiers(array(
					UnderscoreSegmentFieldModifier::create()->setDefault('FieldName'),
					DisambiguationSegmentFieldModifier::create(),
				))->setPreview($this->Name)
			)
		);

		// Custom settings
		if (!empty(self::$allowed_css)) {
			$cssList = array();
			foreach(self::$allowed_css as $k => $v) {
				if (!is_array($v)) {
					$cssList[$k]=$v;
				} elseif ($k === $this->ClassName) {
					$cssList = array_merge($cssList, $v);
				}
			}

			$fields->addFieldToTab('Root.Main',
				DropdownField::create(
					'ExtraClass',
					_t('EditableFormField.EXTRACLASS_TITLE', 'Extra Styling/Layout'),
					$cssList
				)->setDescription(_t(
					'EditableFormField.EXTRACLASS_SELECT',
					'Select from the list of allowed styles'
				))
			);
		} else {
			$fields->addFieldToTab('Root.Main',
				TextField::create(
					'ExtraClass',
					_t('EditableFormField.EXTRACLASS_Title', 'Extra CSS classes')
				)->setDescription(_t(
					'EditableFormField.EXTRACLASS_MULTIPLE',
					'Separate each CSS class with a single space'
				))
			);
		}

		// Validation
		$validationFields = $this->getFieldValidationOptions();
		if($validationFields) {
			$fields->addFieldsToTab(
				'Root.Validation',
				$this->getFieldValidationOptions()
			);
		}
		$allowedClasses = array_keys($this->getEditableFieldClasses(false));
		$self = $this;
		$editableColumns = new GridFieldEditableColumns();
		$editableColumns->setDisplayFields(array(
			'Display' => '',
			'ConditionFieldID' => function($record, $column, $grid) use ($allowedClasses, $self) {
				return DropdownField::create(
					$column,
					'',
					EditableFormField::get()
						->filter(array(
							'ParentID' => $self->ParentID,
							'ClassName' => $allowedClasses
						))
						->exclude(array(
							'ID' => $self->ID
						))
						->map('ID', 'Title')
					);
			},
			'ConditionOption' => function($record, $column, $grid) {
				$options = Config::inst()->get('EditableCustomRule', 'condition_options');
				return DropdownField::create($column, '', $options);
			},
			'FieldValue' => function($record, $column, $grid) {
				return TextField::create($column);
			},
			'ParentID' => function($record, $column, $grid) use ($self) {
				return HiddenField::create($column, '', $self->ID);
			}
		));

		// Custom rules
		$customRulesConfig = GridFieldConfig::create()
			->addComponents(
				$editableColumns,
				new GridFieldButtonRow(),
				new GridFieldToolbarHeader(),
				new GridFieldAddNewInlineButton(),
				new GridFieldDeleteAction()
			);

		$fields->addFieldsToTab('Root.DisplayRules', array(
			CheckboxField::create('ShowOnLoad')
				->setDescription(_t(
					'EditableFormField.SHOWONLOAD',
					'Initial visibility before processing these rules'
				)),
			GridField::create(
				'DisplayRules',
				_t('EditableFormField.CUSTOMRULES', 'Custom Rules'),
				$this->DisplayRules(),
				$customRulesConfig
			)
		));

		$this->extend('updateCMSFields', $fields);

		return $fields;
	}

	/**
	 * @return void
	 */
	public function onBeforeWrite() {
		parent::onBeforeWrite();

		if($this->Name === 'Field') {
			throw new ValidationException('Field name cannot be "Field"');
		}

		if(!$this->Sort && $this->ParentID) {
			$parentID = $this->ParentID;
			$this->Sort = EditableFormField::get()
				->filter('ParentID', $parentID)
				->max('Sort') + 1;
		}
	}

	/**
	 * @return void
	 */
	public function onAfterWrite() {
		parent::onAfterWrite();

		// Set a field name.
		if(!$this->Name) {
			$this->Name = get_class($this) . '_' . $this->ID;
			$this->write();
		}
	}

	/**
	 * Flag indicating that this field will set its own error message via data-msg='' attributes
	 *
	 * @return bool
	 */
	public function getSetsOwnError() {
		return false;
	}

	/**
	 * Return whether a user can delete this form field
	 * based on whether they can edit the page
	 *
	 * @return bool
	 */
	public function canDelete($member = null) {
		if($this->Parent()) {
			return $this->Parent()->canEdit($member) && !$this->isReadonly();
		}

		return true;
	}

	/**
	 * Return whether a user can edit this form field
	 * based on whether they can edit the page
	 *
	 * @return bool
	 */
	public function canEdit($member = null) {
		if($this->Parent()) {
			return $this->Parent()->canEdit($member) && !$this->isReadonly();
		}

		return true;
	}

	/**
	 * Publish this Form Field to the live site
	 *
	 * Wrapper for the {@link Versioned} publish function
	 */
	public function doPublish($fromStage, $toStage, $createNewVersion = false) {
		$this->publish($fromStage, $toStage, $createNewVersion);

		// Don't forget to publish the related custom rules...
		foreach ($this->DisplayRules() as $rule) {
			$rule->doPublish($fromStage, $toStage, $createNewVersion);
		}
	}

	/**
	 * Delete this field from a given stage
	 *
	 * Wrapper for the {@link Versioned} deleteFromStage function
	 */
	public function doDeleteFromStage($stage) {
		// Remove custom rules in this stage
		$rules = Versioned::get_by_stage('EditableCustomRule', $stage)
			->filter('ParentID', $this->ID);
		foreach ($rules as $rule) {
			$rule->deleteFromStage($stage);
		}

		// Remove record
		$this->deleteFromStage($stage);
	}

	/**
	 * checks wether record is new, copied from Sitetree
	 */
	function isNew() {
		if(empty($this->ID)) return true;

		if(is_numeric($this->ID)) return false;

		return stripos($this->ID, 'new') === 0;
	}

	/**
	 * checks if records is changed on stage
	 * @return boolean
	 */
	public function getIsModifiedOnStage() {
		// new unsaved fields could be never be published
		if($this->isNew()) return false;

		$stageVersion = Versioned::get_versionnumber_by_stage('EditableFormField', 'Stage', $this->ID);
		$liveVersion = Versioned::get_versionnumber_by_stage('EditableFormField', 'Live', $this->ID);

		return ($stageVersion && $stageVersion != $liveVersion);
	}

	/**
	 * @deprecated since version 4.0
	 */
	public function getSettings() {
		Deprecation::notice('4.0', 'getSettings is deprecated');
		return (!empty($this->CustomSettings)) ? unserialize($this->CustomSettings) : array();
	}

	/**
	 * @deprecated since version 4.0
	 */
	public function setSettings($settings = array()) {
		Deprecation::notice('4.0', 'setSettings is deprecated');
		$this->CustomSettings = serialize($settings);
	}

	/**
	 * @deprecated since version 4.0
	 */
	public function setSetting($key, $value) {
		Deprecation::notice('4.0', "setSetting({$key}) is deprecated");
		$settings = $this->getSettings();
		$settings[$key] = $value;

		$this->setSettings($settings);
	}

	/**
	 * Set the allowed css classes for the extraClass custom setting
	 *
	 * @param array The permissible CSS classes to add
	 */
	public function setAllowedCss(array $allowed) {
		if (is_array($allowed)) {
			foreach ($allowed as $k => $v) {
				self::$allowed_css[$k] = (!is_null($v)) ? $v : $k;
			}
		}
	}

	/**
	 * @deprecated since version 4.0
	 */
	public function getSetting($setting) {
		Deprecation::notice("4.0", "getSetting({$setting}) is deprecated");

		$settings = $this->getSettings();
		if(isset($settings) && count($settings) > 0) {
			if(isset($settings[$setting])) {
				return $settings[$setting];
			}
		}
		return '';
	}

	/**
	 * Get the path to the icon for this field type, relative to the site root.
	 *
	 * @return string
	 */
	public function getIcon() {
		return USERFORMS_DIR . '/images/' . strtolower($this->class) . '.png';
	}

	/**
	 * Return whether or not this field has addable options
	 * such as a dropdown field or radio set
	 *
	 * @return bool
	 */
	public function getHasAddableOptions() {
		return false;
	}

	/**
	 * Return whether or not this field needs to show the extra
	 * options dropdown list
	 *
	 * @return bool
	 */
	public function showExtraOptions() {
		return true;
	}

	/**
	 * Returns the Title for rendering in the front-end (with XML values escaped)
	 *
	 * @return string
	 */
	public function getEscapedTitle() {
		return Convert::raw2xml($this->Title);
	}

	/**
	 * Find the numeric indicator (1.1.2) that represents it's nesting value
	 *
	 * Only useful for fields attached to a current page, and that contain other fields such as pages
	 * or groups
	 *
	 * @return string
	 */
	public function getFieldNumber() {
		// Check if exists
		if(!$this->exists()) {
			return null;
		}
		// Check parent
		$form = $this->Parent();
		if(!$form || !$form->exists() || !($fields = $form->Fields())) {
			return null;
		}

		$prior = 0; // Number of prior group at this level
		$stack = array(); // Current stack of nested groups, where the top level = the page
		foreach($fields->map('ID', 'ClassName') as $id => $className) {
			if($className === 'EditableFormStep') {
				$priorPage = empty($stack) ? $prior : $stack[0];
				$stack = array($priorPage + 1);
				$prior = 0;
			} elseif($className === 'EditableFieldGroup') {
				$stack[] = $prior + 1;
				$prior = 0;
			} elseif($className === 'EditableFieldGroupEnd') {
				$prior = array_pop($stack);
			}
			if($id == $this->ID) {
				return implode('.', $stack);
			}
		}
		return null;
	}

	public function getCMSTitle() {
		return $this->i18n_singular_name() . ' (' . $this->Title . ')';
	}

	/**
	 * @deprecated since version 4.0
	 */
	public function getFieldName($field = false) {
		Deprecation::notice('4.0', "getFieldName({$field}) is deprecated");
		return ($field) ? "Fields[".$this->ID."][".$field."]" : "Fields[".$this->ID."]";
	}

	/**
	 * @deprecated since version 4.0
	 */
	public function getSettingName($field) {
		Deprecation::notice('4.0', "getSettingName({$field}) is deprecated");
		$name = $this->getFieldName('CustomSettings');

		return $name . '[' . $field .']';
	}

	/**
	 * Append custom validation fields to the default 'Validation'
	 * section in the editable options view
	 *
	 * @return FieldList
	 */
	public function getFieldValidationOptions() {
		$fields = new FieldList(
			CheckboxField::create('Required', _t('EditableFormField.REQUIRED', 'Is this field Required?'))
				->setDescription(_t('EditableFormField.REQUIRED_DESCRIPTION', 'Please note that conditional fields can\'t be required')),
			TextField::create('CustomErrorMessage', _t('EditableFormField.CUSTOMERROR','Custom Error Message'))
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
	public function getFormField() {
		user_error("Please implement a getFormField() on your EditableFormClass ". $this->ClassName, E_USER_ERROR);
	}

	/**
	 * Updates a formfield with extensions
	 *
	 * @param FormField $field
	 */
	public function doUpdateFormField($field) {
		$this->extend('beforeUpdateFormField', $field);
		$this->updateFormField($field);
		$this->extend('afterUpdateFormField', $field);
	}

	/**
	 * Updates a formfield with the additional metadata specified by this field
	 *
	 * @param FormField $field
	 */
	protected function updateFormField($field) {
		// set the error / formatting messages
		$field->setCustomValidationMessage($this->getErrorMessage());

		// set the right title on this field
		if($this->RightTitle) {
			// Since this field expects raw html, safely escape the user data prior
			$field->setRightTitle(Convert::raw2xml($this->RightTitle));
		}

		// if this field is required add some
		if($this->Required) {
			// Required validation can conflict so add the Required validation messages as input attributes
			$errorMessage = $this->getErrorMessage()->HTML();
			$field->addExtraClass('requiredField');
			$field->setAttribute('data-rule-required', 'true');
			$field->setAttribute('data-msg-required', $errorMessage);

			if($identifier = UserDefinedForm::config()->required_identifier) {
				$title = $field->Title() . " <span class='required-identifier'>". $identifier . "</span>";
				$field->setTitle($title);
			}
		}

		// if this field has an extra class
		if($this->ExtraClass) {
			$field->addExtraClass($this->ExtraClass);
		}
	}

	/**
	 * Return the instance of the submission field class
	 *
	 * @return SubmittedFormField
	 */
	public function getSubmittedFormField() {
		return new SubmittedFormField();
	}


	/**
	 * Show this form field (and its related value) in the reports and in emails.
	 *
	 * @return bool
	 */
	public function showInReports() {
		return true;
	}

	/**
	 * Return the error message for this field. Either uses the custom
	 * one (if provided) or the default SilverStripe message
	 *
	 * @return Varchar
	 */
	public function getErrorMessage() {
		$title = strip_tags("'". ($this->Title ? $this->Title : $this->Name) . "'");
		$standard = sprintf(_t('Form.FIELDISREQUIRED', '%s is required').'.', $title);

		// only use CustomErrorMessage if it has a non empty value
		$errorMessage = (!empty($this->CustomErrorMessage)) ? $this->CustomErrorMessage : $standard;

		return DBField::create_field('Varchar', $errorMessage);
	}

	/**
	 * Validate the field taking into account its custom rules.
	 *
	 * @param Array $data
	 * @param UserForm $form
	 *
	 * @return boolean
	 */
	public function validateField($data, $form) {
		if($this->Required && $this->DisplayRules()->Count() == 0) {
			$formField = $this->getFormField();

			if(isset($data[$this->Name])) {
				$formField->setValue($data[$this->Name]);
			}

			if(
				!isset($data[$this->Name]) ||
				!$data[$this->Name] ||
				!$formField->validate($form->getValidator())
			) {
				$form->addErrorMessage($this->Name, $this->getErrorMessage()->HTML(), 'error', false);
			}
		}

		return true;
	}

	/**
	 * Invoked by UserFormUpgradeService to migrate settings specific to this field from CustomSettings
	 * to the field proper
	 *
	 * @param array $data Unserialised data
	 */
	public function migrateSettings($data) {
		// Map 'Show' / 'Hide' to boolean
		if(isset($data['ShowOnLoad'])) {
			$this->ShowOnLoad = $data['ShowOnLoad'] === '' || ($data['ShowOnLoad'] && $data['ShowOnLoad'] !== 'Hide');
			unset($data['ShowOnLoad']);
		}

		// Migrate all other settings
		foreach($data as $key => $value) {
			if($this->hasField($key)) {
				$this->setField($key, $value);
			}
		}
	}

	/**
	 * Get the formfield to use when editing this inline in gridfield
	 *
	 * @param string $column name of column
	 * @param array $fieldClasses List of allowed classnames if this formfield has a selectable class
	 * @return FormField
	 */
	public function getInlineClassnameField($column, $fieldClasses) {
		return DropdownField::create($column, false, $fieldClasses);
	}

	/**
	 * Get the formfield to use when editing the title inline
	 *
	 * @param string $column
	 * @return FormField
	 */
	public function getInlineTitleField($column) {
		return TextField::create($column, false)
			->setAttribute('placeholder', _t('EditableFormField.TITLE', 'Title'))
			->setAttribute('data-placeholder', _t('EditableFormField.TITLE', 'Title'));
	}

	/**
	 * Get the JS expression for selecting the holder for this field
	 *
	 * @return string
	 */
	public function getSelectorHolder() {
		return "$(\"#{$this->Name}\")";
	}

	/**
	 * Gets the JS expression for selecting the value for this field
	 *
	 * @param EditableCustomRule $rule Custom rule this selector will be used with
	 * @param bool $forOnLoad Set to true if this will be invoked on load
	 */
	public function getSelectorField(EditableCustomRule $rule, $forOnLoad = false) {
		return "$(\"input[name='{$this->Name}']\")";
	}


	/**
	 * Get the list of classes that can be selected and used as data-values
	 *
	 * @param $includeLiterals Set to false to exclude non-data fields
	 * @return array
	 */
	public function getEditableFieldClasses($includeLiterals = true) {
		$classes = ClassInfo::getValidSubClasses('EditableFormField');

		// Remove classes we don't want to display in the dropdown.
		$editableFieldClasses = array();
		foreach ($classes as $class) {
			// Skip abstract / hidden classes
			if(Config::inst()->get($class, 'abstract', Config::UNINHERITED) || Config::inst()->get($class, 'hidden')
			) {
				continue;
			}

			if(!$includeLiterals && Config::inst()->get($class, 'literal')) {
				continue;
			}

			$singleton = singleton($class);
			if(!$singleton->canCreate()) {
				continue;
			}

			$editableFieldClasses[$class] = $singleton->i18n_singular_name();
		}

		asort($editableFieldClasses);
		return $editableFieldClasses;
	}

	/**
	 * @return EditableFormFieldValidator
	 */
	public function getCMSValidator() {
		return EditableFormFieldValidator::create()
			->setRecord($this);
	}
}
