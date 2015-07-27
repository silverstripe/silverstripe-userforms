<?php
/**
 * Allows an editor to insert a generic heading into a field
 *
 * @package userforms
 */

class EditableFormHeading extends EditableFormField {

	private static $singular_name = 'Heading';
	
	private static $plural_name = 'Headings';

	private static $db = array(
		'Level' => 'Int(3)', // From CustomSettings
		'HideFromReports' => 'Boolean(0)' // from CustomSettings
	);

	private static $defaults = array(
		'Level' => 3,
		'HideFromReports' => false
	);

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
		
		$fields->addFieldsToTab('Root.Main', array(
			DropdownField::create(
				'Level',
				_t('EditableFormHeading.LEVEL', 'Select Heading Level'),
				$levels
			),
			CheckboxField::create(
				'HideFromReports',
				_t('EditableLiteralField.HIDEFROMREPORT', 'Hide from reports?')
			)
		));

		return $fields;
	}

	public function getFormField() {
		$labelField = new HeaderField($this->Name, $this->Title, $this->Level);
		$labelField->addExtraClass('FormHeading');
		return $labelField;
	}
	
	public function showInReports() {
		return !$this->HideFromReports;
	}
	
	public function getFieldValidationOptions() {
		return false;
	}
}
