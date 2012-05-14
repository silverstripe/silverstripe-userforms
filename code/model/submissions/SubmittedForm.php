<?php
/**
 * Contents of an UserDefinedForm submission
 *
 * @package userforms
 */

class SubmittedForm extends DataObject {
	
	public static $has_one = array(
		"SubmittedBy" => "Member",
		"Parent" => "UserDefinedForm",
	);
	
	public static $has_many = array( 
		"Values" => "SubmittedFormField"
	);
	
	/**
	 * Before we delete this form make sure we delete all the
	 * field values so that we don't leave old data round
	 *
	 */
	protected function onBeforeDelete() {
		
		if($this->Values()) {
			foreach($this->Values() as $value) {
				$value->delete();
			}
		}
		
		parent::onBeforeDelete();
	}
}