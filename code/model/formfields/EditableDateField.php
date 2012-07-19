<?php
/**
 * EditableDateField
 *
 * Allows a user to add a date field.
 *
 * @package userforms
 */

class EditableDateField extends EditableFormField {
	
	static $singular_name = 'Date Field';
	
	static $plural_name = 'Date Fields';
	
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
		$field = new DateField( $this->Name, $this->Title, $defaultValue);
		$field->setConfig('showcalendar', true);
		return $field;
	}
	
	/**
	 * Return the validation information related to this field. This is 
	 * interrupted as a JSON object for validate plugin and used in the 
	 * PHP. 
	 *
	 * @see http://docs.jquery.com/Plugins/Validation/Methods
	 * @return Array
	 */
	public function getValidation() {
		return array(
			'date' => true
		);
	}
}
