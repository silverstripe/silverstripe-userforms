<?php
/**
 * Allows an editor to insert a generic heading into a field
 *
 * @subpackage userforms
 */
class EditableFormHeading extends EditableFormField {
	
	static $db = array(
		'Level' => 'Varchar(1)'
	);
	
	static $singular_name = 'Form heading';
	
	static $plural_name = 'Form headings';
	
	function populateFromPostData($data) {
		$this->Level = (isset($data['Level'])) ? $data['Level'] : 2;
		
		parent::populateFromPostData($data);
	}
	
	function ExtraOptions() {
		$levels = array('1','2','3','4','5','6');
		$default = ($this->Level) ? $this->Level : 2;
		$extraFields = new FieldSet(
			new DropdownField("Fields[$this->ID][Level]", _t('EditableFormHeading.LEVEL', 'Select Heading Level'), $levels, $default)
		);

		if($this->readonly) {
			$extraFields = $extraFields->makeReadonly();		
		}
		return $extraFields;
	}
	
	function getFormField() {
		$labelField = new HeaderField('FormHeadingLabel',$this->Title, $this->Level);
		$labelField->addExtraClass('FormHeading');
		
		return $labelField;
	}
	
	function showInReports() {
		return false;
	}
}
?>