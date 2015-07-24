<?php
/**
 * Represents the base class of a editable form field 
 * object like {@link EditableTextField}. 
 *
 * @package userforms
 */

class EditableFormField extends DataObject {

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
		"Default" => "Varchar",
		"Sort" => "Int",
		"Required" => "Boolean",
		"CustomErrorMessage" => "Varchar(255)",
		"CustomSettings" => "Text"
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
		"CustomRules" => "EditableCustomRule.Parent"
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
		$fields = $this->scaffoldFormFields(array(
			'tabbed' => true
		));

		$fields->removeByName('Sort');
		$fields->removeByName('Version');
		$fields->removeByName('ParentID');
		$fields->removeByName('CustomSettings');
		$fields->removeByName('Name');

		$fields->insertBefore(ReadonlyField::create(
			'Type',
			_t('EditableFormField.TYPE', 'Type'),
			$this->config()->get('singular_name')),
			'Title'
		);

		$fields->insertBefore(LiteralField::create(
			'MergeField',
			_t('EditableFormField.MERGEFIELDNAME',
			'<div class="field readonly">' .
				'<label class="left">Merge field</label>' .
				'<div class="middleColumn">' .
					'<span class="readonly">$' . $this->Name . '</span>' .
				'</div>' .
			'</div>')),
			'Title'
		);

		/*
		 * Validation
		 */
		$requiredCheckbox = $fields->fieldByName('Root')->fieldByName('Main')->fieldByName('Required');
		$customErrorMessage = $fields->fieldByName('Root')->fieldByName('Main')->fieldByName('CustomErrorMessage');

		$fields->removeFieldFromTab('Root.Main', 'Required');
		$fields->removeFieldFromTab('Root.Main', 'CustomErrorMessage');

		$fields->addFieldToTab('Root.Validation', $requiredCheckbox);
		$fields->addFieldToTab('Root.Validation', $customErrorMessage);

		/*
		 * Custom rules
		 */

		$customRulesConfig = GridFieldConfig::create()
			->addComponents(
				(new GridFieldEditableColumns())
				->setDisplayFields(array(
					'Display' => '',
					'ConditionFieldID' => function($record, $column, $grid) {
						return DropdownField::create(
							$column,
							'',
							EditableFormField::get()
								->filter(array(
									'ParentID' => $this->ParentID
								))
								->exclude(array(
									'ID' => $this->ID
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
					'ParentID' => function($record, $column, $grid) {
						return HiddenField::create($column, '', $this->ID);
					}
				)),
				new GridFieldButtonRow(),
				new GridFieldToolbarHeader(),
				new GridFieldAddNewInlineButton(),
				new GridFieldDeleteAction(),
				new GridState_Component()
			);


		$customRulesGrid = GridField::create(
			'CustomRules',
			_t('EditableFormField.CUSTOMRULES', 'Custom Rules'),
			$this->CustomRules(),
			$customRulesConfig
		);

		$fields->addFieldToTab('Root.CustomRules', $customRulesGrid);

		/*
		 * Custom settings
		 */
		$extraClass = ($this->getSetting('ExtraClass')) ? $this->getSetting('ExtraClass') : '';

		if (is_array(self::$allowed_css) && !empty(self::$allowed_css)) {
			foreach(self::$allowed_css as $k => $v) {
				if (!is_array($v)) $cssList[$k]=$v;
				elseif ($k == $this->ClassName()) $cssList = array_merge($cssList, $v);
			}

			$fields->addFieldToTab('Root.Main',
				new DropdownField(
					$this->getSettingName('ExtraClass'), 
					_t('EditableFormField.EXTRACLASSA', 'Extra Styling/Layout'), 
					$cssList,
					$extraClass
				)
			);
		} else {
			$fields->addFieldToTab('Root.Main',
				new TextField(
					$this->getSettingName('ExtraClass'), 
					_t('EditableFormField.EXTRACLASSB', 'Extra CSS class - separate multiples with a space'), 
					$extraClass
				)
			);
		}

		$fields->addFieldToTab('Root.Main',
			new TextField(
				$this->getSettingName('RightTitle'), 
				_t('EditableFormField.RIGHTTITLE', 'Right Title'), 
				$this->getSetting('RightTitle')
			)
		);

		$this->extend('updateCMSFields', $fields);

		return $fields;
	}

	/**
	 * @return void
	 */
	public function onBeforeWrite() {
		parent::onBeforeWrite();

		// Save custom settings.
		$fields = $this->toMap();
		$settings = $this->getSettings();

		foreach($fields as $field => $value) {
			if(preg_match("/\[CustomSettings\]\[((\w)+)\]$/", $field, $matches)) {
				$settings[$matches[1]] = $value;
			}
		}

		$this->setSettings($settings);

		if(!isset($this->Sort)) {
			$parentID = ($this->ParentID) ? $this->ParentID : 0;

			$this->Sort = EditableFormField::get()->filter('ParentID', $parentID)->max('Sort') + 1;
		}
	}

	/**
	 * @return void
	 */
	public function onAfterWrite() {
		parent::onAfterWrite();

		// Set a field name.
		if(!$this->Name) {
			$this->Name = $this->RecordClassName . $this->ID;
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
		foreach ($this->CustomRules() as $rule) {
			$rule->doPublish($fromStage, $toStage, $createNewVersion);
		}
	}
	
	/**
	 * Delete this form from a given stage
	 *
	 * Wrapper for the {@link Versioned} deleteFromStage function
	 */
	public function doDeleteFromStage($stage) {
		$this->deleteFromStage($stage);

		// Don't forget to delete the related custom rules...
		foreach ($this->CustomRules() as $rule) {
			$rule->deleteFromStage($stage);
		}
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
	 * Show this form on load or not
	 *
	 * @return bool
	 */
	public function getShowOnLoad() {
		return ($this->getSetting('ShowOnLoad') == "Show" || $this->getSetting('ShowOnLoad') == '') ? true : false;
	}
	
	/**
	 * To prevent having tables for each fields minor settings we store it as 
	 * a serialized array in the database. 
	 * 
	 * @return Array Return all the Settings
	 */
	public function getSettings() {
		return (!empty($this->CustomSettings)) ? unserialize($this->CustomSettings) : array();
	}
	
	/**
	 * Set the custom settings for this field as we store the minor details in
	 * a serialized array in the database
	 *
	 * @param Array the custom settings
	 */
	public function setSettings($settings = array()) {
		$this->CustomSettings = serialize($settings);
	}
	
	/**
	 * Set a given field setting. Appends the option to the settings or overrides
	 * the existing value
	 *
	 * @param String key 
	 * @param String value
	 */
	public function setSetting($key, $value) {
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
	 * Return just one custom setting or empty string if it does
	 * not exist
	 *
	 * @param String Value to use as key
	 * @return String
	 */
	public function getSetting($setting) {
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
	 * Title field of the field in the backend of the page
	 *
	 * @return TextField
	 */
	public function TitleField() {
		$label = _t('EditableFormField.ENTERQUESTION', 'Enter Question');
		
		$field = new TextField('Title', $label, $this->getField('Title'));
		$field->setName($this->getFieldName('Title'));

		if(!$this->canEdit()) {
			return $field->performReadonlyTransformation();
		}

		return $field;
	}

	/** Returns the Title for rendering in the front-end (with XML values escaped) */
	public function getTitle() {
		return Convert::raw2att($this->getField('Title'));
	}

	/**
	 * Return the base name for this form field in the 
	 * form builder. Optionally returns the name with the given field
	 *
	 * @param String Field Name
	 *
	 * @return String
	 */
	public function getFieldName($field = false) {
		return ($field) ? "Fields[".$this->ID."][".$field."]" : "Fields[".$this->ID."]";
	}
	
	/**
	 * Generate a name for the Setting field
	 *
	 * @param String name of the setting
	 * @return String
	 */
	public function getSettingName($field) {
		$name = $this->getFieldName('CustomSettings');
		
		return $name . '[' . $field .']';
	}
	
	/**
	 * Append custom validation fields to the default 'Validation' 
	 * section in the editable options view
	 * 
	 * @return FieldSet
	 */
	public function getFieldValidationOptions() {
		$fields = new FieldList(
			new CheckboxField($this->getFieldName('Required'), _t('EditableFormField.REQUIRED', 'Is this field Required?'), $this->Required),
			new TextField($this->getFieldName('CustomErrorMessage'), _t('EditableFormField.CUSTOMERROR','Custom Error Message'), $this->CustomErrorMessage)
		);
		
		if(!$this->canEdit()) {
			foreach($fields as $field) {
				$field->performReadonlyTransformation();
			}
		}

        $this->extend('updateFieldValidationOptions', $fields);
		
		return $fields;
	}
	
	/**
	 * Return a FormField to appear on the front end. Implement on 
	 * your subclass
	 *
	 * @return FormField
	 */
	public function getFormField() {
		user_error("Please implement a getFormField() on your EditableFormClass ". $this->ClassName, E_USER_ERROR);
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
	 * Return the validation information related to this field. This is 
	 * interrupted as a JSON object for validate plugin and used in the 
	 * PHP. 
	 *
	 * @see http://docs.jquery.com/Plugins/Validation/Methods
	 * @return Array
	 */
	public function getValidation() {
		return $this->Required
			? array('required' => true)
			: array();
	}
	
	public function getValidationJSON() {
		return Convert::raw2json($this->getValidation());
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
		if($this->Required && $this->CustomRules()->Count() == 0) {
			$formField = $this->getFormField();

			if(isset($data[$this->Name])) {
				$formField->setValue($data[$this->Name]);
			}

			if(
				!isset($data[$this->Name]) || 
				!$data[$this->Name] ||
				!$formField->validate($form->getValidator())
			) {
				$form->addErrorMessage($this->Name, $this->getErrorMessage(), 'bad');
			}
		}

		return true;
	}
}
