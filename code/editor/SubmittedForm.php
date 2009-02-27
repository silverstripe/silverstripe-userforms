<?php
/**
 * Contents of an UserDefinedForm submission
 * @package cms
 */
class SubmittedForm extends DataObject {
	static $has_one = array(
		"SubmittedBy" => "Member",
		"Parent" => "UserDefinedForm",
	);
	
	static $db = array(
		"Recipient" => "Varchar(255)"	
	);
	
	static $has_many = array( 
		"FieldValues" => "SubmittedFormField"
	);
	
	function SubmitTime() {
		return $this->Created;
	}
}
?>