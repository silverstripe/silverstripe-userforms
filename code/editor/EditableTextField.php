<?php
/**
 * EditableTextField
 *
 * This control represents a user-defined text field in a user defined form
 *
 * @package userforms
 */

class EditableTextField extends EditableFormField {

	static $singular_name = 'Text field';
	
	static $plural_name = 'Text fields';
	
	function getFieldConfiguration() {
		$fields = parent::getFieldConfiguration();
		
		// eventually replace hard-coded "Fields"?
		$baseName = "Fields[$this->ID]";
		
		$minLength = ($this->getSetting('MinLength')) ? $this->getSetting('MinLength') : '';
		$maxLength = ($this->getSetting('MaxLength')) ? $this->getSetting('MaxLength') : '';
		$rows = ($this->getSetting('Rows')) ? $this->getSetting('Rows') : '1';
		
		$extraFields = new FieldSet(
			new FieldGroup(_t('EditableTextField.TEXTLENGTH', 'Text length'),
				new TextField($baseName . "[CustomSettings][MinLength]", "", $minLength),
				new TextField($baseName . "[CustomSettings][MaxLength]", " - ", $maxLength)
			),
			new TextField($baseName . "[CustomSettings][Rows]", _t('EditableTextField.NUMBERROWS', 'Number of rows'), $rows)
		);
		
		$fields->merge($extraFields);
		return $fields;		
	}

	/**
	 * @return TextareaField|TextField
	 */
	function getFormField() {
		if($this->getSetting('Rows') && $this->getSetting('Rows') > 1) {
			return new TextareaField($this->Name, $this->Title, $this->getSetting('Rows'));
		}
		else {
			return new TextField($this->Name, $this->Title, null, $this->getSetting('MaxLength'));
		}
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
		$options = array();
		if($this->getSetting('MinLength')) $options['minlength'] = $this->getSetting('MinLength');
		if($this->getSetting('MaxLength')) $options['maxlength'] = $this->getSetting('MaxLength');
		
		return $options;
	}
}