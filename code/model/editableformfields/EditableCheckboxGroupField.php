<?php
/**
 * EditableCheckboxGroup
 *
 * Represents a set of selectable radio buttons
 * 
 * @package userforms
 */

class EditableCheckboxGroupField extends EditableMultipleOptionField {

	private static $singular_name = "Checkbox Group";
	
	private static $plural_name = "Checkbox Groups";
	
	public function getFormField() {
		$field = new UserFormsCheckboxSetField($this->Name, $this->EscapedTitle, $this->getOptionsMap());

		// Set the default checked items
		$defaultCheckedItems = $this->getDefaultOptions();
		if ($defaultCheckedItems->count()) {
			$field->setDefaultItems($defaultCheckedItems->map('EscapedTitle')->keys());
		}

		$this->doUpdateFormField($field);
		return $field;
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
