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
	
	public function getFormField() {
		if ($this->Required) {
			//  Required and Email validation can conflict so add the Required validation messages
			// as input attributes
			$errorMessage = $this->getErrorMessage()->HTML();
			$field =  new EmailField($this->Name, $this->Title);
			$field->setAttribute('data-rule-required','true');
			$field->setAttribute('data-msg-required',$errorMessage);
			return $field;
		}
		return new EmailField($this->Name, $this->Title);
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
			'email' => true
		);
	}
}