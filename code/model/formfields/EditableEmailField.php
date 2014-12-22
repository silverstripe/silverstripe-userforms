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
		
		$field = EmailField::create($this->Name, $this->Title);
		
		if ($this->Required) {
			// Required and Email validation can conflict so add the Required validation messages
			// as input attributes
			$errorMessage = $this->getErrorMessage()->HTML();
			$field->setAttribute('data-rule-required', 'true');
			$field->setAttribute('data-msg-required', $errorMessage);
		}
		
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