<?php
/**
 * EditableTextField
 *
 * This control represents a user-defined text field in a user defined form
 *
 * @package userforms
 */

class EditableTextField extends EditableFormField {

	static $singular_name = 'Text Field';
	
	static $plural_name = 'Text Fields';
	
	public function getFieldConfiguration() {
		$fields = parent::getFieldConfiguration();
		
		$min = ($this->getSetting('MinLength')) ? $this->getSetting('MinLength') : '';
		$max = ($this->getSetting('MaxLength')) ? $this->getSetting('MaxLength') : '';
		
		$rows = ($this->getSetting('Rows')) ? $this->getSetting('Rows') : '1';
		
		$extraFields = new FieldList(
			new FieldGroup(_t('EditableTextField.TEXTLENGTH', 'Text length'),
				new TextField($this->getSettingName('MinLength'), "", $min),
				new TextField($this->getSettingName('MaxLength'), " - ", $max)
			),
			new TextField($this->getSettingName('Rows'), _t('EditableTextField.NUMBERROWS', 'Number of rows'), $rows)
		);
		
		$fields->merge($extraFields);
		
		return $fields;		
	}

	/**
	 * @return TextareaField|TextField
	 */
	public function getFormField() {
		if($this->getSetting('Rows') && $this->getSetting('Rows') > 1) {
			$taf = new TextareaField($this->Name, $this->Title);
			$taf->setRows($this->getSetting('Rows'));
			return $taf;
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
	 * @return array
	 */
	public function getValidation() {
		$options = array();
		
		if($this->getSetting('MinLength')) 
			$options['minlength'] = $this->getSetting('MinLength');
			
		if($this->getSetting('MaxLength')) 
			$options['maxlength'] = $this->getSetting('MaxLength');
		
		return $options;
	}
}
