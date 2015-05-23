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
		$field = EditableDateField_FormField::create( $this->Name, $this->Title, $defaultValue);
		$field->setConfig('showcalendar', true);

		if ($this->Required) {
			// Required validation can conflict so add the Required validation messages
			// as input attributes
			$errorMessage = $this->getErrorMessage()->HTML();
			$field->setAttribute('data-rule-required', 'true');
			$field->setAttribute('data-msg-required', $errorMessage);
		}
		
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