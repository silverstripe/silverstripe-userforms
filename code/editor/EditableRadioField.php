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
	
	function createField( $asFilter = false ) {
		$optionSet = $this->Options();
		$options = array();
		$defaultOption = '';
		
		if( $asFilter )
			$options['-1'] = '(Any)';
		
		// $defaultOption = '-1';
		
		foreach( $optionSet as $option ) {
			$options[$option->Title] = $option->Title;
			if( $option->getField('Default') && !$asFilter ) $defaultOption = $option->Title;
		}
		
		// return radiofields
		return new OptionsetField($this->Name, $this->Title, $options, $defaultOption);
	}
}
?>
