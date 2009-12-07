<?php
/**
 * Contents of an UserDefinedForm submission
 *
 * @package userforms
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
	 * Before we delete this form make sure we delete all the
	 * field values so that we don't leave old data round
	 *
	 */
	protected function onBeforeDelete() {
		if($this->FieldValues()) {
			foreach($this->FieldValues() as $value) {
				$value->delete();
			}
		}
		parent::onBeforeDelete();
	}
}