<?php
/**
 * EditableDropdown
 *
 * Represents a modifiable dropdown box on a form
 *
 * @package userforms
 */
class EditableDropdown extends EditableMultipleOptionField {
	
	static $singular_name = 'Dropdown';
	
	static $plural_name = 'Dropdowns';

	
	function createField($asFilter = false) {
		$optionSet = $this->Options();
		$options = array();
		
		if($asFilter) {
			$options['-1'] = "(Any)";
		}
		$defaultOption = '-1';
		
		foreach( $optionSet as $option ) {
			$options[$option->Title] = $option->Title;
			if($option->getField('Default') && !$asFilter) $defaultOption = $option->Title;
		}
		
		return new DropdownField( $this->Name, $this->Title, $options, $defaultOption );	
	}

}
?>
