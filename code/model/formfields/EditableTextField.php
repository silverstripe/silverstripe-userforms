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
	
	public function getFieldConfiguration() {
		$fields = parent::getFieldConfiguration();
		
		$min = ($this->getSetting('MinLength')) ? $this->getSetting('MinLength') : '';
		$max = ($this->getSetting('MaxLength')) ? $this->getSetting('MaxLength') : '';
		
		$rows = ($this->getSetting('Rows')) ? $this->getSetting('Rows') : '1';
		
		$extraFields = new FieldList(
			new FieldGroup(_t('EditableTextField.TEXTLENGTH', 'Text length'),
				new NumericField($this->getSettingName('MinLength'), "", $min),
				new NumericField($this->getSettingName('MaxLength'), " - ", $max)
			),
			new NumericField($this->getSettingName('Rows'), _t('EditableTextField.NUMBERROWS',
				'Number of rows'), $rows)
		);
		
		$fields->merge($extraFields);
		
		return $fields;		
	}

	/**
	 * @return TextareaField|TextField
	 */
	public function getFormField() {
		
		$field = NULL;
		
		if($this->getSetting('Rows') && $this->getSetting('Rows') > 1) {
			$field = TextareaField::create($this->Name, $this->Title);
			$field->setRows($this->getSetting('Rows'));
		}
		else {
			$field = TextField::create($this->Name, $this->Title, null, $this->getSetting('MaxLength'));
		}

		if ($this->Required) {
			// Required validation can conflict so add the Required validation messages
			// as input attributes
			$errorMessage = $this->getErrorMessage()->HTML();
			$field->setAttribute('data-rule-required', 'true');
			$field->setAttribute('data-msg-required', $errorMessage);
		}
		
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
		
		if($this->getSetting('MinLength')) {
			$options['minlength'] = $this->getSetting('MinLength');
		}
			
		if($this->getSetting('MaxLength')) {
			$options['maxlength'] = $this->getSetting('MaxLength');
		}
		
		return $options;
	}
}
