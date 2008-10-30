<?php
/**
 * Page type that lets users build a contact form.
 * @package cms
 * @subpackage pagetypes
 */
class UserDefinedForm extends Page {
	
	static $add_action = "a contact form";

	static $icon = "cms/images/treeicons/task";
	
	static $need_permission = 'ADMIN';

	static $db = array(
		"EmailTo" => "Varchar",
		"EmailOnSubmit" => "Boolean",
		"SubmitButtonText" => "Varchar",
		"OnCompleteMessage" => "HTMLText"
	);
	
	static $defaults = array(
		"OnCompleteMessage" => "<p>Thanks, we've received your submission.</p>",
	);

	static $has_many = array( 
		"Fields" => "EditableFormField",
		"Submissions" => "SubmittedForm"
	);
	
	protected $fields;

	function getCMSFields($cms) {
		$fields = parent::getCMSFields($cms);

		$fields->addFieldToTab("Root."._t('UserDefinedForm.FORM', 'Form'), new FieldEditor("Fields", 'Fields', "", $this ));
		$fields->addFieldToTab("Root."._t('UserDefinedForm.SUBMISSIONS','Submissions'), new SubmittedFormReportField( "Reports", _t('UserDefinedForm.RECEIVED', 'Received Submissions'), "", $this ) );
		$fields->addFieldToTab("Root.Content."._t('UserDefinedForm.ONCOMPLETE','On complete'), new HtmlEditorField( "OnCompleteMessage", _t('UserDefinedForm.ONCOMPLETELABEL', 'Show on completion'),3,"",_t('UserDefinedForm.ONCOMPLETEMESSAGE', $this->OnCompleteMessage), $this ) );
		
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
 * @package cms
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
				
				HTTP::sendFileToBrowser($csvData, $fileName);		
			} else {
				user_error("'$SQL_ID' is a valid type, but we can't find a UserDefinedForm in the database that matches the ID.", E_USER_ERROR);
			}
		} else {
			user_error("'$SQL_ID' is not a valid UserDefinedForm ID.", E_USER_ERROR);
		}
	}	
	
	function Form() {
		// Build fields
		$fields = new FieldSet();
		$required = array();
        
        if( !$this->SubmitButtonText )
            $this->SubmitButtonText = 'Submit';
		
		foreach( $this->Fields() as $field ) {
			$fields->push( $field->getFormField() );
			if( $field->Required )
				$required[] = $field->Name;
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
	
	function process( $data, $form ) {
		$submittedForm = new SubmittedForm();
		$submittedForm->SubmittedBy = Member::currentUser();
		$submittedForm->ParentID = $this->ID;
		$submittedForm->Recipient = $this->EmailTo;
		$submittedForm->write();
		
		$values = array();
		$recipientAddresses = array();
		$sendCopy = false;
        
        $attachments = array();
		
		$submittedFields = new DataObjectSet();			
		foreach( $this->Fields() as $field ) {
			$submittedField = new SubmittedFormField();
			$submittedField->ParentID = $submittedForm->ID;
			$submittedField->Name = $field->Name;
			$submittedField->Title = $field->Title;
					
			if( $field->hasMethod( 'getValueFromData' ) )
				$submittedField->Value = $field->getValueFromData( $data );
			else
				if(isset($data[$field->Name])) $submittedField->Value = $data[$field->Name];
				
			$submittedField->write();
			$submittedFields->push($submittedField);
			
			if(!empty( $data[$field->Name])){
				// execute the appropriate functionality based on the form field.
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
		
		if( $this->EmailOnSubmit || $sendCopy ) {
			$emailData = array(
				"Recipient" => $this->EmailTo,
				"Sender" => Member::currentUser(),
				"Fields" => $submittedFields,
			);
			
			$email = new UserDefinedForm_SubmittedFormEmail($submittedFields);			
			$email->populateTemplate($emailData);
			$email->setTo( $this->EmailTo );
			$email->setSubject( $this->Title );

			// add attachments to email (<1MB)
			if($attachments){
				foreach($attachments as $file){
					$email->attachFile($filename,$filename);
				}
			}
			
			$email->send();
					
			// send to each of email fields
			foreach( $recipientAddresses as $addr ) {
				$email->setTo( $addr );
				$email->send();
			}
		}
		
		// Redirect to the finished method on this controller with the referrer data
		Director::redirect($this->Link() . 'finished?referrer=' . urlencode($data['Referrer']));
	}

	/**
	 * This action handles rendering the "finished" message
	 * editable in the CMS for a User Defined Form page type.
	 * It should be redirected to after the user submits the
	 * User Defined Form on the front end of the site.
	 *
	 * @return ViewableData
	 */
	function finished() {
		$referrer = isset($_GET['referrer']) ? urldecode($_GET['referrer']) : null;
		
		$custom = $this->customise(array(
			'Content' => $this->customise(
				array(
					'Link' => $referrer
				))->renderWith('ReceivedFormSubmission'),
			'Form' => ' ',
		));
		
		return $custom->renderWith('Page');
	}
	
}

/**
 * Email that gets sent when a submission is made.
 * @package cms
 * @subpackage pagetypes
 */
class UserDefinedForm_SubmittedFormEmail extends Email {
	protected $ss_template = "SubmittedFormEmail";
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
