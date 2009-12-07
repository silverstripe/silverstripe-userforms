<?php
/**
 * Allows an editor to insert a generic heading into a field
 *
 * @subpackage userforms
 */

class EditableFormHeading extends EditableFormField {

	static $singular_name = 'Heading';
	
	static $plural_name = 'Headings';
	
	function getFieldConfiguration() {
		$levels = array('1' => '1','2' => '2','3' => '3','4' => '4','5' => '5','6' => '6');
		$level = ($this->getSetting('Level')) ? $this->getSetting('Level') : 3;
		
		$options = parent::getFieldConfiguration();
		$options->push(new DropdownField("Fields[$this->ID][CustomSettings][Level]", _t('EditableFormHeading.LEVEL', 'Select Heading Level'), $levels, $level));

		if($this->readonly) {
			$extraFields = $options->makeReadonly();		
		}
		return $options;
	}
	
	function getFormField() {
		$labelField = new HeaderField($this->Name,$this->Title, $this->getSetting('Level'));
		$labelField->addExtraClass('FormHeading');
		
		return $labelField;
	}
	
	function showInReports() {
		return false;
	}
	
	function getFieldValidationOptions() {
		return false;
	}
}