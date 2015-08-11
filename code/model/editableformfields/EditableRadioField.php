<?php
/**
 * EditableRadioField
 *
 * Represents a set of selectable radio buttons
 *
 * @package userforms
 */

class EditableRadioField extends EditableMultipleOptionField {
	
	private static $singular_name = 'Radio field';
	
	private static $plural_name = 'Radio fields';

	/**
	 * @return FieldList
	 */
	public function getCMSFields() {
		$fields = parent::getCMSFields();

		$fields->removeByName('Default');

		return $fields;
	}
	
	public function getFormField() {
		$field = OptionsetField::create($this->Name, $this->EscapedTitle, $this->getOptionsMap());

		// Set default item
		$defaultOption = $this->getDefaultOptions()->first();
		if($defaultOption) {
			$field->setValue($defaultOption->EscapedTitle);
		}
		$this->doUpdateFormField($field);
		return $field;
	}
}
