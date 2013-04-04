<?php
/**
 * Allows an editor to insert a generic heading into a field
 *
 * @package userforms
 */

class EditableFormHeading extends EditableFormField {

	private static $singular_name = 'Heading';
	
	private static $plural_name = 'Headings';
	
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

		$options->push(
			new CheckboxField(
				$this->getSettingName('HideFromReports'),
				_t('EditableLiteralField.HIDEFROMREPORT', 'Hide from reports?'), 
				$this->getSetting('HideFromReports')
			)
		);
		
		return $options;
	}

	public function getFormField() {
		$labelField = new HeaderField($this->Name,$this->Title, $this->getSetting('Level'));
		$labelField->addExtraClass('FormHeading');
		
		return $labelField;
	}
	
	public function showInReports() {
		return (!$this->getSetting('HideFromReports'));
	}
	
	public function getFieldValidationOptions() {
		return false;
	}
}
