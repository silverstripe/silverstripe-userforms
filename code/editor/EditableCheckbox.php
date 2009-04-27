<?php
/**
 * EditableCheckbox
 * A user modifiable checkbox on a UserDefinedForm
 * 
 * @package userforms
 */
class EditableCheckbox extends EditableFormField {
	
	static $singular_name = 'Checkbox';
	
	static $plural_name = 'Checkboxes';
	
	
	public function ExtraOptions() {
		$fields = new FieldSet(
			new CheckboxField("Fields[$this->ID][CustomSettings][Default]", _t('EditableFormField.CHECKEDBYDEFAULT', 'Checked by Default?'), $this->getSetting('Default'))
		);
		$fields->merge(parent::ExtraOptions());
		return $fields;
	}
	
	public function getFormField() {
		return new CheckboxField( $this->Name, $this->Title, $this->getSetting('Default'));
	}
}
?>