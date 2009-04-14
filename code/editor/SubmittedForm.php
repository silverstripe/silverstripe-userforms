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
	
	static $has_many = array( 
		"FieldValues" => "SubmittedFormField"
	);

	/**
	 * Return the Link to DeleteLink
	 *
	 * @return String
	 */
	public function DeleteLink() {
		return $this->Parent()->Link().'deletesubmission/'.$this->ID;
	}
	
	function SubmitTime() {
		return $this->Created;
	}
}
?>