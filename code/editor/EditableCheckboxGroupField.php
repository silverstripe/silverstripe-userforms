<?php
/**
 * EditableCheckboxGroup
 *
 * Represents a set of selectable radio buttons
 * 
 * @package userforms
 */
class EditableCheckboxGroupField extends EditableMultipleOptionField {

	static $singular_name = "Checkbox group";
	
	static $plural_name = "Checkbox groups";

	function DefaultOption() {
		$defaultOption = 0;
		
		foreach( $this->Options() as $option ) {
			if( $option->getField('Default') )
				return $defaultOption;
			else
				$defaultOption++;
		}
		
		return -1;
	}
	
	function createField($asFilter = false) {
		$optionSet = $this->Options();
		$options = array();
		
		$optionMap = ($optionSet) ? $optionSet->map('ID', 'Title') : array();
		
		return new CheckboxSetField($this->Name, $this->Title, $optionMap);
	}
	
	function getValueFromData($data) {
		if(empty($data[$this->Name])) {
			return "";
		}
		
		$result = '';
		$entries = $data[$this->Name];
		
		if(!is_array($data[$this->Name])) {
			$entries = array($data[$this->Name]);
		}
			
		$selectedOptions = DataObject::get('EditableOption', "ParentID={$this->ID} AND ID IN (" . implode(',', $entries) . ")");
		foreach($selectedOptions as $selected) {
			if(!$result) {
				$result = $selected->Title;
			} else {
				$result .= "," . $selected->Title;
			}
		}
		
		return $result;
	}
}

?>