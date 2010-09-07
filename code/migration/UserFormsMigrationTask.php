<?php

/**
 * Migration Task for older versions of userforms to the newer version of userforms.
 * This will handle the datamodel changes of the form page as well as the form fields.
 * Nothing is done with Submissions as the datamodel for that has not changed
 *
 * This has been designed to port 0.1 userforms to 0.2 userforms as this had major
 * api changes
 *
 * You can import 1 form at a time by entering the formID in the URL
 * 		- /dev/tasks/UserFormsMigrationTask/?formID=12
 *
 * You can also run this without writing anything to the database - by doing a dryrun
 * 		- /dev/tasks/UserFormsMigrationTask/?dryRun=1
 * 
 * @package userforms
 */

class UserFormsMigrationTask extends MigrationTask {

	protected $title = "UserForms Database Migration";

	protected $description = "Upgrades your current forms to the latest structure";

	/**
	 * Run the update.
	 *
	 * Things it needs to do:
	 * 		- Port the Settings on Individual Fields
	 *		- Create the new class for multiple options
	 *		- Port Email To to New Email_Recipients
	 * 
	 * @param HTTPRequest
	 */
	function run($request) {
		// load the forms
		$forms = DataObject::get('UserDefinedForm');
		if(!$forms) $forms = new DataObjectSet();
		
		// set debugging / useful test
		$this->dryRun = (isset($_GET['dryRun'])) ? true : false;
		
		if($this->dryRun) {
			echo "Will be running this test as a dry run. No data will be added or removed.<br />";
		}
		
		// if they want to import just 1 form - eg for testing
		if(isset($_GET['formID'])) {
			$id = Convert::raw2sql($_GET['formID']);
			$forms->push(DataObject::get_by_id('UserDefinedForm', $id));
		}
		
		if(!$forms) {
			echo "No UserForms Found on Database";
			return;
		}
		
		echo "Proceeding to update ". $forms->Count() . " Forms<br />";
		
		foreach($forms as $form) {
			echo " -- Updating  $form->URLSegment <br />";
			// easy step first port over email data from the structure
			if($form->EmailOnSubmit && $form->EmailTo) {
				// EmailTo can be a comma separated list so we need to explode that
				$emails = explode(",", $form->EmailTo);
				if($emails) {
					foreach($emails as $email) {
						$emailTo = new UserDefinedForm_EmailRecipient();
						$emailTo->EmailAddress = trim($email);
						$emailTo->EmailSubject = ($form) ? $form->Title : _t('UserFormsMigrationTask.DEFAULTSUBMISSIONTITLE',"Submission Data");
						$emailTo->EmailFrom = Email::getAdminEmail();
						$emailTo->FormID = $form->ID;
						echo " -- -- Created new Email Recipient  $email<br />";
						if(!$this->dryRun) $emailTo->write();
					}
				}
			}
			// now fix all the fields 
			if($form->Fields()) {
				foreach($form->Fields() as $field) {
					switch($field->ClassName) {
						case 'EditableDropdown':
						case 'EditableRadioField':
						case 'EditableCheckboxGroupField':
							$optionClass = "EditableDropdownOption";
							if($field->ClassName == "EditableRadioField") {
								$optionClass = "EditableRadioOption";
							}
							else if($field->ClassName == "EditableCheckboxGroupField") {
								$optionClass = "EditableCheckboxOption";
							}
							$query = DB::query("SELECT * FROM \"$optionClass\" WHERE \"ParentID\" = '$field->ID'");
							$result = $query->first();
							if($result) {
								do {
									$this->createOption($result, $optionClass);
								} while($result = $query->next());
							}

							break;
							
						case 'EditableTextField':
							$database = $this->findDatabaseTableName('EditableTextField');
							
							// get the data from the table
							$result = DB::query("SELECT * FROM \"$database\" WHERE \"ID\" = $field->ID")->first();
							
							if($result) {
								$field->setSettings(array(
									'Size' => $result['Size'],
									'MinLength' => $result['MinLength'],
									'MaxLength' => $result['MaxLength'],
									'Rows' => $result['Rows']
								));
							}

							break;
							
						case 'EditableLiteralField':
							if($field->Content) {
								// find what table to use
								$database = $this->findDatabaseTableName('EditableLiteralField');

								// get the data from the table
								$result = DB::query("SELECT * FROM \"$database\" WHERE \"ID\" = $field->ID")->first();
								
								if($result) {
									$field->setSettings(array(
										'Content' => $result['Content']
									));
								}
							}
							break;
							
						case 'EditableMemberListField':
							if($field->GroupID) {
								// find what table to use
								$database = $this->findDatabaseTableName('EditableMemberListField');

								// get the data from the table
								$result = DB::query("SELECT * FROM \"$database\" WHERE \"ID\" = $field->ID")->first();
								
								if($result) {
									$field->setSettings(array(
										'GroupID' => $result['GroupID']
									));
								}
							}
							break;
							
						case 'EditableCheckbox':		
							if($field->Checked) {
								// find what table to use
								$database = $this->findDatabaseTableName('EditableCheckbox');

								// get the data from the table
								$result = DB::query("SELECT * FROM \"$database\" WHERE \"ID\" = $field->ID")->first();
								
								if($result) {
									$field->setSettings(array(
										'Default' => $result['Checked']
									));
								}
							}
							break;
							
						case 'EditableEmailField': 
							$database = $this->findDatabaseTableName('EditableEmailField');
							$result = DB::query("SELECT * FROM \"$database\" WHERE \"ID\" = $field->ID")->first();
							if($result && isset($result['SendCopy']) && $result['SendCopy'] == true) {
								// we do not store send copy on email field anymore. This has been wrapped into
								// the email recipients
								$emailTo = new UserDefinedForm_EmailRecipient();
								$emailTo->EmailSubject = ($form) ? $form->Title : _t('UserFormsMigrationTask.DEFAULTSUBMISSIONTITLE',"Submission Data");
								$emailTo->EmailFrom = Email::getAdminEmail();
								$emailTo->FormID = $form->ID;
								$emailTo->SendEmailToFieldID = $field->ID;
								$emailTo->EmailBody = $form->EmailMessageToSubmitter;
								if(!$this->dryRun) $emailTo->write();
							}
							break;
					}
					if(!$this->dryRun) $field->write();
				}
			}
		}
		echo "<h3>Migration Complete</h3>";
	}
	/**
	 * Find if this table is obsolete or used
	 *
	 */
	function findDatabaseTableName($tableName) {
		$exist = DB::tableList();
 		if(!empty($exist)) {
			if(array_search($tableName, $exist) !== false) return $tableName;
			$tableName = "_obsolete_$tableName";
 			if(array_search($tableName, $exist) !== false) return $tableName;
		}
		echo '<strong>!! Could Not Find '.$tableName;
		return;
	}

	/**
	 * Create a EditableOption from a whatever type of multi
	 * form field it is coming from
	 */
	function createOption($option, $class) {
		
		$editableOption = new EditableOption();
		$editableOption->ParentID = $option['ParentID'];
		if(!$this->dryRun) $editableOption->populateFromPostData($option);
		// log
		echo " -- -- Created new option $editableOption->Title<br />";
	}
}