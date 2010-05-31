<?php
/**
 * User Defined Form Page type that lets users build a form in the CMS 
 * using the FieldEditor Field. 
 * 
 * @package userforms
 */

class UserDefinedForm extends Page {
	
	/**
	 * @var String Icon for the User Defined Form in the CMS. Without the extension
	 */
	static $icon = "cms/images/treeicons/task";
	
	/**
	 * @var String What level permission is needed to edit / add 
	 */
	static $need_permission = 'ADMIN';

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
	 * Roll back a form to a previous version
	 *
	 * @param String|int Version to roll back to
	 */
	public function doRollbackTo($version) {
		if($this->Fields()) {
			foreach($this->Fields() as $field) {
				$field->publish($version, "Stage", true);
				$field->writeWithoutVersion();
			}
		}
		
		parent::doRollbackTo($version);
	}
	
	/**
	 * Revert the draft site to the current live site
	 *
	 * @return void
	 */
	public function doRevertToLive() {
		if($this->Fields()) {
			foreach($this->Fields() as $field) {
				$field->writeToStage('Live', 'Stage');
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
	 * Custom Form Actions for the form
	 *
	 * @param bool Is the Form readonly
	 * @return FieldSet
	 */
  	public function customFormActions($isReadonly = false) {
		return new FieldSet(
			new TextField("SubmitButtonText", _t('UserDefinedForm.TEXTONSUBMIT', 'Text on submit button:'), $this->SubmitButtonText),
			new CheckboxField("ShowClearButton", _t('UserDefinedForm.SHOWCLEARFORM', 'Show Clear Form Button'), $this->ShowClearButton)
		);
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
			'Content' => $this->Content,
			'Form' => $this->Form
		);
	}

	/**
	 * User Defined Form. Feature of the user defined form is if you want the
	 * form to appear in a custom location on the page you can use $UserDefinedForm 
	 * in the content area to describe where you want the form
	 *
	 * @return Form
	 */
	public function Form() {
		$fields = new FieldSet();
		$fieldValidation = array();
		$fieldValidationRules = array();
		$CustomDisplayRules = "";
		$defaults = "";
		$this->SubmitButtonText = ($this->SubmitButtonText) ? $this->SubmitButtonText : _t('UserDefinedForm.SUBMITBUTTON', 'Submit');
		if($this->Fields()) {
			foreach($this->Fields() as $field) {
			
				$fieldToAdd = $field->getFormField();
				
				if(!$fieldToAdd) break;
				
				$fieldValidationOptions = array();
				
				// Set the Error Messages
				$errorMessage = sprintf(_t('Form.FIELDISREQUIRED').'.', strip_tags("'". ($field->Title ? $field->Title : $field->Name) . "'"));
				$errorMessage = ($field->CustomErrorMessage) ? $field->CustomErrorMessage : $errorMessage;
				$fieldToAdd->setCustomValidationMessage($errorMessage);
				
				// Is this field required
				if($field->Required) {
					$fieldValidation[$field->Name] = $errorMessage;
					$fieldValidationOptions['required'] = true;
					$fieldToAdd->addExtraClass('requiredField');
					if(UserDefinedForm::$required_identifier) {
						$title = $fieldToAdd->Title() ." <span class='requiredIdentifier'>". UserDefinedForm::$required_identifier . "</span>";
						$fieldToAdd->setTitle($title);
					}
				}
				
				// Add field to the form
				$fields->push($fieldToAdd);
				
				// Ask our form field for some more information on hour it should be validated
				$fieldValidationOptions = array_merge($fieldValidationOptions, $field->getValidation());
				
				// Check if we have need to update the global validation
				if($fieldValidationOptions) {
					$fieldValidationRules[$field->Name] = $fieldValidationOptions;
				}
				$fieldId = $field->Name;
				
				if($field->ClassName == 'EditableFormHeading') { 
					$fieldId = 'Form_Form_'.$field->Name;
				}
				
				// Is this Field Show by Default
				if(!$field->ShowOnLoad) {
					$defaults .= "$(\"#" . $fieldId . "\").hide();\n";
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
							}
							
							// watch out for checkboxs as the inputs don't have values but are 'checked
							else if(is_a($formFieldWatch, 'EditableCheckboxGroupField')) {
								$fieldToWatch = "$(\"input[name='".$dependency['ConditionField']."[".$dependency['Value']."]']\")";
							}
							else {
								$fieldToWatch = "$(\"input[name='".$dependency['ConditionField']."']\")";		
							}
							
							// show or hide?
							$view = (isset($dependency['Display']) && $dependency['Display'] == "Hide") ? "hide" : "show";
							$opposite = ($view == "show") ? "hide" : "show";
							
							// what action do we need to keep track of. Something nicer here maybe?
							// @todo encapulsation
							$action = "change";
							
							if($formFieldWatch->ClassName == "EditableTextField" || $formFieldWatch->ClassName == "EditableDateField") {
								$action = "keyup";
							}
							
							// is this field a special option field
							$checkboxField = false;
							if(in_array($formFieldWatch->ClassName, array('EditableCheckboxGroupField', 'EditableCheckbox'))) {
								$action = "click";
								$checkboxField = true;
							}
							
							// and what should we evaluate
							switch($dependency['ConditionOption']) {
								case 'IsNotBlank':
									$expression = ($checkboxField) ? '$(this).attr("checked")' :'$(this).val() != ""';

									break;
								case 'IsBlank':
									$expression = ($checkboxField) ? '!($(this).attr("checked"))' : '$(this).val() == ""';
									
									break;
								case 'HasValue':
									$expression = ($checkboxField) ? '$(this).attr("checked")' : '$(this).val() == "'. $dependency['Value'] .'"';

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
								default:
									$expression = ($checkboxField) ? '!($(this).attr("checked"))' : '$(this).val() != "'. $dependency['Value'] .'"';
								
									break;
							}
							// put it all together
							$CustomDisplayRules .= $fieldToWatch.".$action(function() {
								if(". $expression ." ) {
									$(\"#". $fieldId ."\").".$view."();
								}
								else {
									$(\"#". $fieldId ."\").".$opposite."();
								}
							});";
						}
					}
				}
			}
		}
		$referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
		
		// Keep track of the referer
		$fields->push( new HiddenField( "Referrer", "", $referer ) );
				
		// Build actions
		$actions = new FieldSet(
			new FormAction("process", $this->SubmitButtonText)
		);
		
		// Do we want to add a clear form.
		if($this->ShowClearButton) {
			$actions->push(new ResetFormAction("clearForm"));
		}
		
		// return the form
		$form = new Form($this, "Form", $fields, $actions, new RequiredFields(array_keys($fieldValidation)));
		$form->loadDataFrom($this->failover);
		
		$FormName = $form->FormName();

		// Set the Form Name
		$rules = $this->array2json($fieldValidationRules);
		$messages = $this->array2json($fieldValidation);
		

		// set the custom script for this form
		Requirements::customScript(<<<JS
			(function($) {
				$(document).ready(function() {
					$defaults
					$("#$FormName").validate({
						errorClass: "required",	
						messages:
							$messages
						,
						
						rules: 
						 	$rules
					});
					$CustomDisplayRules
				});
			})(jQuery);
JS
);

		return $form;
	}
	
	/**
	 * Convert a PHP array to a JSON string. We cannot use {@link Convert::array2json}
	 * as it escapes our values with "" which appears to break the validate plugin
	 *
	 * @param Array array to convert
	 * @return JSON 
	 */
	protected function array2json($array) {
		foreach($array as $key => $value)
			if(is_array( $value )) {
				$result[] = "$key:" . $this->array2json($value);
			} else {
				$value = (is_bool($value)) ? $value : "\"$value\"";
				$result[] = "$key:$value \n";
			}
		return (isset($result)) ? "{\n".implode( ', ', $result ) ."} \n": '{}';
	}
	
	/**
	 * Process the form that is submitted through the site
	 * 
	 * @param Array Data
	 * @param Form Form 
	 * @return Redirection
	 */
	function process($data, $form) {
		// submitted form object
		$submittedForm = new SubmittedForm();
		$submittedForm->SubmittedByID = ($id = Member::currentUserID()) ? $id : 0;
		$submittedForm->ParentID = $this->ID;
		$submittedForm->Recipient = $this->EmailTo;
		if(!$this->DisableSaveSubmissions) $submittedForm->write();
		
		// email values
		$values = array();
		$recipientAddresses = array();
		$sendCopy = false;
        $attachments = array();

		$submittedFields = new DataObjectSet();
		
		foreach($this->Fields() as $field) {
			// don't show fields that shouldn't be shown
			if(!$field->showInReports()) continue;
			
			$submittedField = $field->getSubmittedFormField();
			$submittedField->ParentID = $submittedForm->ID;
			$submittedField->Name = $field->Name;
			$submittedField->Title = $field->Title;
					
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
						$upload->loadIntoFile($_FILES[$field->Name], $file);

						// write file to form field
						$submittedField->UploadedFileID = $file->ID;
						
						// Attach the file if its less than 1MB, provide a link if its over.
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

		// email users on submit. All have their own custom options. 
		if($this->EmailRecipients()) {
			$email = new UserDefinedForm_SubmittedFormEmail($submittedFields);                     
			$email->populateTemplate($emailData);
			if($attachments){
				foreach($attachments as $file){
					// bug with double decorated fields, valid ones should have an ID.
					if($file->ID != 0) {
						$email->attachFile($file->Filename,$file->Filename, $file->getFileType());
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
			
		return Director::redirect($this->Link() . 'finished?referrer=' . urlencode($data['Referrer']));
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
			'Form' => ' ',
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
			new TextareaField('EmailBody', 'Body')
		);
		
		if($this->Form()) {
			$validEmailFields = DataObject::get("EditableEmailField", "\"ParentID\" = '$this->FormID'");
			$multiOptionFields = DataObject::get("EditableMultipleOptionField", "\"ParentID\" = '$this->FormID'");
			
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