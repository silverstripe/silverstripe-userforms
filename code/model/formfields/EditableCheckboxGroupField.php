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
	
	public function getFormField() {
		$optionSet = $this->Options();
		$options = array();
		
		$optionMap = ($optionSet) ? $optionSet->map('Title', 'Title') : array();
		
		return new CheckboxSetField($this->Name, $this->Title, $optionMap);
	}
	
	public function getValueFromData($data) {
		$result = '';
		$entries = (isset($data[$this->Name])) ? $data[$this->Name] : false;
		
		if($entries) {
			if(!is_array($data[$this->Name])) {
				$entries = array($data[$this->Name]);
			}
			foreach($entries as $selected => $value) {
				if(!$result) {
					$result = $value;
				} else {
					$result .= ", " . $value;
				}
			}
		}
		return $result;
	}
}