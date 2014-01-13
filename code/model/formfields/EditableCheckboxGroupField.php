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

	private static $form_field_class = 'CheckboxSetField';


	public function getValueFromData($data) {
		$values = array();
		if (isset($data[$this->Name])) {
			$items = is_array($data[$this->Name]) ? $data[$this->Name] : array($data[$this->Name]);
			foreach($items as $item) {
				$values[] = $this->processValueFromData($item);
			}
			$values = array_filter($values);
		}
		return implode(', ', $values);
	}
}
