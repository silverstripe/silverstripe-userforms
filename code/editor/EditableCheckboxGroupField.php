<?php
/**
 * EditableCheckboxGroup
 *
 * Represents a set of selectable radio buttons
 * 
 * @package userforms
 */

class EditableCheckboxGroupField extends EditableMultipleOptionField {

	static $singular_name = "Checkbox Group";
	
	static $plural_name = "Checkbox Groups";
	
	function getFormField() {
		$optionSet = $this->Options();
		$options = array();
		if($optionSet)
			foreach($optionSet as $option)
				$options["EditableOption-{$option->ID}-{$option->Value}"] = $option->Title;
		return new CheckboxSetField($this->Name, $this->Title, $options);
	}
	

}