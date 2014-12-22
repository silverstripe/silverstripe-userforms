<?php
/**
 * EditableDropdown
 *
 * Represents a modifiable dropdown (select) box on a form
 *
 * @package userforms
 */

class EditableDropdown extends EditableMultipleOptionField {
	
	private static $singular_name = 'Dropdown Field';
	
	private static $plural_name = 'Dropdowns';
	
	/**
	 * @return DropdownField
	 */
	public function getFormField() {	
		$optionSet = $this->Options();
		$options = array();

		if($optionSet) {
			foreach($optionSet as $option) {
				$options[$option->Title] = $option->Title;
			}
		}
		
		$field = DropdownField::create($this->Name, $this->Title, $options);

		if ($this->Required) {
			// Required validation can conflict so add the Required validation messages
			// as input attributes
			$errorMessage = $this->getErrorMessage()->HTML();
			$field->setAttribute('data-rule-required', 'true');
			$field->setAttribute('data-msg-required', $errorMessage);
		}
		
		return $field;
	}
}