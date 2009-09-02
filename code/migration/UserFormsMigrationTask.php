<?php

/**
 * Migration Task for older versions of userforms to the newer version of userforms.
 * Handles the datamodel changes
 *
 * This has been designed to port 0.1 userforms to 0.2 userforms as this had major
 * api changes
 *
 * @todo Finish this off.
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
		$forms = DataObject::get("UserDefinedForm");
		
		if(!$forms) return;
		echo "Proceeding to update ". $forms->Count() . " Forms to ensure proper structure<br />";
		
		foreach($forms as $form) {
			echo " -- Updating  $form->URLSegment <br />";
			// easy step first port over email data from the structure
			if($form->EmailOnSubmit && $form->EmailTo) {
				$emailTo = new UserDefinedForm_EmailRecipient();
				$emailTo->EmailAddress = $form->EmailTo;
				$emailTo->EmailSubject = _t('UserFormsMigrationTask.DEFAULTSUBMISSIONTITLE',"Submission Data");
				$emailTo->EmailFrom = Email::getAdminEmail();
				$emailTo->EmailBody = $form->EmailMessageToSubmitter;
				$emailTo->FormID = $form->ID;
				$emailTo->write();
			}
			
			// now fix all the fields 
			if($form->Fields()) {
				foreach($form->Fields() as $field) {
					switch($field->ClassName) {
						case 'EditableDropdown':
						case 'EditableRadioField':
						case 'EditableCheckboxGroupField':
							
							$optionClass = "EditableDropdownOption";
							if($field->ClassName = "EditableRadioField") {
								$optionClass = "EditableRadioOption";
							}
							else if($field->ClassName = "EditableCheckboxGroupField") {
								$optionClass = "EditableCheckboxOption";
							}
							
							$query = DB::query("SELECT * FROM $optionClass WHERE ParentID = '$field->ID'");
							$result = $query->first();
							if($result) {
								do {
									$this->createOption($result, $optionClass);
								} while($result = $query->next());
							}

							break;
						case 'EditableTextField':
							
							// find what table to use
							$database = $this->findDatabaseTableName('EditableTextField');
							
							// get the data from the table
							$result = DB::query("SELECT * FROM $database WHERE ID = $field->ID")->first();
							
							if($result) {
								$field->setFieldSettings(array(
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
								$result = DB::query("SELECT * FROM $database WHERE ID = $field->ID")->first();
								
								if($result) {
									$field->setFieldSettings(array(
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
								$result = DB::query("SELECT * FROM $database WHERE ID = $field->ID")->first();
								
								if($result) {
									$field->setFieldSettings(array(
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
								$result = DB::query("SELECT * FROM $database WHERE ID = $field->ID")->first();
								
								if($result) {
									$field->setFieldSettings(array(
										'Default' => $result['Checked']
									));
								}
							}
							break;
					}
					$field->write();
				}
			}
		}
	}
	/**
	 * Find if this table is obsolete or used
	 *
	 */
	function findDatabaseTableName($tableName) {
		$table = DB::query("SHOW TABLES LIKE '$tableName'")->value();
		if(!$table) {
			$table = DB::query("SHOW TABLES LIKE '_obsolete_EditableTextField'")->value();
		}
		return $table;
	}
	/**
	 * Create a EditableOption from a whatever type of multi
	 * form field it is coming from
	 */
	function createOption($option, $class) {
		$editableOption = new EditableOption();
		$editableOption->ParentID = $option['ParentID'];
		$editableOption->populateFromPostData($option);
		// log
		echo " -- -- Created new option '$editableOption->Title'<br />";
	}
}
?>