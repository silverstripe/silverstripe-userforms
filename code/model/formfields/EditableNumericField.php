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

	private static $db = array(
		'MinValue' => 'Int',
		'MaxValue' => 'Int'
	);
	
	public function getSetsOwnError() {
		return true;
	}
	
	/**
	 * @return NumericField
	 */
	public function getFormField() {
		$field = new NumericField($this->Name, $this->Title);
		$field->addExtraClass('number');
		$field->setValue($this->Default);

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
		$fields->push(FieldGroup::create(
			_t("EditableNumericField.RANGE", "Allowed numeric range"),
			array(
				new NumericField('MinValue', false),
				new LiteralField('RangeValue', _t("EditableNumericField.RANGE_TO", "to")),
				new NumericField('MaxValue', false)
			)
		));
		return $fields;
	}

	public function getValidation() {
		$options = array();
		if($this->MinValue) {
			$options['min'] = (int)$this->MinValue;
		}
		if($this->MaxValue) {
			$options['max'] = (int)$this->MaxValue;
		}
		return $options;
	}
}
