<?php
/**
 * Allows an editor to insert a generic heading into a field
 *
 * @subpackage userforms
 */
class EditableFormHeading extends EditableFormField {

	static $singular_name = 'Form heading';
	
	static $plural_name = 'Form headings';
	
	function ExtraOptions() {
		$levels = array('1' => '1','2' => '2','3' => '3','4' => '4','5' => '5','6' => '6');
		$level = ($this->getSetting('Level')) ? $this->getSetting('Level') : 3;
		$extraFields = new FieldSet(
			new DropdownField("Fields[$this->ID][CustomSettings][Level]", _t('EditableFormHeading.LEVEL', 'Select Heading Level'), $levels, $level)
		);

		if($this->readonly) {
			$extraFields = $extraFields->makeReadonly();		
		}
		return $extraFields;
	}
	
	function getFormField() {
		$labelField = new HeaderField('FormHeadingLabel',$this->Title, $this->getSetting('Level'));
		$labelField->addExtraClass('FormHeading');
		
		return $labelField;
	}
	
	function showInReports() {
		return false;
	}
}
?>