<?php
/**
 * EditableTextField
 *
 * This control represents a user-defined text field in a user defined form
 *
 * @package userforms
 */

class EditableTextField extends EditableFormField {

	private static $singular_name = 'Text Field';
	
	private static $plural_name = 'Text Fields';

	private static $db = array(
		'MinLength' => 'Int',
		'MaxLength' => 'Int',
		'Rows' => 'Int(1)'
	);

	private static $defaults = array(
		'Rows' => 1
	);

	public function getCMSFields() {
		$this->beforeUpdateCMSFields(function($fields) {
			$fields->addFieldToTab(
				'Root.Main',
				NumericField::create(
					'Rows',
					_t('EditableTextField.NUMBERROWS', 'Number of rows')
				)->setDescription(_t(
					'EditableTextField.NUMBERROWS_DESCRIPTION',
					'Fields with more than one row will be generated as a textarea'
				))
			);
		});

		return parent::getCMSFields();
	}

	/**
	 * @return FieldList
	 */
	public function getFieldValidationOptions() {
		$fields = parent::getFieldValidationOptions();

		$fields->merge(array(
			FieldGroup::create(
				_t('EditableTextField.TEXTLENGTH', 'Allowed text length'),
				array(
					NumericField::create('MinLength', false),
					LiteralField::create('RangeLength', _t("EditableTextField.RANGE_TO", "to")),
					NumericField::create('MaxLength', false)
				)
			)
		));

		return $fields;
	}

	/**
	 * @return TextareaField|TextField
	 */
	public function getFormField() {
		if($this->Rows > 1) {
			$field = TextareaField::create($this->Name, $this->Title);
			$field->setRows($this->Rows);
		} else {
			$field = TextField::create($this->Name, $this->Title, null, $this->MaxLength);
		}

		if ($this->Required) {
			// Required validation can conflict so add the Required validation messages
			// as input attributes
			$errorMessage = $this->getErrorMessage()->HTML();
			$field->setAttribute('data-rule-required', 'true');
			$field->setAttribute('data-msg-required', $errorMessage);
		}

		$field->setValue($this->Default);
		
		return $field;
	}
	
	/**
	 * Return the validation information related to this field. This is 
	 * interrupted as a JSON object for validate plugin and used in the 
	 * PHP. 
	 *
	 * @see http://docs.jquery.com/Plugins/Validation/Methods
	 * @return array
	 */
	public function getValidation() {
		$options = parent::getValidation();
		if($this->MinLength) {
			$options['minlength'] = (int)$this->MinLength;
		}	
		if($this->MaxLength) {
			$options['maxlength'] = (int)$this->MaxLength;
		}
		return $options;
	}
}
