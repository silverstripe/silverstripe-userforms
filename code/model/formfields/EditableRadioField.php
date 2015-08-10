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
		$optionSet = $this->Options();
		$defaultOptions = $optionSet->filter('Default', 1);
		$options = array();

		if($optionSet) {
			foreach($optionSet as $option) {
				$options[$option->EscapedTitle] = $option->Title;
			}
		}

		$field = OptionsetField::create($this->Name, $this->Title, $options);

		if($defaultOptions->count()) {
			$field->setValue($defaultOptions->First()->EscapedTitle);
		}

		return $field;
	}
}
