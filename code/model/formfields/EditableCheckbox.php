<?php
/**
 * EditableCheckbox
 *
 * A user modifiable checkbox on a UserDefinedForm
 * 
 * @package userforms
 */

class EditableCheckbox extends EditableFormField {
	
	static $singular_name = 'Checkbox Field';
	
	static $plural_name = 'Checkboxes';
	
	public function getFieldConfiguration() {
		$options = parent::getFieldConfiguration();
		$options->push(new CheckboxField("Fields[$this->ID][CustomSettings][Default]", _t('EditableFormField.CHECKEDBYDEFAULT', 'Checked by Default?'), $this->getSetting('Default')));
		
		return $options;
	}
	
	public function getFormField() {
		return new CheckboxField( $this->Name, $this->Title, $this->getSetting('Default'));
	}
	
	public function getValueFromData($data) {
		$value = (isset($data[$this->Name])) ? $data[$this->Name] : false;
		
		return ($value) ? _t('EditableFormField.YES', 'Yes') : _t('EditableFormField.NO', 'No');
	}
}