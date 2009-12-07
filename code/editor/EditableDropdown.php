<?php
/**
 * EditableDropdown
 *
 * Represents a modifiable dropdown box on a form
 *
 * @package userforms
 */

class EditableDropdown extends EditableMultipleOptionField {
	
	static $singular_name = 'Dropdown Field';
	
	static $plural_name = 'Dropdowns';

	
	function getFormField($asFilter = false) {
		$optionSet = $this->Options();
		$options = array();
		if($optionSet) {
			foreach( $optionSet as $option ) {
				$options[$option->Title] = $option->Title;
			}
		}
		return new DropdownField( $this->Name, $this->Title, $options);	
	}

}