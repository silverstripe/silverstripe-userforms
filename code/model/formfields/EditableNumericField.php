<?php
/**
 * EditableNumericField
 *
 * This control represents a user-defined numeric field in a user defined form
 *
 * @package userforms
 */

class EditableNumericField extends EditableFormField {

	private static $singular_name = 'Numeric Field';
	
	private static $plural_name = 'Numeric Fields';
	
	public function getSetsOwnError() {
		return true;
	}
	
	/**
	 * @return NumericField
	 */
	public function getFormField() {
		$field = new NumericField($this->Name, $this->Title);
		$field->addExtraClass('number');

		if ($this->Required) {
			// Required and numeric validation can conflict so add the
			// required validation messages as input attributes
			$errorMessage = $this->getErrorMessage()->HTML();
			$field->setAttribute('data-rule-required', 'true');
			$field->setAttribute('data-msg-required', $errorMessage);
		}

		return $field;
	}

	public function getFieldValidationOptions() {
		$fields = parent::getFieldValidationOptions();

		$min = ($this->getSetting('MinValue')) ? $this->getSetting('MinValue') : '';
		$max = ($this->getSetting('MaxValue')) ? $this->getSetting('MaxValue') : '';

		$extraFields = new FieldList(
			new NumericField($this->getSettingName('MinValue'), _t('EditableFormField.MINVALUE', 'Min Value'), $min),
			new NumericField($this->getSettingName('MaxValue'), _t('EditableFormField.MAXVALUE', 'Max Value'), $max)
		);

		$fields->merge($extraFields);

		return $fields;
	}

	public function getValidation() {
		$options = array();

		if($this->getSetting('MinValue')) {
			$options['min'] = 0 + (int) $this->getSetting('MinValue');
		}

		if($this->getSetting('MaxValue')) {
			$options['max'] = 0 + (int)$this->getSetting('MaxValue');
		}

		return $options;
	}
}
