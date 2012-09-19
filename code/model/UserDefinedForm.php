<?php

/**
 * User Defined Form Page type that lets users build a form in the CMS 
 * using the FieldEditor Field. 
 * 
 * @todo Allow UserDefinedForm instances on Page subclasses (eg via decorator)
 *
 * @package userforms
 */

class UserDefinedForm extends Page {
	
	/**
	 * @var String Required Identifier
	 */
	static $required_identifier = null;
	
	/**
	 * @var Array Fields on the user defined form page. 
	 */
	static $db = array(
		"SubmitButtonText" => "Varchar",
		"OnCompleteMessage" => "HTMLText",
		"ShowClearButton" => "Boolean",
		'DisableSaveSubmissions' => 'Boolean'
	);
	
	/**
	 * @var Array Default values of variables when this page is created
	 */ 
	static $defaults = array(
		'Content' => '$UserDefinedForm',
		'DisableSaveSubmissions' => 0,
		'OnCompleteMessage' => '<p>Thanks, we\'ve received your submission.</p>'
	);

	static $extensions = array(
		"Versioned('Stage', 'Live')"
	);

	/**
	 * @var Array
	 */
	static $has_many = array( 
		"Fields" => "EditableFormField",
		"Submissions" => "SubmittedForm",
		"EmailRecipients" => "UserDefinedForm_EmailRecipient"
	);
	
	/**
	 * Setup the CMS Fields for the User Defined Form
	 * 
	 * @return FieldSet
	 */
	public function getCMSFields() {
		$fields = parent::getCMSFields();

		// define tabs
		$fields->findOrMakeTab('Root.Content.Form', _t('UserDefinedForm.FORM', 'Form'));
		$fields->findOrMakeTab('Root.Content.Options', _t('UserDefinedForm.OPTIONS', 'Options'));
		$fields->findOrMakeTab('Root.Content.EmailRecipients', _t('UserDefinedForm.EMAILRECIPIENTS', 'Email Recipients'));
		$fields->findOrMakeTab('Root.Content.OnComplete', _t('UserDefinedForm.ONCOMPLETE', 'On Complete'));
		$fields->findOrMakeTab('Root.Content.Submissions', _t('UserDefinedForm.SUBMISSIONS', 'Submissions'));

		// field editor
		$fields->addFieldToTab("Root.Content.Form", new FieldEditor("Fields", 'Fields', "", $this ));
		
		// view the submissions
		$fields->addFieldToTab("Root.Content.Submissions", new CheckboxField('DisableSaveSubmissions',_t('UserDefinedForm.SAVESUBMISSIONS',"Disable Saving Submissions to Server")));
		$fields->addFieldToTab("Root.Content.Submissions", new SubmittedFormReportField( "Reports", _t('UserDefinedForm.RECEIVED', 'Received Submissions'), "", $this ) );

		// who do we email on submission
		$emailRecipients = new ComplexTableField(
			$this,
	    	'EmailRecipients',
	    	'UserDefinedForm_EmailRecipient',
	    	array(
				'EmailAddress' => _t('UserDefinedForm.EMAILADDRESS', 'Email'),
				'EmailSubject' => _t('UserDefinedForm.EMAILSUBJECT', 'Subject'),
				'EmailFrom' => _t('UserDefinedForm.EMAILFROM', 'From')
	    	),
	    	'getCMSFields_forPopup',
			"\"FormID\" = '$this->ID'"
		);
		$emailRecipients->setAddTitle(_t('UserDefinedForm.AEMAILRECIPIENT', 'A Email Recipient'));
		
		$fields->addFieldToTab("Root.Content.EmailRecipients", $emailRecipients);
	
		// text to show on complete
		$onCompleteFieldSet = new FieldSet(
			new HtmlEditorField( "OnCompleteMessage", _t('UserDefinedForm.ONCOMPLETELABEL', 'Show on completion'),3,"",_t('UserDefinedForm.ONCOMPLETEMESSAGE', $this->OnCompleteMessage), $this )
		);
		
		$fields->addFieldsToTab("Root.Content.OnComplete", $onCompleteFieldSet);
		$fields->addFieldsToTab("Root.Content.Options", $this->getFormOptions());
		
		return $fields;
	}
	
	
	/**
	 * Publishing Versioning support.
	 *
	 * When publishing copy the editable form fields to the live database
	 * Not going to version emails and submissions as they are likely to 
	 * persist over multiple versions
	 *
	 * @return void
	 */
	public function doPublish() {
		// remove fields on the live table which could have been orphaned.
		$live = Versioned::get_by_stage("EditableFormField", "Live", "\"EditableFormField\".\"ParentID\" = $this->ID");

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
	 * Unpublishing Versioning support
	 * 
	 * When unpublishing the page it has to remove all the fields from 
	 * the live database table
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
	 * @param String|int Version to roll back to
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
	 * Duplicate this UserDefinedForm page, and its form fields.
	 * Submissions, on the other hand, won't be duplicated.
	 *
	 * @return Page
	 */
	public function duplicate() {
		$page = parent::duplicate();
		
		// the form fields
		if($this->Fields()) {
			foreach($this->Fields() as $field) {
				$newField = $field->duplicate();
				$newField->ParentID = $page->ID;
				$newField->write();
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
		
		return $page;
	}
	
	/**
	 * Custom options for the form. You can extend the built in options by 
	 * using {@link updateFormOptions()}
	 *
	 * @return FieldSet
	 */
  	public function getFormOptions() {
		$submit = ($this->SubmitButtonText) ? $this->SubmitButtonText : _t('UserDefinedForm.SUBMITBUTTON', 'Submit');
		
		$options = new FieldSet(
			new TextField("SubmitButtonText", _t('UserDefinedForm.TEXTONSUBMIT', 'Text on submit button:'), $submit),
			new CheckboxField("ShowClearButton", _t('UserDefinedForm.SHOWCLEARFORM', 'Show Clear Form Button'), $this->ShowClearButton)
		);
		
		$this->extend('updateFormOptions', $options);
		
		return $options;
	}
	
	/**
	 * Return if this form has been modified on the stage site and not published.
	 * this is used on the workflow module and for a couple highlighting things
	 *
	 * @todo make this a bit smarter - the issue with userforms is that it uses several
	 * 		relationships to form fields which has a undefined amount of options so 
	 * 		for now just say its always modified
	 */
	public function getIsModifiedOnStage() {
		return true;
	}
}

/**
 * Controller for the {@link UserDefinedForm} page type.
 *
 * @package userform
 * @subpackage pagetypes
 */

class UserDefinedForm_Controller extends Page_Controller {
	
	/**
	 * Load all the custom jquery needed to run the custom 
	 * validation 
	 */
	public function init() {
		parent::init();
		
		// block prototype validation
		Validator::set_javascript_validation_handler('none');
		
		// load the jquery
		Requirements::javascript(SAPPHIRE_DIR .'/thirdparty/jquery/jquery.js');
		Requirements::javascript('userforms/thirdparty/jquery-validate/jquery.validate.min.js');
		Requirements::javascript('userforms/javascript/UserForm_frontend.js');
	}
	
	/**
	 * Using $UserDefinedForm in the Content area of the page shows
	 * where the form should be rendered into. If it does not exist
	 * then default back to $Form
	 *
	 * @return Array
	 */
	public function index() {
		if($this->Content && $form = $this->Form()) {
			$hasLocation = stristr($this->Content, '$UserDefinedForm');
			if($hasLocation) {
				$content = str_ireplace('$UserDefinedForm', $form->forTemplate(), $this->Content);
				return array(
					'Content' => DBField::create('HTMLText', $content),
					'Form' => ""
				);
			}
		}

		return array(
			'Content' => DBField::create('HTMLText', $this->Content),
			'Form' => $this->Form()
		);
	}

	/**
	 * Keep the session alive for the user.
	 */
	function ping() {
		return 1;
	}

	/**
	 * Get the form for the page. Form can be modified by calling {@link updateForm()}
	 * on a UserDefinedForm extension
	 *
	 * @return Form|false
	 */
	function Form() {
		$fields = $this->getFormFields();
		if(!$fields || !$fields->exists()) return false;
		
		$actions = $this->getFormActions();
		
		// get the required fields including the validation
		$required = $this->getRequiredFields();

		// generate the conditional logic 
		$this->generateConditionalJavascript();

		$form = new Form($this, "Form", $fields, $actions, $required);
		
		$data = Session::get("FormInfo.{$form->FormName()}.data");
		
		if(is_array($data)) $form->loadDataFrom($data);
		
		$this->extend('updateForm', $form);
		
		return $form;
	}
	
	/**
	 * Get the form fields for the form on this page. Can modify this FieldSet
	 * by using {@link updateFormFields()} on an {@link Extension} subclass which
	 * is applied to this controller
	 *
	 * @return FieldSet
	 */
	function getFormFields() {
		$fields = new FieldSet();

		if($this->Fields()) {
			foreach($this->Fields() as $editableField) {
				// get the raw form field from the editable version
				$field = $editableField->getFormField();
				if(!$field) break;
				
				// set the error / formatting messages
				$field->setCustomValidationMessage($editableField->getErrorMessage());

				// set the right title on this field
				if($right = $editableField->getSetting('RightTitle')) {
					$field->setRightTitle($right);
				}
				
				// if this field is required add some
				if($editableField->Required) {
					$field->addExtraClass('requiredField');
					
					if($identifier = UserDefinedForm::$required_identifier) {
						
						$title = $field->Title() ." <span class='required-identifier'>". $identifier . "</span>";
						$field->setTitle($title);
					}
				}
				// if this field has an extra class
				if($editableField->getSetting('ExtraClass')) {
					$field->addExtraClass(Convert::raw2att(
						$editableField->getSetting('ExtraClass')
					));
				}
				
				// set the values passed by the url to the field
				$request = $this->getRequest();
				if(isset($request) && $var = $request->getVar($field->name)) {
					$field->value = Convert::raw2att($var);
				}
				
				$fields->push($field);
			}
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
	 * @return FieldSet
	 */
	function getFormActions() {
		$submitText = ($this->SubmitButtonText) ? $this->SubmitButtonText : _t('UserDefinedForm.SUBMITBUTTON', 'Submit');
		
		$actions = new FieldSet(
			new FormAction("process", $submitText)
		);

		if($this->ShowClearButton) {
			$actions->push(new ResetFormAction("clearForm"));
		}
		
		$this->extend('updateFormActions', $actions);
		
		return $actions;
	}
	
	/**
	 * Get the required form fields for this form. Includes building the jQuery
	 * validate structure
	 *
	 * @return RequiredFields
	 */
	function getRequiredFields() {
		$required = new RequiredFields();
		
		$rules = array();
		$validation = array();
		$messages = array();
		
		if($this->Fields()) {
			foreach($this->Fields() as $field) {
				$messages[$field->Name] = $field->getErrorMessage()->HTML();
	
				if($field->Required && $field->CustomRules()->Count() == 0) {
					$rules[$field->Name] = array_merge(array('required' => true), $field->getValidation());
					$required->addRequiredField($field->Name);
				}
			}
		}
		
		// Set the Form Name
		$rules = $this->array2json($rules);
		$messages = $this->array2json($messages);
		
		// set the custom script for this form
		Requirements::customScript(<<<JS
			(function($) {
				$(document).ready(function() {
					$("#Form_Form").validate({
						ignore: [':hidden'],
						errorClass: "required",	
						errorPlacement: function(error, element) {
							if(element.is(":radio")) {
								error.insertAfter(element.closest("ul"));
							} else {
								error.insertAfter(element);
							}
						},
						messages:
							$messages
						,
						rules: 
						 	$rules
					});
				});
			})(jQuery);
JS
, 'UserFormsValidation');
		
		$this->extend('updateRequiredFields', $required);
		
		return $required;
	}
	
	/**
	 * Generate the javascript for the conditional field show / hiding logic.
	 * Allows complex rules to be created 
	 * @return void
	 */
	function generateConditionalJavascript() {
		$default = "";
		$rules = "";
		
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
									$expression = ($checkboxField || $radioField) ? '$(this).attr("checked")' :'$(this).val() != ""';

									break;
								case 'IsBlank':
									$expression = ($checkboxField || $radioField) ? '!($(this).attr("checked"))' : '$(this).val() == ""';
									
									break;
								case 'HasValue':
									if ($checkboxField) {
										$expression = '$(this).attr("checked")';
									} else if ($radioField) {
										// We cannot simply get the value of the radio group, we need to find the checked option first.
										$expression = '$(this).parents(".field").find("input:checked").val()=="'. $dependency['Value'] .'"';
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
										$expression = '!$(this).attr("checked")';
									} else if ($radioField) {
										// We cannot simply get the value of the radio group, we need to find the checked option first.
										$expression = '$(this).parents(".field").find("input:checked").val()!="'. $dependency['Value'] .'"';
									} else {
										$expression = '$(this).val() != "'. $dependency['Value'] .'"';
									}
								
									break;
							}

							// Register conditional behaviour with an element, so it can be triggered from many places.
							$rules .= $fieldToWatch.".each(function() {
								$(this).data('userformConditions', function() {
									if(". $expression ." ) {
										$(\"#". $fieldId ."\").".$view."();
									}
									else {
										$(\"#". $fieldId ."\").".$opposite."();
									}
								});
							});";

							// Trigger update on element changes.
							$rules .= $fieldToWatch.".$action(function() {
								$(this).data('userformConditions').call(this);
							});\n";

							// Trigger update on load (if server-side validation fails some fields will have different values than defaults).
							$rules .= $fieldToWatchOnLoad.".each(function() {
								$(this).data('userformConditions').call(this);
							});\n";
						}
					}
				}
			}
		}
		
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
	
	/**
	 * Convert a PHP array to a JSON string. We cannot use {@link Convert::array2json}
	 * as it escapes our values with "" which appears to break the validate plugin
	 *
	 * @param Array array to convert
	 * @return JSON 
	 */
	function array2json($array) {
		foreach($array as $key => $value)
			if(is_array( $value )) {
				$result[] = "$key:" . $this->array2json($value);
			} else {
				$value = (is_bool($value)) ? $value : "\"$value\"";
				$result[] = "$key:$value";
			}
		return (isset($result)) ? "{\n".implode( ', ', $result ) ."\n}\n": '{}';
	}
	
	/**
	 * Process the form that is submitted through the site
	 * 
	 * @param Array Data
	 * @param Form Form 
	 * @return Redirection
	 */
	function process($data, $form) {
		Session::set("FormInfo.{$form->FormName()}.data",$data);	
		Session::clear("FormInfo.{$form->FormName()}.errors");
		
		foreach($this->Fields() as $field) {
			$messages[$field->Name] = $field->getErrorMessage()->HTML();
				
			if($field->Required && $field->CustomRules()->Count() == 0) {
				if(	!isset($data[$field->Name]) ||
					!$data[$field->Name] ||
					!$field->getFormField()->validate($this->validator)
				){
					$form->addErrorMessage($field->Name,$field->getErrorMessage()->HTML(),'bad');
				}
			}
		}
		
		if(Session::get("FormInfo.{$form->FormName()}.errors")){
			Director::redirectBack();
			return;
		}
		
		$submittedForm = Object::create('SubmittedForm');
		$submittedForm->SubmittedByID = ($id = Member::currentUserID()) ? $id : 0;
		$submittedForm->ParentID = $this->ID;

		// if saving is not disabled save now to generate the ID
		if(!$this->DisableSaveSubmissions) $submittedForm->write();
		
		$values = array();
        $attachments = array();

		$submittedFields = new DataObjectSet();
		
		foreach($this->Fields() as $field) {
			
			if(!$field->showInReports()) continue;
			
			$submittedField = $field->getSubmittedFormField();
			$submittedField->ParentID = $submittedForm->ID;
			$submittedField->Name = $field->Name;
			$submittedField->Title = $field->getField('Title');
			
			// save the value from the data
			if($field->hasMethod('getValueFromData')) {
				$submittedField->Value = $field->getValueFromData($data);
			}
			else {
				if(isset($data[$field->Name])) $submittedField->Value = $data[$field->Name];
			}

			if(!empty($data[$field->Name])){
				if(in_array("EditableFileField", $field->getClassAncestry())) {
					if(isset($_FILES[$field->Name])) {
						
						// create the file from post data
						$upload = new Upload();
						$file = new File();
						$file->ShowInSearch = 0;
						
						$upload->loadIntoFile($_FILES[$field->Name], $file);

						// write file to form field
						$submittedField->UploadedFileID = $file->ID;
						
						// attach a file only if lower than 1MB
						if($file->getAbsoluteSize() < 1024*1024*1){
							$attachments[] = $file;
						}
					}									
				}
			}
			
			if(!$this->DisableSaveSubmissions) $submittedField->write();
	
			$submittedFields->push($submittedField);
		}
		
		$emailData = array(
			"Sender" => Member::currentUser(),
			"Fields" => $submittedFields
		);

		// email users on submit.
		if($this->EmailRecipients()) {
			
			$email = new UserDefinedForm_SubmittedFormEmail($submittedFields);                     
			$email->populateTemplate($emailData);
			
			if($attachments){
				foreach($attachments as $file){
					if($file->ID != 0) {
						$email->attachFile($file->Filename,$file->Filename, HTTP::getMimeType($file->Filename));
					}
				}
			}

			foreach($this->EmailRecipients() as $recipient) {
				$email->populateTemplate($recipient);
				$email->populateTemplate($emailData);
				$email->setFrom($recipient->EmailFrom);
				$email->setBody($recipient->EmailBody);
				$email->setSubject($recipient->EmailSubject);
				$email->setTo($recipient->EmailAddress);
				
				// check to see if they are a dynamic sender. eg based on a email field a user selected
				if($recipient->SendEmailFromField()) {
					$submittedFormField = $submittedFields->find('Name', $recipient->SendEmailFromField()->Name);
					if($submittedFormField) {
						$email->setFrom($submittedFormField->Value);	
					}
				}
				// check to see if they are a dynamic reciever eg based on a dropdown field a user selected
				if($recipient->SendEmailToField()) {
					$submittedFormField = $submittedFields->find('Name', $recipient->SendEmailToField()->Name);
					
					if($submittedFormField) {
						$email->setTo($submittedFormField->Value);	
					}
				}
				
				if($recipient->SendPlain) {
					$body = strip_tags($recipient->EmailBody) . "\n ";
					if(isset($emailData['Fields']) && !$recipient->HideFormData) {
						foreach($emailData['Fields'] as $Field) {
							$body .= $Field->Title .' - '. $Field->Value .' \n';
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
		
		Session::clear("FormInfo.{$form->FormName()}.errors");
		Session::clear("FormInfo.{$form->FormName()}.data");
		
		$referrer = (isset($data['Referrer'])) ? '?referrer=' . urlencode($data['Referrer']) : "";
		
		return Director::redirect($this->Link() . 'finished' . $referrer);
	}

	/**
	 * This action handles rendering the "finished" message,
	 * which is customisable by editing the ReceivedFormSubmission.ss
	 * template.
	 *
	 * @return ViewableData
	 */
	function finished() {
		$referrer = isset($_GET['referrer']) ? urldecode($_GET['referrer']) : null;
		
		return $this->customise(array(
			'Content' => $this->customise(
				array(
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
	
	static $db = array(
		'EmailAddress' => 'Varchar(200)',
		'EmailSubject' => 'Varchar(200)',
		'EmailFrom' => 'Varchar(200)',
		'EmailBody' => 'Text',
		'SendPlain' => 'Boolean',
		'HideFormData' => 'Boolean'
	);
	
	static $has_one = array(
		'Form' => 'UserDefinedForm',
		'SendEmailFromField' => 'EditableFormField',
		'SendEmailToField' => 'EditableFormField'
	);
	
	/**
	 * Return the fields to edit this email. 
	 * @return FieldSet
	 */
	public function getCMSFields_forPopup() {
		
		$fields = new FieldSet(
			new TextField('EmailSubject', _t('UserDefinedForm.EMAILSUBJECT', 'Email Subject')),
			new TextField('EmailFrom', _t('UserDefinedForm.FROMADDRESS','Send Email From')),
			new TextField('EmailAddress', _t('UserDefinedForm.SENDEMAILTO','Send Email To')),
			new CheckboxField('HideFormData', _t('UserDefinedForm.HIDEFORMDATA', 'Hide Form Data from Email')),
			new CheckboxField('SendPlain', _t('UserDefinedForm.SENDPLAIN', 'Send Email as Plain Text (HTML will be stripped)')),
			new TextareaField('EmailBody', _t('UserDefinedForm.EMAILBODY','Body'))
		);
		
		if($this->Form()) {
			$validEmailFields = DataObject::get("EditableEmailField", "\"ParentID\" = '" . (int)$this->FormID . "'");
			$multiOptionFields = DataObject::get("EditableMultipleOptionField", "\"ParentID\" = '" . (int)$this->FormID . "'");
			
			// if they have email fields then we could send from it
			if($validEmailFields) {
				$fields->insertAfter(new DropdownField('SendEmailFromFieldID', _t('UserDefinedForm.ORSELECTAFIELDTOUSEASFROM', '.. or Select a Form Field to use as the From Address'), $validEmailFields->toDropdownMap('ID', 'Title'), '', null,""), 'EmailFrom');
			}
			
			// if they have multiple options
			if($multiOptionFields || $validEmailFields) {

				if($multiOptionFields && $validEmailFields) {
					$multiOptionFields->merge($validEmailFields);
					
				}
				elseif(!$multiOptionFields) {
					$multiOptionFields = $validEmailFields;	
				}
				
				$multiOptionFields = $multiOptionFields->toDropdownMap('ID', 'Title');
				$fields->insertAfter(new DropdownField('SendEmailToFieldID', _t('UserDefinedForm.ORSELECTAFIELDTOUSEASTO', '.. or Select a Field to use as the To Address'), $multiOptionFields, '', null, ""), 'EmailAddress');
			}
		}

		return $fields;
	}
	
	function canEdit() {
		return $this->Form()->canEdit();
	}
	
	function canDelete() {
		return $this->Form()->canDelete();
	}
}

/**
 * Email that gets sent to the people listed in the Email Recipients 
 * when a submission is made
 *
 * @package userforms
 */

class UserDefinedForm_SubmittedFormEmail extends Email {
	protected $ss_template = "SubmittedFormEmail";
	protected $data;

	function __construct() {
		parent::__construct();
	}
}
