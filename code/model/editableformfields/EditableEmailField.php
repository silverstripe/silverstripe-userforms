<?php
/**
 * EditableEmailField
 *
 * Allow users to define a validating editable email field for a UserDefinedForm
 *
 * @package userforms
 */

class EditableEmailField extends EditableFormField {
	
	private static $singular_name = 'Email Field';
	
	private static $plural_name = 'Email Fields';
	
	public function getSetsOwnError() {
		return true;
	}
	
	public function getFormField() {
		$field = EmailField::create($this->Name, $this->EscapedTitle, $this->Default);
		$this->doUpdateFormField($field);
		return $field;
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
		return array_merge(parent::getValidation(), array(
			'email' => true
		));
	}
}