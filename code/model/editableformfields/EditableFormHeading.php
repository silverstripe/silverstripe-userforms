<?php
/**
 * Allows an editor to insert a generic heading into a field
 *
 * @package userforms
 */

class EditableFormHeading extends EditableFormField {

	private static $singular_name = 'Heading';
	
	private static $plural_name = 'Headings';

	/**
	 * @return FieldList
	 */
	public function getCMSFields() {
		$fields = parent::getCMSFields();

		$fields->removeByName('Default');
		$fields->removeByName('Validation');

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

		$fields->addFieldsToTab('Root.Main', array(
			DropdownField::create(
				$this->getSettingName('Level'),
				$label,
				$levels,
				$level
			),
			CheckboxField::create(
				$this->getSettingName('HideFromReports'),
				_t('EditableLiteralField.HIDEFROMREPORT', 'Hide from reports?'), 
				$this->getSetting('HideFromReports')
			)
		));

		return $fields;
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
