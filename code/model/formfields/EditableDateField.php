<?php
/**
 * EditableDateField
 *
 * Allows a user to add a date field.
 *
 * @package userforms
 */

class EditableDateField extends EditableFormField {
	
	private static $singular_name = 'Date Field';
	
	private static $plural_name = 'Date Fields';
	
	public function getFieldConfiguration() {
		$default = ($this->getSetting('DefaultToToday')) ? $this->getSetting('DefaultToToday') : false;
		$label = _t('EditableFormField.DEFAULTTOTODAY', 'Default to Today?');
		
		return new FieldList(
			new CheckboxField($this->getSettingName("DefaultToToday"), $label, $default)
		);
	}
	
	public function populateFromPostData($data) {
		$fieldPrefix = 'Default-';
		
		if(empty($data['Default']) && !empty($data[$fieldPrefix.'Year']) && !empty($data[$fieldPrefix.'Month']) && !empty($data[$fieldPrefix.'Day'])) {
			$data['Default'] = $data['Year'] . '-' . $data['Month'] . '-' . $data['Day'];		
		}
		
		parent::populateFromPostData($data);
	}
	
	/**
	 * Return the form field
	 *
	 */
	public function getFormField() {
		$defaultValue = ($this->getSetting('DefaultToToday')) ? date('Y-m-d') : $this->Default;
		$field = new EditableDateField_FormField( $this->Name, $this->Title, $defaultValue);
		$field->setConfig('showcalendar', true);

		return $field;
	}
}

/**
  * @package userforms
 */
class EditableDateField_FormField extends DateField {

	public function Type() {
		return "date-alt text";
	}
}