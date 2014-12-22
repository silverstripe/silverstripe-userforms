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
	
	public function getFieldConfiguration() {
		$options = parent::getFieldConfiguration();
		$options->push(new CheckboxField("Fields[$this->ID][CustomSettings][Default]", _t('EditableFormField.CHECKEDBYDEFAULT', 'Checked by Default?'), $this->getSetting('Default')));
		
		return $options;
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
		
		return $field;
	}
	
	public function getValueFromData($data) {
		$value = (isset($data[$this->Name])) ? $data[$this->Name] : false;
		
		return ($value) ? _t('EditableFormField.YES', 'Yes') : _t('EditableFormField.NO', 'No');
	}
}