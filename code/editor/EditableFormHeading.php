<?php
/**
 * Allows an editor to insert a generic heading into a field
 * @package forms
 * @subpackage fieldeditor
 */
class EditableFormHeading extends EditableFormField {
	static $singular_name = 'Form heading';
	static $plural_name = 'Form headings';
	
	function getFormField() {
		// TODO customise this
		$labelField = new LabelField('FormHeadingLabel',$this->Title);
		$labelField->addExtraClass('FormHeading');
		return $labelField;
	}
	
	function showInReports() {
		return false;
	}
}
?>