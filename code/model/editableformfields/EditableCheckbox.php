<?php
/**
 * EditableCheckbox
 *
 * A user modifiable checkbox on a UserDefinedForm
 * 
 * @package userforms
 */

class EditableCheckbox extends EditableFormField {
	
	private static $singular_name = 'Checkbox Field';
	
	private static $plural_name = 'Checkboxes';

	/**
	 * @return FieldList
	 */
	public function getCMSFields() {
		$fields = parent::getCMSFields();

		$fields->removeByName('Default');

		$fields->addFieldToTab('Root.Main', CheckboxField::create(
			"Fields[$this->ID][CustomSettings][Default]",
			_t('EditableFormField.CHECKEDBYDEFAULT', 'Checked by Default?'),
			$this->getSetting('Default')
		));

		return $fields;
	}

	public function getFormField() {
		
		$field = CheckboxField::create( $this->Name, $this->Title, $this->getSetting('Default'));
		
		if ($this->Required) {
			// Required validation can conflict so add the Required validation messages
			// as input attributes
			$errorMessage = $this->getErrorMessage()->HTML();
			$field->setAttribute('data-rule-required', 'true');
			$field->setAttribute('data-msg-required', $errorMessage);
		}

		$field->setValue($this->getSetting('Default'));
		
		return $field;
	}
	
	public function getValueFromData($data) {
		$value = (isset($data[$this->Name])) ? $data[$this->Name] : false;
		
		return ($value) ? _t('EditableFormField.YES', 'Yes') : _t('EditableFormField.NO', 'No');
	}
}