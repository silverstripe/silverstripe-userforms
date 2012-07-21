<?php
/**
 * EditableRadioField
 *
 * Represents a set of selectable radio buttons
 *
 * @package userforms
 */

class EditableRadioField extends EditableMultipleOptionField {
	
	static $singular_name = 'Radio field';
	
	static $plural_name = 'Radio fields';
	
	public function getFormField() {
		$optionSet = $this->Options();
		$options = array();
		
		if($optionSet) {
			foreach( $optionSet as $option ) {
				$options[$option->Title] = $option->Title;
			}	
		}
		
		return new OptionsetField($this->Name, $this->Title, $options);
	}
}
