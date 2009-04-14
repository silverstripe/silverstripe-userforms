<?php
/**
 * User Defined Form Page type that lets users build a form in the CMS 
 * using the FieldEditor Field. 
 * 
 * @package userforms
 */

class UserDefinedForm extends Page {
	
	/**
	 * Add Action in the CMS
	 *
	 * @var String
	 */
	static $add_action = "A User Form";

	/**
	 * Icon for the User Defined Form in the CMS. Without the extension
	 * or the -file
	 *
	 * @var String
	 */
	static $icon = "cms/images/treeicons/task";
	
	/**
	 * What level permission is needed to edit / add 
	 * this page type
	 *
	 * @var String
	 */
	static $need_permission = 'ADMIN';

	/**
	 * Fields on the user defined form page. 
	 * 
	 * @var Array
	 */
	static $db = array(
		"EmailOnSubmit" => "Boolean",
		"EmailOnSubmitSubject" => "Varchar(200)",
		"SubmitButtonText" => "Varchar",
		"OnCompleteMessage" => "HTMLText",
	);
	
	/**
	 * Default values of variables when this page is created
	 * in the CMS
	 * 
	 * @var Array
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
	
	protected $fields;

	/**
	 * Setup the CMS Fields for the User Defined Form
	 * 
	 * @return FieldSet
	 */
	function getCMSFields() {
		$fields = parent::getCMSFields();

		// field editor
		$fields->addFieldToTab("Root."._t('UserDefinedForm.FORM', 'Form'), new FieldEditor("Fields", 'Fields', "", $this ));
		
		// view the submissions
		$fields->addFieldToTab("Root."._t('UserDefinedForm.SUBMISSIONS','Submissions'), new SubmittedFormReportField( "Reports", _t('UserDefinedForm.RECEIVED', 'Received Submissions'), "", $this ) );
		
		// who do we email on submission
		$emailRecipients = new HasManyComplexTableField($this,
	    	'EmailRecipients',
	    	'UserDefinedForm_EmailRecipient',
	    	array(
				'EmailAddress' => 'Email',
				'EmailSubject' => 'Subject',
				'EmailFrom' => 'From'
	    	),
	    	'getCMSFields_forPopup'
	    );
		$emailRecipients->setAddTitle(_t('UserDefinedForm.AEMAILRECIPIENT', 'A Email Recipient'));
		$fields->addFieldToTab("Root."._t('UserDefinedForm.EMAILRECIPIENTS', 'Email Recipients'), $emailRecipients);
	
		// text to show on complete
		$onCompleteFieldSet = new FieldSet(
			new HtmlEditorField( "OnCompleteMessage", _t('UserDefinedForm.ONCOMPLETELABEL', 'Show on completion'),3,"",_t('UserDefinedForm.ONCOMPLETEMESSAGE', $this->OnCompleteMessage), $this ),
			new TextField("EmailOnSubmitSubject", _t('UserDefinedForm.ONSUBMITSUBJECT', 'Email Subject')),
			new HtmlEditorField( "EmailMessageToSubmitter", _t('UserDefinedForm.EMAILMESSAGETOSUBMITTER', 'Email message to submitter'),3,"",_t('UserDefinedForm.EMAILMESSAGETOSUBMITTER', $this->EmailMessageToSubmitter), $this )
		);
		
		$fields->addFieldsToTab("Root.Content."._t('UserDefinedForm.ONCOMPLETE','On complete'), $onCompleteFieldSet);
		
		return $fields;
	}
	
	function FilterForm() {
		// Build fields
		$fields = new FieldSet();
		$required = array();
		
		foreach( $this->Fields() as $field ) {
			$fields->push( $field->getFilterField() );
		}
		
		// Build actions
		$actions = new FieldSet( 
			new FormAction( "filter", _t('UserDefinedForm.SUBMIT', 'Submit') )
		);
		
		// set the name of the form
		return new Form( $this, "Form", $fields, $actions );
	}
	
	/**
	 * Filter the submissions by the given criteria
	 */
	function filter( $data, $form ) {
		
		$filterClause = array( "`SubmittedForm`.`ParentID` = '{$this->ID}'" );
		
		$keywords = preg_split( '/\s+/', $data['FilterKeyword'] );
		
		$keywordClauses = array();
		
		// combine all keywords into one clause
		foreach( $keywords as $keyword ) {
		
			// escape %, \ and _ in the keyword. These have special meanings in a LIKE string
			$keyword = preg_replace( '/([%_])/', '\\\\1', addslashes( $keyword ) );
			
			$keywordClauses[] = "`Value` LIKE '%$keyword%'";	
		}
		
		if( count( $keywordClauses ) > 0 ) {
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
    
  function delete() {
      // remove all the fields associated with this page
      foreach( $this->Fields() as $field )
          $field->delete();
          
      parent::delete();   
  }
  
  	public function customFormActions( $isReadonly = false ) {
          return new FieldSet( new TextField( "SubmitButtonText", _t('UserDefinedForm.TEXTONSUBMIT', 'Text on submit button:'), $this->SubmitButtonText ) );
	}
	
	/**
	 * Duplicate this UserDefinedForm page, and its form fields.
	 * Submissions, on the other hand, won't be duplicated.
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
		Requirements::javascript(THIRDPARTY_DIR . 'jsparty/prototype-safe.js');
		Requirements::javascript(THIRDPARTY_DIR . 'jsparty/behaviour.js');
		
		parent::init();
	}
	
	/**
	 * Export each of the form submissions for this UserDefinedForm
	 * instance into a CSV file.
	 * 
	 * In order to run this export function, the user must be
	 * able to edit the page, so we check canEdit()
	 *
	 * @return HTTPResponse / bool
	 */
	function export() {
		if(!$this->canEdit()) return false;

		$now = Date("Y-m-d_h.i.s");
		$fileName = "export-$now.csv";
		$separator = ",";
		
		// Get the UserDefinedForm to export data from the URL
		$SQL_ID = Convert::raw2sql(Director::urlParam('ID'));

		if($SQL_ID) {
			$udf = DataObject::get_by_id("UserDefinedForm", $SQL_ID);
			if($udf) {
				$submissions = $udf->Submissions();
				if($submissions && $submissions->Count() > 0) {
					
					// Get all the submission IDs (so we know what names/titles to get - helps for sites with many UDF's)
					$inClause = array();
					foreach($submissions as $submission) {
						$inClause[] = $submission->ID;
					}

					// Get the CSV header rows from the database
					$tmp = DB::query("SELECT DISTINCT Name, Title FROM SubmittedFormField LEFT JOIN SubmittedForm ON SubmittedForm.ID = SubmittedFormField.ParentID WHERE SubmittedFormField.ParentID IN (" . implode(',', $inClause) . ")");
					
					// Sort the Names and Titles from the database query into separate keyed arrays
					foreach($tmp as $array) {
						$csvHeaderNames[] = $array['Name'];
						$csvHeaderTitle[] = $array['Title'];
					}

					// For every submission...
					$i = 0;
					foreach($submissions as $submission) {
						
						// Get the rows for this submission (One row = one form field)
						$dataRow = $submission->FieldValues();
						$rows[$i] = array();
						
						// For every row/field, get all the columns
						foreach($dataRow as $column) {
							
							// If the Name of this field is in the $csvHeaderNames array, get an array of all the places it exists
							if($index = array_keys($csvHeaderNames, $column->Name)) {
								if(is_array($index)) {
									
									// Set the final output array for each index that we want to insert this value into
									foreach($index as $idx) {
										$rows[$i][$idx] = $column->Value;
									}
								}
							}
						}
						
						$i++;
					}
					
					// CSV header row
					$csvData = '"' . implode('","', $csvHeaderTitle) . '"' . "\n";

					// For every row of data (one form submission = one row)
					foreach($rows as $row) {
						
						// Loop over all the names we can use
						for($i=0;$i<count($csvHeaderNames);$i++) {
							if(!$row[$i]) $csvData .= '"",';    // If there is no data for this column, output it as blank instead
							else $csvData .= '"'.$row[$i].'",'; // Otherwise, output the value for this column
						}
						// Start a new row for each submission
						$csvData .= "\n";
					}
				} else {
					user_error("No submissions to export.", E_USER_ERROR);
				}
				
				HTTPRequest::send_file($csvData, $fileName)->output();		
			} else {
				user_error("'$SQL_ID' is a valid type, but we can't find a UserDefinedForm in the database that matches the ID.", E_USER_ERROR);
			}
		} else {
			user_error("'$SQL_ID' is not a valid UserDefinedForm ID.", E_USER_ERROR);
		}
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
            $this->SubmitButtonText = 'Submit';
		}
		foreach($this->Fields() as $field) {
			$fields->push($field->getFormField());
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
			new FormAction( "process", $this->SubmitButtonText )
		);
		
		// set the name of the form
		$form = new Form( $this, "Form", $fields, $actions, new RequiredFields( $required ) );
		$form->loadDataFrom($this->failover);

		return $form;
	}	
	
	function ReportFilterForm() {
		return new SubmittedFormReportField_FilterForm( $this, 'ReportFilterForm' );
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
			
			if(!empty( $data[$field->Name])){

				switch($field->ClassName){
					
					case "EditableEmailField" : 
						if($field->SendCopy){
							$recipientAddresses[] = $data[$field->Name];
							$sendCopy = true;
							$values[$field->Title] = '<a style="white-space: nowrap" href="mailto:'.$data[$field->Name].'">'.$data[$field->Name].'</a>';
						}	
					break;
					
					case "EditableFileField" :
						
						// Returns a file type which we attach to the email. 
						$submittedfile = $field->createSubmittedField($data[$field->Name], $submittedForm);
						$file = $submittedfile->UploadedFile();
									
						$filename = $file->getFilename();
										
						// Attach the file if its less than 1MB, provide a link if its over.
						if($file->getAbsoluteSize() < 1024*1024*1){
							$attachments[] = $file;
						}
						
						// Always provide the link if present.
						if($file->ID) {
							$submittedField->Value = $values[$field->Title] = "<a href=\"". $filename ."\" title=\"". Director::absoluteBaseURL(). $filename. "\">Uploaded to: ". Director::absoluteBaseURL(). $filename . "</a>";
						} else {
							$submittedField->Value = $values[$field->Title] = "";
						}
								
					break;						
				}
				
			}elseif( $field->hasMethod( 'getValueFromData' ) ) {
				$values[$field->Title] = Convert::linkIfMatch($field->getValueFromData( $data ));
			
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
					$email->attachFile($filename,$filename);
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
		'EmailBody' => 'HTMLText'
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
		$fields = new FieldSet(
			new TextField('EmailSubject', _t('UserDefinedForm.EMAILSUBJECT', 'Email Subject')),
			new TextField('EmailFrom', _t('UserDefinedForm.FROMADDRESS','From Address')),
			new TextField('EmailAddress', _t('UserDefinedForm.SENDEMAILTO','Send Email To'))
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
	
	function __construct($values) {
	        $this->subject = _t('UserDefinedForm_SubmittedFormEmail.EMAILSUBJECT', 'Submission of form');
		parent::__construct();
		
		$this->data = $values;
	}
	
	function Data() {
		return $this->data;
	}
}

?>
