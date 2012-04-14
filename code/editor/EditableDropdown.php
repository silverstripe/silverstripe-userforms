<?php
/**
 * EditableDropdown
 *
 * Represents a modifiable dropdown (select) box on a form
 *
 * @package userforms
 */

class EditableDropdown extends EditableMultipleOptionField {
	
	static $singular_name = 'Dropdown Field';
	
	static $plural_name = 'Dropdowns';
	
	/**
	 * @return DropdownField
	 */
	function getFormField() {
		$optionSet = $this->Options();
		$options = array();
		if($optionSet) 
			foreach($optionSet as $option)
				$options["EditableOption-{$option->ID}-{$option->Value}"] = $option->Title;
		return new DropdownField($this->Name, $this->Title, $options);	
	}
}