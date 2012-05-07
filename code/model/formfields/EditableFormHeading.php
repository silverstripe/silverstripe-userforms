<?php
/**
 * Allows an editor to insert a generic heading into a field
 *
 * @package userforms
 */

class EditableFormHeading extends EditableFormField {

	static $singular_name = 'Heading';
	
	static $plural_name = 'Headings';
	
	public function getFieldConfiguration() {
		$levels = array(
			'1' => '1',
			'2' => '2',
			'3' => '3',
			'4' => '4',
			'5' => '5',
			'6' => '6'
		);
		
		$level = ($this->getSetting('Level')) ? $this->getSetting('Level') : 3;
		$label = _t('EditableFormHeading.LEVEL', 'Select Heading Level');
		
		$options = parent::getFieldConfiguration();
		
		$options->push(
			new DropdownField($this->getSettingName("Level"), $label, $levels, $level)
		);

		if($this->readonly) {
			$extraFields = $options->makeReadonly();		
		}
		
		return $options;
	}
	
	public function getFormField() {
		$labelField = new HeaderField($this->Name,$this->Title, $this->getSetting('Level'));
		$labelField->addExtraClass('FormHeading');
		
		return $labelField;
	}
	
	public function showInReports() {
		return false;
	}
	
	public function getFieldValidationOptions() {
		return false;
	}
}