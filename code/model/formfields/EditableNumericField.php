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
	 * @return TextareaField|TextField
	 */
	public function getFormField() {
		$taf = new NumericField($this->Name, $this->Title);
		$taf->addExtraClass('number');
		if ($this->Required) {
			//  Required and numeric validation can conflict so add the Required validation messages
			// as input attributes
			$errorMessage = $this->getErrorMessage()->HTML();
			$taf->setAttribute('data-rule-required','true');
			$taf->setAttribute('data-msg-required',$errorMessage);
		}
		return $taf;
	}
	
	public function getFieldValidationOptions() {
		$fields = parent::getFieldValidationOptions();
	
		$min = ($this->getSetting('MinLength')) ? $this->getSetting('MinLength') : '';
		$max = ($this->getSetting('MaxLength')) ? $this->getSetting('MaxLength') : '';
	
		$extraFields = new FieldList(
				new FieldGroup(_t('EditableTextField.TEXTLENGTH', 'Text length'),
						new NumericField($this->getSettingName('MinLength'), "Minimum Value", $min),
						new NumericField($this->getSettingName('MaxLength'), "Maximum Value", $max)
				)
		);
	
		$fields->merge($extraFields);
	
		return $fields;
	}
	
	public function getValidation() {
		$options = array();
	
		if($this->getSetting('MinLength'))
			$options['min'] = 0 + (int)$this->getSetting('MinLength');
			
		if($this->getSetting('MaxLength'))
			$options['max'] = 0 + (int)$this->getSetting('MaxLength');
	
		return $options;
	}
}
