<?php
/**
 * EditableDateField
 *
 * Allows a user to add a date field to the Field Editor
 *
 * @package userforms
 */

class EditableDateField extends EditableFormField {
	
	static $singular_name = 'Date Field';
	
	static $plural_name = 'Date Fields';
	
	function populateFromPostData($data) {
		$fieldPrefix = 'Default-';
		
		if(empty($data['Default']) && !empty($data[$fieldPrefix.'Year']) && !empty($data[$fieldPrefix.'Month']) && !empty($data[$fieldPrefix.'Day'])) {
			$data['Default'] = $data['Year'] . '-' . $data['Month'] . '-' . $data['Day'];		
		}
		
		parent::populateFromPostData($data);
	}
	
	/**
	 * Return the form field.
	 *
	 * @todo Make a jQuery safe form field. The current CalendarDropDown
	 * 			breaks on the front end.
	 */
	public function getFormField() {
		return new TextField( $this->Name, $this->Title, $this->Default);
	}
	
	/**
	 * Return the validation information related to this field. This is 
	 * interrupted as a JSON object for validate plugin and used in the 
	 * PHP. 
	 *
	 * @see http://docs.jquery.com/Plugins/Validation/Methods
	 * @return Array
	 */
	public function getValidation() {
		return array(
			'date' => true
		);
	}
}