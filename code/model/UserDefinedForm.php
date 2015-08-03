<?php

/**
 * @package userforms
 */

class UserDefinedForm extends Page {
	
	/**
	 * @var string
	 */
	private static $description = 'Adds a customizable form.';

	/**
	 * @var string Required Identifier
	 */
	private static $required_identifier = null;

	/**
	 * Prevent translatable module from attepmting to translate FieldEditor
	 *
	 * @var array
	 * @config
	 */
	private static $translate_excluded_fields = array(
		'Fields'
	);

	/**
	 * @var string
	 */
	private static $email_template_directory = 'userforms/templates/email/';
	
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
		"Fields" => "EditableFormField",
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
	 * Temporary storage of field ids when the form is duplicated.
	 * Example layout: array('EditableCheckbox3' => 'EditableCheckbox14')
	 * @var array
	 */
	protected $fieldsFromTo = array();

	/**
	 * @return FieldList
	 */
	 public function getCMSFields() {
		
		$self = $this;
		
		$this->beforeUpdateCMSFields(function($fields) use ($self) {
			
			// define tabs
			$fields->findOrMakeTab('Root.FormContent', _t('UserDefinedForm.FORM', 'Form'));
			$fields->findOrMakeTab('Root.FormOptions', _t('UserDefinedForm.CONFIGURATION', 'Configuration'));
			$fields->findOrMakeTab('Root.Recipients', _t('UserDefinedForm.RECIPIENTS', 'Recipients'));
			$fields->findOrMakeTab('Root.Submissions', _t('UserDefinedForm.SUBMISSIONS', 'Submissions'));
			
			// field editor
			$fields->addFieldToTab('Root.FormContent', new FieldEditor('Fields', 'Fields', '', $self ));
			
			// text to show on complete
			$onCompleteFieldSet = new CompositeField(
				$label = new LabelField('OnCompleteMessageLabel',_t('UserDefinedForm.ONCOMPLETELABEL', 'Show on completion')),
				$editor = new HtmlEditorField( 'OnCompleteMessage', '', _t('UserDefinedForm.ONCOMPLETEMESSAGE', $self->OnCompleteMessage))
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
				->setItemRequestClass('UserDefinedForm_EmailRecipient_ItemRequest');
			
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
			$parentID = (!empty($self->ID)) ? $self->ID : 0;
			
			// get a list of all field names and values used for print and export CSV views of the GridField below.
			$columnSQL = <<<SQL
SELECT "Name", "Title"
FROM "SubmittedFormField"
LEFT JOIN "SubmittedForm" ON "SubmittedForm"."ID" = "SubmittedFormField"."ParentID"
WHERE "SubmittedForm"."ParentID" = '$parentID'
ORDER BY "Title" ASC
SQL;
			$columns = DB::query($columnSQL)->map();
			
			$config = new GridFieldConfig();
			$config->addComponent(new GridFieldToolbarHeader());
			$config->addComponent($sort = new GridFieldSortableHeader());
			$config->addComponent($filter = new UserFormsGridFieldFilterHeader());
			$config->addComponent(new GridFieldDataColumns());
			$config->addComponent(new GridFieldEditButton());
			$config->addComponent(new GridState_Component());
			$config->addComponent(new GridFieldDeleteAction());
			$config->addComponent(new GridFieldPageCount('toolbar-header-right'));
			$config->addComponent($pagination = new GridFieldPaginator(25));
			$config->addComponent(new GridFieldDetailForm());
			$config->addComponent($export = new GridFieldExportButton());
			$config->addComponent($print = new GridFieldPrintButton());
			
			/**
			 * Support for {@link https://github.com/colymba/GridFieldBulkEditingTools}
			 */
			if(class_exists('GridFieldBulkManager')) {
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
		
		return $fields;
	}
	
	
	/**
	 * When publishing copy the editable form fields to the live database
	 * Not going to version emails and submissions as they are likely to 
	 * persist over multiple versions.
	 *
	 * @return void
	 */
	public function doPublish() {
		$parentID = (!empty($this->ID)) ? $this->ID : 0;
		// remove fields on the live table which could have been orphaned.
		$live = Versioned::get_by_stage("EditableFormField", "Live", "\"EditableFormField\".\"ParentID\" = $parentID");

		if($live) {
			foreach($live as $field) {
				$field->doDeleteFromStage('Live');
			}
		}

		// publish the draft pages
		if($this->Fields()) {
			foreach($this->Fields() as $field) {
				$field->doPublish('Stage', 'Live');
			}
		}

		parent::doPublish();
	}
	
	/**
	 * When un-publishing the page it has to remove all the fields from the 
	 * live database table.
	 *
	 * @return void
	 */
	public function doUnpublish() {
		if($this->Fields()) {
			foreach($this->Fields() as $field) {
				$field->doDeleteFromStage('Live');
			}
		}
		
		parent::doUnpublish();
	}
	
	/**
	 * Roll back a form to a previous version.
	 *
	 * @param string|int Version to roll back to
	 */
	public function doRollbackTo($version) {
		parent::doRollbackTo($version);
		
		/*
			Not implemented yet 
	
		// get the older version
		$reverted = Versioned::get_version($this->ClassName, $this->ID, $version);
		
		if($reverted) {
			
			// using the lastedited date of the reverted object we can work out which
			// form fields to revert back to
			if($this->Fields()) {
				foreach($this->Fields() as $field) {
					// query to see when the version of the page was pumped
					$editedDate = DB::query("
						SELECT LastEdited
						FROM \"SiteTree_versions\"
						WHERE \"RecordID\" = '$this->ID' AND \"Version\" = $version
					")->value(); 
					

					// find a the latest version which has been edited
					$versionToGet = DB::query("
						SELECT *
						FROM \"EditableFormField_versions\" 
						WHERE \"RecordID\" = '$field->ID' AND \"LastEdited\" <= '$editedDate'
						ORDER BY Version DESC
						LIMIT 1
					")->record();

					if($versionToGet) {
						Debug::show('publishing field'. $field->Name);
						Debug::show($versionToGet);
						$field->publish($versionToGet, "Stage", true);
						$field->writeWithoutVersion();
					}
					else {
						Debug::show('deleting field'. $field->Name);
						$this->Fields()->remove($field);
						
						$field->delete();
						$field->destroy();
					}
				}
			}
			
			// @todo Emails
		}
		*/
	}
	
	/**
	 * Revert the draft site to the current live site
	 *
	 * @return void
	 */
	public function doRevertToLive() {
		if($this->Fields()) {
			foreach($this->Fields() as $field) {
				$field->publish("Live", "Stage", false);
				$field->writeWithoutVersion();
			}
		}
		
		parent::doRevertToLive();
	}

	/**
	 * Allow overriding the EmailRecipients on a {@link DataExtension}
	 * so you can customise who receives an email.
	 * Converts the RelationList to an ArrayList so that manipulation
	 * of the original source data isn't possible.
	 *
	 * @return ArrayList
	 */
	public function FilteredEmailRecipients($data = null, $form = null) {
		$recipients = new ArrayList($this->EmailRecipients()->toArray());

		// Filter by rules
		$recipients = $recipients->filterByCallback(function($recipient) use ($data, $form) {
			return $recipient->canSend($data, $form);
		});

		$this->extend('updateFilteredEmailRecipients', $recipients, $data, $form);

		return $recipients;
	}


	/**
	 * Store new and old ids of duplicated fields.
	 * This method also serves as a hook for descendant classes.
	 */
	protected function afterDuplicateField($page, $fromField, $toField) {
		$this->fieldsFromTo[$fromField->ClassName . $fromField->ID] = $toField->ClassName . $toField->ID;
	}


	/**
	 * Duplicate this UserDefinedForm page, and its form fields.
	 * Submissions, on the other hand, won't be duplicated.
	 *
	 * @return Page
	 */
	public function duplicate($doWrite = true) {
		$page = parent::duplicate($doWrite);
		
		// the form fields
		if($this->Fields()) {
			foreach($this->Fields() as $field) {
				$newField = $field->duplicate();
				$newField->ParentID = $page->ID;
				$newField->write();
				$this->afterDuplicateField($page, $field, $newField);
			}
		}
		
		// the emails
		if($this->EmailRecipients()) {
			foreach($this->EmailRecipients() as $email) {
				$newEmail = $email->duplicate();
				$newEmail->FormID = $page->ID;
				$newEmail->write();
			}
		}
		
		// Rewrite CustomRules
		if($page->Fields()) {
			foreach($page->Fields() as $field) {
				// Rewrite name to make the CustomRules-rewrite below work.
				$field->Name = $field->ClassName . $field->ID;
				$rules = unserialize($field->CustomRules);

				if (count($rules) && isset($rules[0]['ConditionField'])) {
					$from = $rules[0]['ConditionField'];

					if (array_key_exists($from, $this->fieldsFromTo)) {
						$rules[0]['ConditionField'] = $this->fieldsFromTo[$from];
						$field->CustomRules = serialize($rules);
					}
				}

				$field->Write();
			}
		}

		return $page;
	}

	/**
	 * Custom options for the form. You can extend the built in options by 
	 * using {@link updateFormOptions()}
	 *
	 * @return FieldList
	 */
	public function getFormOptions() {
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
			new CheckboxField('DisableAuthenicatedFinishAction', _t('UserDefinedForm.DISABLEAUTHENICATEDFINISHACTION', 'Disable Authenication on finish action'))
		);
		
		$this->extend('updateFormOptions', $options);
		
		return $options;
	}
	
	/**
	 * Return if this form has been modified on the stage site and not published.
	 * this is used on the workflow module and for a couple highlighting things
	 *
	 * @return boolean
	 */
	public function getIsModifiedOnStage() {
		// new unsaved pages could be never be published
		if($this->isNew()) {
			return false;
		}

		$stageVersion = Versioned::get_versionnumber_by_stage('UserDefinedForm', 'Stage', $this->ID);
		$liveVersion = Versioned::get_versionnumber_by_stage('UserDefinedForm', 'Live', $this->ID);

		$isModified = ($stageVersion && $stageVersion != $liveVersion);

		if(!$isModified) {
			if($this->Fields()) {
				foreach($this->Fields() as $field) {
					if($field->getIsModifiedOnStage()) {
						$isModified = true;
						break;
					}
				}
			}
		}
		return $isModified;
	}

	/**
	 * Get the HTML id of the error container displayed above the form.
	 *
	 * @return string
	 */
	public function getErrorContainerID() {
		return $this->config()->error_container_id;
	}
}

/**
 * Controller for the {@link UserDefinedForm} page type.
 *
 * @package userforms
 */

class UserDefinedForm_Controller extends Page_Controller {
	
	private static $finished_anchor = '#uff';

	private static $allowed_actions = array(
		'index',
		'ping',
		'Form',
		'finished'
	);

	public function init() {
		parent::init();
		
		// load the jquery
		$lang = i18n::get_lang_from_locale(i18n::get_locale());
		Requirements::javascript(FRAMEWORK_DIR .'/thirdparty/jquery/jquery.js');
		Requirements::javascript(USERFORMS_DIR . '/thirdparty/jquery-validate/jquery.validate.min.js');
		Requirements::add_i18n_javascript(USERFORMS_DIR . '/javascript/lang');
		Requirements::javascript(USERFORMS_DIR . '/javascript/UserForm_frontend.js');
		Requirements::javascript(
			USERFORMS_DIR . "/thirdparty/jquery-validate/localization/messages_{$lang}.min.js"
		);
		Requirements::javascript(
			USERFORMS_DIR . "/thirdparty/jquery-validate/localization/methods_{$lang}.min.js"
		);
		if($this->HideFieldLabels) {
			Requirements::javascript(USERFORMS_DIR . '/thirdparty/Placeholders.js/Placeholders.min.js');
		}
	}
	
	/**
	 * Using $UserDefinedForm in the Content area of the page shows
	 * where the form should be rendered into. If it does not exist
	 * then default back to $Form.
	 *
	 * @return array
	 */
	public function index() {
		if($this->Content && $form = $this->Form()) {
			$hasLocation = stristr($this->Content, '$UserDefinedForm');
			if($hasLocation) {
				$content = str_ireplace('$UserDefinedForm', $form->forTemplate(), $this->Content);
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
	public function ping() {
		return 1;
	}

	/**
	 * Get the form for the page. Form can be modified by calling {@link updateForm()}
	 * on a UserDefinedForm extension.
	 *
	 * @return Form|false
	 */
	public function Form() {
		$fields = $this->getFormFields();
		if(!$fields || !$fields->exists()) return false;
		
		$actions = $this->getFormActions();
		
		// get the required fields including the validation
		$required = $this->getRequiredFields();

		// generate the conditional logic 
		$this->generateConditionalJavascript();

		$form = new Form($this, "Form", $fields, $actions, $required);
		$form->setRedirectToFormOnValidationError(true);
		
		$data = Session::get("FormInfo.{$form->FormName()}.data");
		
		if(is_array($data)) $form->loadDataFrom($data);
		
		$this->extend('updateForm', $form);

		if($this->DisableCsrfSecurityToken) {
			$form->disableSecurityToken();
		}

		$this->generateValidationJavascript($form);
		
		return $form;
	}

	/**
	 * Get the form fields for the form on this page. Can modify this FieldSet
	 * by using {@link updateFormFields()} on an {@link Extension} subclass which
	 * is applied to this controller.
	 *
	 * @return FieldList
	 */
	public function getFormFields() {
		$fields = new FieldList();

		$editableFields = $this->Fields();
		if($editableFields) foreach($editableFields as $editableField) {
			// get the raw form field from the editable version
			$field = $editableField->getFormField();
			if(!$field) break;

			// set the error / formatting messages
			$field->setCustomValidationMessage($editableField->getErrorMessage());

			// set the right title on this field
			if($right = $editableField->getSetting('RightTitle')) {
				// Since this field expects raw html, safely escape the user data prior
				$field->setRightTitle(Convert::raw2xml($right));
			}

			// if this field is required add some
			if($editableField->Required) {
				$field->addExtraClass('requiredField');

				if($identifier = UserDefinedForm::config()->required_identifier) {

					$title = $field->Title() ." <span class='required-identifier'>". $identifier . "</span>";
					$field->setTitle($title);
				}
			}
			// if this field has an extra class
			if($extraClass = $editableField->getSetting('ExtraClass')) {
				$field->addExtraClass(Convert::raw2att($extraClass));
			}

			// set the values passed by the url to the field
			$request = $this->getRequest();
			if($value = $request->getVar($field->getName())) {
				$field->setValue($value);
			}

			$fields->push($field);
		}
		$this->extend('updateFormFields', $fields);

		return $fields;
	}
	
	/**
	 * Generate the form actions for the UserDefinedForm. You 
	 * can manipulate these by using {@link updateFormActions()} on
	 * a decorator.
	 *
	 * @todo Make form actions editable via their own field editor.
	 *
	 * @return FieldList
	 */
	public function getFormActions() {
		$submitText = ($this->SubmitButtonText) ? $this->SubmitButtonText : _t('UserDefinedForm.SUBMITBUTTON', 'Submit');
		$clearText = ($this->ClearButtonText) ? $this->ClearButtonText : _t('UserDefinedForm.CLEARBUTTON', 'Clear');
		
		$actions = new FieldList(
			new FormAction("process", $submitText)
		);

		if($this->ShowClearButton) {
			$actions->push(new ResetFormAction("clearForm", $clearText));
		}
		
		$this->extend('updateFormActions', $actions);
		
		return $actions;
	}
	
	/**
	 * Get the required form fields for this form.
	 *
	 * @return RequiredFields
	 */
	public function getRequiredFields() {
		// Generate required field validator
		$requiredNames = $this
			->Fields()
			->filter('Required', true)
			->column('Name');
		$required = new RequiredFields($requiredNames);
		
		$this->extend('updateRequiredFields', $required);
		
		return $required;
	}

	/**
	 * Build jQuery validation script and require as a custom script
	 * 
	 * @param Form $form
	 */
	public function generateValidationJavascript($form) {
		// set the custom script for this form
		Requirements::customScript(
			$this
				->customise(array('Form' => $form))
				->renderWith('ValidationScript'),
			'UserFormsValidation'
		);
	}
	
	/**
	 * Generate the javascript for the conditional field show / hiding logic.
	 *
	 * @return void
	 */
	public function generateConditionalJavascript() {
		$default = "";
		$rules = "";

		$watch = array();
		$watchLoad = array();

		if($this->Fields()) {
			foreach($this->Fields() as $field) {
				$fieldId = $field->Name;
				
				if($field->ClassName == 'EditableFormHeading') { 
					$fieldId = 'Form_Form_'.$field->Name;
				}
				
				// Is this Field Show by Default
				if(!$field->getShowOnLoad()) {
					$default .= "$(\"#" . $fieldId . "\").hide();\n";
				}

				// Check for field dependencies / default
				if($field->Dependencies()) {
					foreach($field->Dependencies() as $dependency) {
						if(is_array($dependency) && isset($dependency['ConditionField']) && $dependency['ConditionField'] != "") {
							// get the field which is effected
							$formName = Convert::raw2sql($dependency['ConditionField']);
							$formFieldWatch = DataObject::get_one("EditableFormField", "\"Name\" = '$formName'");
							
							if(!$formFieldWatch) break;
							
							// watch out for multiselect options - radios and check boxes
							if(is_a($formFieldWatch, 'EditableDropdown')) {
								$fieldToWatch = "$(\"select[name='".$dependency['ConditionField']."']\")";
								$fieldToWatchOnLoad = $fieldToWatch;
							}
							// watch out for checkboxs as the inputs don't have values but are 'checked
							else if(is_a($formFieldWatch, 'EditableCheckboxGroupField')) {
								$fieldToWatch = "$(\"input[name='".$dependency['ConditionField']."[".$dependency['Value']."]']\")";
								$fieldToWatchOnLoad = $fieldToWatch;
							}
							else if(is_a($formFieldWatch, 'EditableRadioField')) {
								$fieldToWatch = "$(\"input[name='".$dependency['ConditionField']."']\")";
								// We only want to trigger on load once for the radio group - hence we focus on the first option only.
								$fieldToWatchOnLoad = "$(\"input[name='".$dependency['ConditionField']."']:first\")";
							}
							else {
								$fieldToWatch = "$(\"input[name='".$dependency['ConditionField']."']\")";
								$fieldToWatchOnLoad = $fieldToWatch;
							}
							
							// show or hide?
							$view = (isset($dependency['Display']) && $dependency['Display'] == "Hide") ? "hide" : "show";
							$opposite = ($view == "show") ? "hide" : "show";
							
							// what action do we need to keep track of. Something nicer here maybe?
							// @todo encapulsation
							$action = "change";
							
							if($formFieldWatch->ClassName == "EditableTextField") {
								$action = "keyup";
							}
							
							// is this field a special option field
							$checkboxField = false;
							$radioField = false;

							if(in_array($formFieldWatch->ClassName, array('EditableCheckboxGroupField', 'EditableCheckbox'))) {
								$action = "click";
								$checkboxField = true;
							}
							else if ($formFieldWatch->ClassName == "EditableRadioField") {
								$radioField = true;
							}

							// Escape the values.
							$dependency['Value'] = str_replace('"', '\"', $dependency['Value']);

							// and what should we evaluate
							switch($dependency['ConditionOption']) {
								case 'IsNotBlank':
									$expression = ($checkboxField || $radioField) ? '$(this).prop("checked")' :'$(this).val() != ""';

									break;
								case 'IsBlank':
									$expression = ($checkboxField || $radioField) ? '!($(this).prop("checked"))' : '$(this).val() == ""';
									
									break;
								case 'HasValue':
									if ($checkboxField) {
										$expression = '$(this).prop("checked")';
									} else if ($radioField) {
										// We cannot simply get the value of the radio group, we need to find the checked option first.
										$expression = '$(this).parents(".field, .control-group").find("input:checked").val()=="'. $dependency['Value'] .'"';
									} else {
										$expression = '$(this).val() == "'. $dependency['Value'] .'"';
									}

									break;
								case 'ValueLessThan':
									$expression = '$(this).val() < parseFloat("'. $dependency['Value'] .'")';
									
									break;
								case 'ValueLessThanEqual':
									$expression = '$(this).val() <= parseFloat("'. $dependency['Value'] .'")';
									
									break;
								case 'ValueGreaterThan':
									$expression = '$(this).val() > parseFloat("'. $dependency['Value'] .'")';

									break;
								case 'ValueGreaterThanEqual':
									$expression = '$(this).val() >= parseFloat("'. $dependency['Value'] .'")';

									break;
								default: // ==HasNotValue
									if ($checkboxField) {
										$expression = '!$(this).prop("checked")';
									} else if ($radioField) {
										// We cannot simply get the value of the radio group, we need to find the checked option first.
										$expression = '$(this).parents(".field, .control-group").find("input:checked").val()!="'. $dependency['Value'] .'"';
									} else {
										$expression = '$(this).val() != "'. $dependency['Value'] .'"';
									}
								
									break;
							}
	
							if(!isset($watch[$fieldToWatch])) {
								$watch[$fieldToWatch] = array();
							}

							$watch[$fieldToWatch][] =  array(
								'expression' => $expression,
								'field_id' => $fieldId,
								'view' => $view,
								'opposite' => $opposite,
								'action' => $action
							);

							$watchLoad[$fieldToWatchOnLoad] = true;
					
						}
					}
				}
			}
		}
		
		if($watch) {
			foreach($watch as $key => $values) {
				$logic = array();
				$actions = array();

				foreach($values as $rule) {
					// Register conditional behaviour with an element, so it can be triggered from many places.
					$logic[] = sprintf(
						'if(%s) { $("#%s").%s(); } else { $("#%2$s").%s(); }', 
						$rule['expression'], 
						$rule['field_id'], 
						$rule['view'], 
						$rule['opposite']
					);

					$actions[$rule['action']] = $rule['action'];
				}

				$logic = implode("\n", $logic);
				$rules .= $key.".each(function() {\n
	$(this).data('userformConditions', function() {\n
		$logic\n
	}); \n
});\n";
				foreach($actions as $action) {
					$rules .= $key.".$action(function() {
	$(this).data('userformConditions').call(this);\n
});\n";
				}
			}
		}

		if($watchLoad) {
			foreach($watchLoad as $key => $value) {
				$rules .= $key.".each(function() {
	$(this).data('userformConditions').call(this);\n
});\n";
			}
		}

		// Only add customScript if $default or $rules is defined
    	if($default  || $rules) {
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
	 * Convert a PHP array to a JSON string. We cannot use {@link Convert::array2json}
	 * as it escapes our values with "" which appears to break the validate plugin
	 *
	 * @param Array array to convert
	 * @return JSON 
	 */
	public function array2json($array) {
		foreach($array as $key => $value) {
			if(is_array( $value )) {
				$result[] = "$key:" . $this->array2json($value);
			} else {
				$value = ( is_bool($value) || is_numeric($value) ) ? $value : "\"$value\"";
				$result[] = "$key:$value";
			}
		}

		return (isset($result)) ? "{\n".implode( ', ', $result ) ."\n}\n": '{}';
	}
	
	/**
	 * Process the form that is submitted through the site
	 * 
	 * @param array $data
	 * @param Form $form
	 *
	 * @return Redirection
	 */
	public function process($data, $form) {
		Session::set("FormInfo.{$form->FormName()}.data",$data);
		Session::clear("FormInfo.{$form->FormName()}.errors");
		
		foreach($this->Fields() as $field) {
			$messages[$field->Name] = $field->getErrorMessage()->HTML();
			$formField = $field->getFormField();

			if($field->Required && $field->CustomRules()->Count() == 0) {
				if(isset($data[$field->Name])) {
					$formField->setValue($data[$field->Name]);
				}

				if(
					!isset($data[$field->Name]) || 
					!$data[$field->Name] ||
					!$formField->validate($form->getValidator())
				) {
					$form->addErrorMessage($field->Name, $field->getErrorMessage(), 'bad');
				}
			}
		}
		
		if(Session::get("FormInfo.{$form->FormName()}.errors")){
			Controller::curr()->redirectBack();

			return;
		}
		
		$submittedForm = Object::create('SubmittedForm');
		$submittedForm->SubmittedByID = ($id = Member::currentUserID()) ? $id : 0;
		$submittedForm->ParentID = $this->ID;

		// if saving is not disabled save now to generate the ID
		if(!$this->DisableSaveSubmissions) {
			$submittedForm->write();
		}
		
		$values = array();
		$attachments = array();

		$submittedFields = new ArrayList();
		
		foreach($this->Fields() as $field) {
			if(!$field->showInReports()) {
				continue;
			}
			
			$submittedField = $field->getSubmittedFormField();
			$submittedField->ParentID = $submittedForm->ID;
			$submittedField->Name = $field->Name;
			$submittedField->Title = $field->getField('Title');
			
			// save the value from the data
			if($field->hasMethod('getValueFromData')) {
				$submittedField->Value = $field->getValueFromData($data);
			} else {
				if(isset($data[$field->Name])) {
					$submittedField->Value = $data[$field->Name];
				}
			}

			if(!empty($data[$field->Name])){
				if(in_array("EditableFileField", $field->getClassAncestry())) {
					if(isset($_FILES[$field->Name])) {
						$foldername = $field->getFormField()->getFolderName();
						
						// create the file from post data
						$upload = new Upload();
						$file = new File();
						$file->ShowInSearch = 0;
						try {
							$upload->loadIntoFile($_FILES[$field->Name], $file, $foldername);
						} catch( ValidationException $e ) {
							$validationResult = $e->getResult();
							$form->addErrorMessage($field->Name, $validationResult->message(), 'bad');
							Controller::curr()->redirectBack();
							return;
						}

						// write file to form field
						$submittedField->UploadedFileID = $file->ID;
						
						// attach a file only if lower than 1MB
						if($file->getAbsoluteSize() < 1024*1024*1){
							$attachments[] = $file;
						}
					}
				}
			}
			
			$submittedField->extend('onPopulationFromField', $field);
			
			if(!$this->DisableSaveSubmissions) {
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
		if($recipients = $this->FilteredEmailRecipients($data, $form)) {
			$email = new UserDefinedForm_SubmittedFormEmail($submittedFields); 
			$mergeFields = $this->getMergeFieldsMap($emailData['Fields']);

			if($attachments) {
				foreach($attachments as $file) {
					if($file->ID != 0) {
						$email->attachFile(
							$file->Filename, 
							$file->Filename, 
							HTTP::get_mime_type($file->Filename)
						);
					}
				}
			}

			foreach($recipients as $recipient) {
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
				
				if($recipient->EmailReplyTo) {
					$email->setReplyTo($recipient->EmailReplyTo);
				}

				// check to see if they are a dynamic reply to. eg based on a email field a user selected
				if($recipient->SendEmailFromField()) {
					$submittedFormField = $submittedFields->find('Name', $recipient->SendEmailFromField()->Name);

					if($submittedFormField && is_string($submittedFormField->Value)) {
						$email->setReplyTo($submittedFormField->Value);
					}
				}
				// check to see if they are a dynamic reciever eg based on a dropdown field a user selected
				if($recipient->SendEmailToField()) {
					$submittedFormField = $submittedFields->find('Name', $recipient->SendEmailToField()->Name);
					
					if($submittedFormField && is_string($submittedFormField->Value)) {
						$email->setTo($submittedFormField->Value);
					}
				}
				
				// check to see if there is a dynamic subject
				if($recipient->SendEmailSubjectField()) {
					$submittedFormField = $submittedFields->find('Name', $recipient->SendEmailSubjectField()->Name);

					if($submittedFormField && trim($submittedFormField->Value)) {
						$email->setSubject($submittedFormField->Value);
					}
				}

				$this->extend('updateEmail', $email, $recipient, $emailData);

				if($recipient->SendPlain) {
					$body = strip_tags($recipient->getEmailBodyContent()) . "\n";
					if(isset($emailData['Fields']) && !$recipient->HideFormData) {
						foreach($emailData['Fields'] as $Field) {
							$body .= $Field->Title .': '. $Field->Value ." \n";
						}
					}

					$email->setBody($body);
					$email->sendPlain();
				}
				else {
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
		if(!$this->DisableAuthenicatedFinishAction) {
			if (isset($data['SecurityID'])) {
				Session::set('FormProcessed',$data['SecurityID']);
			} else {
				// if the form has had tokens disabled we still need to set FormProcessed
				// to allow us to get through the finshed method
				if (!$this->Form()->getSecurityToken()->isEnabled()) {
					$randNum = rand(1, 1000);
					$randHash = md5($randNum);
					Session::set('FormProcessed',$randHash);
					Session::set('FormProcessedNum',$randNum);
				}
			}
		}
		
		if(!$this->DisableSaveSubmissions) {
			Session::set('userformssubmission'. $this->ID, $submittedForm->ID);
		}

		return $this->redirect($this->Link('finished') . $referrer . $this->config()->finished_anchor);
	}

	/**
	 * Allows the use of field values in email body.
	 *
	 * @param ArrayList fields
	 * @return ViewableData
	 */
	private function getMergeFieldsMap($fields = array()) {
		$data = new ViewableData();

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
	public function finished() {
		$submission = Session::get('userformssubmission'. $this->ID);

		if($submission) {
			$submission = SubmittedForm::get()->byId($submission);
		}

		$referrer = isset($_GET['referrer']) ? urldecode($_GET['referrer']) : null;
		
		if(!$this->DisableAuthenicatedFinishAction) {
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

/**
 * A Form can have multiply members / emails to email the submission
 * to and custom subjects
 *
 * @package userforms
 */
class UserDefinedForm_EmailRecipient extends DataObject {

	private static $db = array(
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
	);

	private static $has_one = array(
		'Form' => 'UserDefinedForm',
		'SendEmailFromField' => 'EditableFormField',
		'SendEmailToField' => 'EditableFormField',
		'SendEmailSubjectField' => 'EditableFormField'
	);

	private static $has_many = array(
		'CustomRules' => 'UserDefinedForm_EmailRecipientCondition'
	);

	private static $summary_fields = array(
		'EmailAddress',
		'EmailSubject',
		'EmailFrom'
	);

	public function summaryFields() {
		$fields = parent::summaryFields();
		if(isset($fields['EmailAddress'])) {
			$fields['EmailAddress'] = _t('UserDefinedForm.EMAILADDRESS', 'Email');
		}
		if(isset($fields['EmailSubject'])) {
			$fields['EmailSubject'] = _t('UserDefinedForm.EMAILSUBJECT', 'Subject');
		}
		if(isset($fields['EmailFrom'])) {
			$fields['EmailFrom'] = _t('UserDefinedForm.EMAILFROM', 'From');
		}
		return $fields;
	}

	/**
	 * Get instance of UserDefinedForm when editing in getCMSFields
	 *
	 * @return UserDefinedFrom
	 */
	protected function getFormParent() {
		$formID = $this->FormID
			? $this->FormID
			: Session::get('CMSMain.currentPage');
		return UserDefinedForm::get()->byID($formID);
	}

	public function getTitle() {
		if($this->EmailAddress) {
			return $this->EmailAddress;
		}
		if($this->EmailSubject) {
			return $this->EmailSubject;
		}
		return parent::getTitle();
	}

	/**
	 * Generate a gridfield config for editing filter rules
	 *
	 * @return GridFieldConfig
	 */
	protected function getRulesConfig() {
		$formFields = $this->getFormParent()->Fields();

		$config = GridFieldConfig::create()
			->addComponents(
				new GridFieldButtonRow('before'),
				new GridFieldToolbarHeader(),
				new GridFieldAddNewInlineButton(),
				new GridState_Component(),
				new GridFieldDeleteAction(),
				$columns = new GridFieldEditableColumns()
			);

		$columns->setDisplayFields(array(
			'ConditionFieldID' => function($record, $column, $grid) use ($formFields) {
				return DropdownField::create($column, false, $formFields->map('ID', 'Title'));
			},
			'ConditionOption' => function($record, $column, $grid) {
				$options = UserDefinedForm_EmailRecipientCondition::config()->condition_options;
				return DropdownField::create($column, false, $options);
			},
			'ConditionValue' => function($record, $column, $grid) {
				return TextField::create($column);
			}
		));

		return $config;
	}

	/**
	 * @return FieldList
	 */
	public function getCMSFields() {
		// Determine optional field values
		$form = $this->getFormParent();

		// predefined choices are also candidates
		$multiOptionFields = EditableMultipleOptionField::get()->filter('ParentID', $form->ID);

		// if they have email fields then we could send from it
		$validEmailFromFields = EditableEmailField::get()->filter('ParentID', $form->ID);

		// For the subject, only one-line entry boxes make sense
		$validSubjectFields = EditableTextField::get()
			->filter('ParentID', $form->ID)
			->filterByCallback(function($item, $list) {
				return (int)$item->getSetting('Rows') === 1;
			});
		$validSubjectFields->merge($multiOptionFields);

		// To address can only be email fields or multi option fields
		$validEmailToFields = new ArrayList($validEmailFromFields->toArray());
		$validEmailToFields->merge($multiOptionFields);

		// Build fieldlist
		$fields = FieldList::create(Tabset::create('Root')->addExtraClass('EmailRecipientForm'));

		// Configuration fields
		$fields->addFieldsToTab('Root.EmailDetails', array(
			// Subject
			FieldGroup::create(
				TextField::create('EmailSubject', _t('UserDefinedForm.TYPESUBJECT', 'Type subject'))
					->setAttribute('style', 'min-width: 400px;'),
				DropdownField::create(
					'SendEmailSubjectFieldID',
					_t('UserDefinedForm.SELECTAFIELDTOSETSUBJECT', '.. or select a field to use as the subject'),
					$validSubjectFields->map('ID', 'Title')
				)->setEmptyString('')
			)
				->setTitle(_t('UserDefinedForm.EMAILSUBJECT', 'Email subject')),

			// To
			FieldGroup::create(
				TextField::create('EmailAddress', _t('UserDefinedForm.TYPETO', 'Type to address'))
					->setAttribute('style', 'min-width: 400px;'),
				DropdownField::create(
					'SendEmailToFieldID',
					_t('UserDefinedForm.ORSELECTAFIELDTOUSEASTO', '.. or select a field to use as the to address'),
					$validEmailToFields->map('ID', 'Title')
				)->setEmptyString(' ')
			)
				->setTitle(_t('UserDefinedForm.SENDEMAILTO','Send email to'))
				->setDescription(_t(
					'UserDefinedForm.SENDEMAILTO_DESCRIPTION',
					'You may enter multiple email addresses as a comma separated list.'
				)),


			// From
			TextField::create('EmailFrom', _t('UserDefinedForm.FROMADDRESS','Send email from'))
				->setDescription(_t(
					'UserDefinedForm.EmailFromContent',
					"The from address allows you to set who the email comes from. On most servers this ".
					"will need to be set to an email address on the same domain name as your site. ".
					"For example on yoursite.com the from address may need to be something@yoursite.com. ".
					"You can however, set any email address you wish as the reply to address."
				)),


			// Reply-To
			FieldGroup::create(
				TextField::create('EmailReplyTo', _t('UserDefinedForm.TYPEREPLY', 'Type reply address'))
					->setAttribute('style', 'min-width: 400px;'),
				DropdownField::create(
					'SendEmailFromFieldID',
					_t('UserDefinedForm.ORSELECTAFIELDTOUSEASFROM', '.. or select a field to use as reply to address'),
					$validEmailFromFields->map('ID', 'Title')
				)->setEmptyString(' ')
			)
				->setTitle(_t('UserDefinedForm.REPLYADDRESS', 'Email for reply to'))
				->setDescription(_t(
					'UserDefinedForm.REPLYADDRESS_DESCRIPTION',
					'The email address which the recipient is able to \'reply\' to.'
				))
		));
		
		// Only show the preview link if the recipient has been saved.
		if (!empty($this->EmailTemplate)) {
			$preview = sprintf(
				'<p><a href="%s" target="_blank" class="ss-ui-button">%s</a></p><em>%s</em>',
				"admin/pages/edit/EditForm/field/EmailRecipients/item/{$this->ID}/preview",
				_t('UserDefinedForm.PREVIEW_EMAIL', 'Preview email'),
				_t('UserDefinedForm.PREVIEW_EMAIL_DESCRIPTION', 'Note: Unsaved changes will not appear in the preview.')
			);
		} else {
			$preview = sprintf(
				'<em>%s</em>',
				_t(
					'UserDefinedForm.PREVIEW_EMAIL_UNAVAILABLE',
					'You can preview this email once you have saved the Recipient.'
				)
			);
		}

		// Email templates
		$fields->addFieldsToTab('Root.EmailContent', array(
			new CheckboxField('HideFormData', _t('UserDefinedForm.HIDEFORMDATA', 'Hide form data from email?')),
			new CheckboxField('SendPlain', _t('UserDefinedForm.SENDPLAIN', 'Send email as plain text? (HTML will be stripped)')),
			new DropdownField('EmailTemplate', _t('UserDefinedForm.EMAILTEMPLATE', 'Email template'), $this->getEmailTemplateDropdownValues()),
			new HTMLEditorField('EmailBodyHtml', _t('UserDefinedForm.EMAILBODYHTML','Body')),
			new TextareaField('EmailBody', _t('UserDefinedForm.EMAILBODY','Body')),
			new LiteralField('EmailPreview', '<div id="EmailPreview">' . $preview . '</div>')
		));

		// Custom rules for sending this field
		$grid = new GridField(
			"CustomRules",
			_t('EditableFormField.CUSTOMRULES', 'Custom Rules'),
			$this->CustomRules(),
			$this->getRulesConfig()
		);
		$grid->setDescription(_t(
			'UserDefinedForm.RulesDescription',
			'Emails will only be sent to the recipient if the custom rules are met. If no rules are defined, this receipient will receive notifications for every submission.'
		));
		$fields->addFieldsToTab('Root.CustomRules', array(
			new DropdownField(
				'CustomRulesCondition',
				_t('UserDefinedForm.SENDIF', 'Send condition'),
				array(
					'Or' => 'Any conditions are true',
					'And' => 'All conditions are true'
				)
			),
			$grid
		));

		$this->extend('updateCMSFields', $fields);
		return $fields;
	}

	/**
	 * @param Member
	 *
	 * @return boolean
	 */
	public function canCreate($member = null) {
		return $this->Form()->canEdit();
	}

	/**
	 * @param Member
	 *
	 * @return boolean
	 */
	public function canView($member = null) {
		return $this->Form()->canView();
	}

	/**
	 * @param Member
	 *
	 * @return boolean
	 */
	public function canEdit($member = null) {
		return $this->Form()->canEdit();
	}

	/**
	 * @param Member
	 *
	 * @return boolean
	 */
	public function canDelete($member = null) {
		return $this->Form()->canDelete();
	}

	/*
	 * Determine if this recipient may receive notifications for this submission
	 *
	 * @param array $data
	 * @param Form $form
	 * @return bool
	 */
	public function canSend($data, $form) {
		// Skip if no rules configured
		$customRules = $this->CustomRules();
		if(!$customRules->count()) {
			return true;
		}

		// Check all rules
		$isAnd = $this->CustomRulesCondition === 'And';
		foreach($customRules as $customRule) {
			$matches = $customRule->matches($data, $form);
			if($isAnd && !$matches) {
				return false;
			}
			if(!$isAnd && $matches) {
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
	public function emailTemplateExists($template = '') {
		$t = ($template ? $template : $this->EmailTemplate);

		return in_array($t, $this->getEmailTemplateDropdownValues());
	}

	/**
	 * Get the email body for the current email format
	 *
	 * @return string
	 */
	public function getEmailBodyContent() {
		return $this->SendPlain ? $this->EmailBody : $this->EmailBodyHtml;
	}

	/**
	 * Gets a list of email templates suitable for populating the email template dropdown.
	 *
	 * @return array
	 */
	public function getEmailTemplateDropdownValues() {
		$templates = array();

		$finder = new SS_FileFinder();
		$finder->setOption('name_regex', '/^.*\.ss$/');

		$found = $finder->find(BASE_PATH . '/' . UserDefinedForm::config()->email_template_directory);

		foreach ($found as $key => $value) {
			$template = pathinfo($value);

			$templates[$template['filename']] = $template['filename'];
		}

		return $templates;
	}
}

/**
 * Controller that handles requests to EmailRecipient's
 *
 * @package userforms
 */
class UserDefinedForm_EmailRecipient_ItemRequest extends GridFieldDetailForm_ItemRequest {

	private static $allowed_actions = array(
		'edit',
		'view',
		'ItemEditForm',
		'preview'
	);

	public function edit($request) {
		Requirements::javascript(USERFORMS_DIR . '/javascript/Recipient.js');
		return parent::edit($request);
	}

	/**
	 * Renders a preview of the recipient email.
	 */
	public function preview() {
		return $this->customise(new ArrayData(array(
			'Body' => $this->record->getEmailBodyContent(),
			'HideFormData' => $this->record->HideFormData,
			'Fields' => $this->getPreviewFieldData()
		)))->renderWith($this->record->EmailTemplate);
	}

	/**
	 * Get some placeholder field values to display in the preview
	 * @return ArrayList
	 */
	private function getPreviewFieldData() {
		$data = new ArrayList();

		$fields = $this->record->Form()->Fields()->filter(array(
			'ClassName:not' => 'EditableLiteralField',
			'ClassName:not' => 'EditableFormHeading'
		));

		foreach ($fields as $field) {
			$data->push(new ArrayData(array(
				'Name' => $field->Name,
				'Title' => $field->Title,
				'Value' => '$' . $field->Name,
				'FormattedValue' => '$' . $field->Name
			)));
		}

		return $data;
	}
}

/**
 * Declares a condition that determines whether an email can be sent to a given recipient
 */
class UserDefinedForm_EmailRecipientCondition extends DataObject {

	/**
	 * List of options
	 *
	 * @config
	 * @var array
	 */
	private static $condition_options = array(
		"IsBlank" => "Is blank",
		"IsNotBlank" => "Is not blank",
		"Equals" => "Equals",
		"NotEquals" => "Doesn't equal"
	);

	private static $db = array(
		'ConditionOption' => 'Enum("IsBlank,IsNotBlank,Equals,NotEquals")',
		'ConditionValue' => 'Varchar'
	);

	private static $has_one = array(
		'Parent' => 'UserDefinedForm_EmailRecipient',
		'ConditionField' => 'EditableFormField'
	);

	/**
	 * Determine if this rule matches the given condition
	 *
	 * @param array $data
	 * @param Form $form
	 * @return bool
	 */
	public function matches($data, $form) {
		$fieldName = $this->ConditionField()->Name;
		$fieldValue = isset($data[$fieldName]) ? $data[$fieldName] : null;
		switch($this->ConditionOption) {
			case 'IsBlank':
				return empty($fieldValue);
			case 'IsNotBlank':
				return !empty($fieldValue);
			default:
				$matches = is_array($fieldValue)
					? in_array($this->ConditionValue, $fieldValue)
					: $this->ConditionValue === (string)$fieldValue;
				return ($this->ConditionOption === 'Equals') === (bool)$matches;
		}
	}
}

/**
 * Email that gets sent to the people listed in the Email Recipients when a 
 * submission is made.
 *
 * @package userforms
 */

class UserDefinedForm_SubmittedFormEmail extends Email {
	
	protected $ss_template = "SubmittedFormEmail";

	protected $data;

	public function __construct($submittedFields = null) {
		parent::__construct($submittedFields = null);
	}
	
	/**
	 * Set the "Reply-To" header with an email address rather than append as
	 * {@link Email::replyTo} does. 
	 *
	 * @param string $email The email address to set the "Reply-To" header to
 	 */
	public function setReplyTo($email) {
		$this->customHeaders['Reply-To'] = $email;
	}
}
