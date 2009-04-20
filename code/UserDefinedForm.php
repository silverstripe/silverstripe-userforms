<?php
/**
 * User Defined Form Page type that lets users build a form in the CMS 
 * using the FieldEditor Field. 
 * 
 * @package userforms
 */

class UserDefinedForm extends Page {
	
	/**
	 * @var String Add Action in the CMS
	 */
	static $add_action = "A User Form";

	/**
	 * @var String Icon for the User Defined Form in the CMS. Without the extension
	 */
	static $icon = "cms/images/treeicons/task";
	
	/**
	 * @var String What level permission is needed to edit / add 
	 */
	static $need_permission = 'ADMIN';

	/**
	 * @var Array Fields on the user defined form page. 
	 */
	static $db = array(
		"EmailOnSubmit" => "Boolean",
		"EmailOnSubmitSubject" => "Varchar(200)",
		"SubmitButtonText" => "Varchar",
		"OnCompleteMessage" => "HTMLText",
		"ShowClearButton" => "Boolean"
	);
	
	/**
	 * @var Array Default values of variables when this page is created
	 */ 
	static $defaults = array(
		'Content' => '$UserDefinedForm',
		'OnCompleteMessage' => '<p>Thanks, we\'ve received your submission.</p>'
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

		// field editor
		$fields->addFieldToTab("Root.Content."._t('UserDefinedForm.FORM', 'Form'), new FieldEditor("Fields", 'Fields', "", $this ));
		
		// view the submissions
		$fields->addFieldToTab("Root.Content."._t('UserDefinedForm.SUBMISSIONS','Submissions'), new SubmittedFormReportField( "Reports", _t('UserDefinedForm.RECEIVED', 'Received Submissions'), "", $this ) );

		// who do we email on submission
		$emailRecipients = new HasManyComplexTableField($this,
	    	'EmailRecipients',
	    	'UserDefinedForm_EmailRecipient',
	    	array(
				'EmailAddress' => 'Email',
				'EmailSubject' => 'Subject',
				'EmailFrom' => 'From'
	    	),
	    	'getCMSFields_forPopup',
			"FormID = '$this->ID'"
	    );
		$emailRecipients->setAddTitle(_t('UserDefinedForm.AEMAILRECIPIENT', 'A Email Recipient'));
		$fields->addFieldToTab("Root.Content."._t('UserDefinedForm.EMAILRECIPIENTS', 'Email Recipients'), $emailRecipients);
	
		// text to show on complete
		$onCompleteFieldSet = new FieldSet(
			new HtmlEditorField( "OnCompleteMessage", _t('UserDefinedForm.ONCOMPLETELABEL', 'Show on completion'),3,"",_t('UserDefinedForm.ONCOMPLETEMESSAGE', $this->OnCompleteMessage), $this ),
			new TextField("EmailOnSubmitSubject", _t('UserDefinedForm.ONSUBMITSUBJECT', 'Email Subject')),
			new HtmlEditorField( "EmailMessageToSubmitter", _t('UserDefinedForm.EMAILMESSAGETOSUBMITTER', 'Email message to submitter'),3,"",_t('UserDefinedForm.EMAILMESSAGETOSUBMITTER', $this->EmailMessageToSubmitter), $this )
		);
		
		$fields->addFieldsToTab("Root.Content."._t('UserDefinedForm.ONCOMPLETE','On complete'), $onCompleteFieldSet);
		
		return $fields;
	}
	
	/**
	 * Filter the Submissions page form
	 * 
	 * @return Form
	 */
	public function FilterForm() {

		$fields = new FieldSet();
		$required = array();
		
		foreach($this->Fields() as $field) {
			$fields->push($field->getFilterField());
		}
		
		$actions = new FieldSet( 
			new FormAction("filter", _t('UserDefinedForm.SUBMIT', 'Submit'))
		);
		
		return new Form( $this, "Form", $fields, $actions );
	}
	
	/**
	 * Filter the submissions by the given criteria
	 *
	 * @param Array the filter data
	 * @param Form the form used
	 * @return Array|String
	 */
	public function filter( $data, $form ) {
		
		$filterClause = array( "`SubmittedForm`.`ParentID` = '{$this->ID}'" );	
		$keywords = preg_split( '/\s+/', $data['FilterKeyword'] );	
		$keywordClauses = array();
		
		// combine all keywords into one clause
		foreach($keywords as $keyword) {
		
			// escape %, \ and _ in the keyword. These have special meanings in a LIKE string
			$keyword = preg_replace( '/([%_])/', '\\\\1', addslashes( $keyword ) );
			$keywordClauses[] = "`Value` LIKE '%$keyword%'";	
		}
		
		if(count($keywordClauses) > 0) {
			$filterClause[] = "( " . implode( ' OR ', $keywordClauses ) . ")";
			$searchQuery = 'keywords \'' . implode( "', '", $keywords ) . '\' ';
		}
		
		$fromDate = addslashes( $data['FilterFromDate'] );
		$toDate = addslashes( $data['FilterToDate'] );
		
		// use date objects to convert date to value expected by database
		if( ereg('^([0-9]+)/([0-9]+)/([0-9]+)$', $fromDate, $parts) )
			$fromDate = $parts[3] . '-' . $parts[2] . '-' . $parts[1];
			
		if( ereg('^([0-9]+)/([0-9]+)/([0-9]+)$', $toDate, $parts) )
			$toDate = $parts[3] . '-' . $parts[2] . '-' . $parts[1];
			
		if( $fromDate ) {
			$filterClause[] = "`SubmittedForm`.`Created` >= '$fromDate'";
			$searchQuery .= 'from ' . $fromDate . ' ';
		}
			
		if( $toDate ) {
			$filterClause[] = "`SubmittedForm`.`Created` <= '$toDate'";
			$searchQuery .= 'to ' . $toDate;
		}
		
		$submittedValues = DataObject::get( 'SubmittedFormField', implode( ' AND ', $filterClause ), "", "INNER JOIN `SubmittedForm` ON `SubmittedFormField`.`ParentID`=`SubmittedForm`.`ID`" );
	
		if( !$submittedValues || $submittedValues->Count() == 0 )
		        return _t('UserDefinedForm.NORESULTS', 'No matching results found');
			
		$submissions = $submittedValues->groupWithParents( 'ParentID', 'SubmittedForm' );
		
		if( !$submissions || $submissions->Count() == 0 )
		        return _t('UserDefinedForm.NORESULTS', 'No matching results found');
		
		return $submissions->customise( 
			array( 'Submissions' => $submissions )
		)->renderWith( 'SubmittedFormReportField_Reports' );
	}
	
	function ReportFilterForm() {
		return new SubmittedFormReportField_FilterForm( $this, 'ReportFilterForm' );
	}
	
	/**
	 * Called on before delete remove all the fields from the database
	 */
  	public function delete() {
		foreach($this->Fields() as $field) {
			$field->delete();
		}
		parent::delete();   
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
	 * Duplicate this UserDefinedForm page, and its form fields.
	 * Submissions, on the other hand, won't be duplicated.
	 *
	 * @return Page
	 */
	public function duplicate() {
		$page = parent::duplicate();
		foreach($this->Fields() as $field) {
			$newField = $field->duplicate();
			$newField->ParentID = $page->ID;
			$newField->write();
		}
		return $page;
	}
}

/**
 * Controller for the {@link UserDefinedForm} page type.
 *
 * @package userform
 * @subpackage pagetypes
 */

class UserDefinedForm_Controller extends Page_Controller {
	
	function init() {
		Requirements::javascript(THIRDPARTY_DIR . '/prototype.js');
		Requirements::javascript(THIRDPARTY_DIR . '/behaviour.js');
		parent::init();
	}
	
	/**
	 * Using $UserDefinedForm in the Content area of the page shows
	 * where the form should be rendered into. If it does not exist
	 * then default back to $Form
	 *
	 * @return Array
	 */
	public function index() {
		if($this->Content && $this->Form()) {
			$hasLocation = stristr($this->Content, '$UserDefinedForm');
			if($hasLocation) {
				$content = str_ireplace('$UserDefinedForm', $this->Form()->forTemplate(), $this->Content);
				return array(
					'Content' => $content,
					'Form' => ""
				);
			}
		}
		return array(
			'Content' => $this->Content,
			'Form' => $this->Form
		);
	}
	
	function ReportFilterForm() {
		return new SubmittedFormReportField_FilterForm( $this, 'ReportFilterForm' );
	}
	
	/**
	 * User Defined Form. Feature of the user defined form is if you want the
	 * form to appear in a custom location on the page you can use $UserDefinedForm 
	 * in the content area to describe where you want the form
	 *
	 * @return Form
	 */
	function Form() {
		// Build fields
		$fields = new FieldSet();
		$required = array();
        
        if(!$this->SubmitButtonText) {
            $this->SubmitButtonText =  _t('UserDefinedForm.SUBMITBUTTON', 'Submit');
		}
		foreach($this->Fields() as $field) {
			$fieldToAdd = $field->getFormField();
			if($field->CustomErrorMessage) {				
				$fieldToAdd->setCustomValidationMessage($field->CustomErrorMessage);
			}
			
			$fields->push($fieldToAdd);
			if($field->Required) {
				$required[] = $field->Name;	
			}
		}
		
		if(!isset($_SERVER['HTTP_REFERER'])) {
			$_SERVER['HTTP_REFERER'] = "";
		}
		
		$referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
		$fields->push( new HiddenField( "Referrer", "", $referer ) );
				
		// Build actions
		$actions = new FieldSet(
			new FormAction("process", $this->SubmitButtonText)
		);
		
		// Do we want to add a clear form.
		if($this->ShowClearButton) {
			$actions->push(new ResetFormAction("clearForm"));
		}
		
		$form = new Form( $this, "Form", $fields, $actions, new RequiredFields($required));
		$form->loadDataFrom($this->failover);

		return $form;
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
		$submittedForm->SubmittedBy = Member::currentUser();
		$submittedForm->ParentID = $this->ID;
		$submittedForm->Recipient = $this->EmailTo;
		$submittedForm->write();
		
		// email values
		$values = array();
		$recipientAddresses = array();
		$sendCopy = false;
        $attachments = array();

		$submittedFields = new DataObjectSet();
		
		foreach($this->Fields() as $field) {
			$submittedField = new SubmittedFormField();
			$submittedField->ParentID = $submittedForm->ID;
			$submittedField->Name = $field->Name;
			$submittedField->Title = $field->Title;
					
			if($field->hasMethod( 'getValueFromData' )) {
				$submittedField->Value = $field->getValueFromData( $data );
			}
			else {
				if(isset($data[$field->Name])) $submittedField->Value = $data[$field->Name];
			}
			
			$submittedField->write();
			$submittedFields->push($submittedField);

			if(!empty($data[$field->Name])){

				switch($field->ClassName){	
					case "EditableEmailField" : 
						if($field->SendCopy){
							$recipientAddresses[] = $data[$field->Name];
							$sendCopy = true;
							$values[$field->Title] = '<a style="white-space: nowrap" href="mailto:'.$data[$field->Name].'">'.$data[$field->Name].'</a>';
						}	
					break;
					
					case "EditableFileField" :
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

							// Always provide the link if present.
							if($file->ID) {
								$submittedField->Value = "<a href=\"". $file->getFilename() ."\" title=\"". $file->getFilename() . "\">". $file->Title . "</a>";
							} else {
								$submittedField->Value = "";
							}
							$submittedField->write();
						}				
					break;						
				}
			}
			elseif($field->hasMethod('getValueFromData')) { 
				$values[$field->Title] = Convert::linkIfMatch($field->getValueFromData($data)); 
			} else { 
				if(isset($data[$field->Name])) $values[$field->Title] = Convert::linkIfMatch($data[$field->Name]); 
			}
		}	
		$emailData = array(
			"Sender" => Member::currentUser(),
			"Fields" => $submittedFields,
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
				$email->populateTemplate($emailData);
				$email->setFrom($recipient->EmailFrom);
				$email->setBody($recipient->EmailBody);
				$email->setSubject($recipient->EmailSubject);
				$email->setTo($recipient->EmailAddress);
				// check to see if they are a dynamic recipient. eg based on a field
				// a user selected
				if($recipient->SendEmailFromFieldID) {
					$name = Convert::raw2sql($recipient->SendEmailFromField()->Name);
					$SubmittedFormField = DataObject::get_one("SubmittedFormField", "Name = '$name' AND ParentID = '$submittedForm->ID'");
					if($SubmittedFormField) {
						$email->setTo($SubmittedFormField->Value);	
					}
				}
				$email->send();
			}
		}
		
		// send a copy to the author of the form
		if($sendCopy) {
			$emailToSubmiter = new UserDefinedForm_SubmittedFormEmailToSubmitter($submittedFields);
			$emailToSubmiter->setSubject($this->EmailOnSubmitSubject);
			
			foreach($recipientAddresses as $addr) {
				$emailToSubmiter->setBody($this->EmailMessageToSubmitter);
				$emailToSubmiter->setTo($addr);
				$emailToSubmiter->send();
			}
		}
		
		// Redirect to the finished method on this controller with the referrer data
		Director::redirect($this->Link() . 'finished?referrer=' . urlencode($data['Referrer']));
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
		
		$templateData = $this->customise(array(
			'Content' => $this->customise(
				array(
					'Link' => $referrer
				))->renderWith('ReceivedFormSubmission'),
			'Form' => ' ',
		));
		
		return $templateData;
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
		'EmailBody' => 'Text'
	);
	
	static $has_one = array(
		'Form' => 'UserDefinedForm',
		'SendEmailFromField' => 'EditableFormField'
	);
	
	/**
	 * Return the fields to edit this email. 
	 *
	 * @return FieldSet
	 */
	public function getCMSFields_forPopup() {
		$forms = DataObject::get("UserDefinedForm");
		if($forms) $forms = $forms->toDropdownMap('ID', 'Title');
		$fields = new FieldSet(
			new TextField('EmailSubject', _t('UserDefinedForm.EMAILSUBJECT', 'Email Subject')),
			new TextField('EmailFrom', _t('UserDefinedForm.FROMADDRESS','From Address')),
			new TextField('EmailAddress', _t('UserDefinedForm.SENDEMAILTO','Send Email To')),
			new DropdownField('FormID', 'Form', $forms)
		);
		
		if($this->Form()) {
			$validEmailFields = DataObject::get("EditableFormField", "ParentID = '$this->FormID'");
			
			if($validEmailFields) {
				$validEmailFields = $validEmailFields->toDropdownMap('ID', 'Title');
				$fields->push(new DropdownField('SendEmailFromFieldID', _t('UserDefinedForm.SENDEMAILINSTEAD', 'Send Email Instead To'),$validEmailFields, '', null, 'Use Fixed Email'));
			}
		}
		$fields->push(new HTMLEditorField('EmailBody', 'Body'));
		return $fields;
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

/**
 * Email that gets sent to submitter when a submission is made.
 *
 * @package userforms
 */
class UserDefinedForm_SubmittedFormEmailToSubmitter extends Email {
	protected $ss_template = "SubmittedFormEmailToSubmitter";
	protected $from = '$Sender.Email';
	protected $to = '$Recipient.Email';
	protected $subject = 'Submission of form';
	protected $data;
	
	function __construct($values = null) {
		$this->subject = _t('UserDefinedForm_SubmittedFormEmail.EMAILSUBJECT', 'Submission of form');
		
		parent::__construct();
		$this->data = $values;
	}
	
	function Data() {
		return $this->data;
	}
}

?>
