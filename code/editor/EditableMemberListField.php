<?php
/**
 * Creates an editable field that displays members in a given group
 *
 * @package userforms
 */
class EditableMemberListField extends EditableFormField {
	
	static $singular_name = 'Member list field';
	
	static $plural_name = 'Member list fields';
	
	function getFormField() {
		return ($this->getSetting($this->GroupID)) ? new DropdownField( $this->Name, $this->Title, Member::mapInGroups($this->getSetting($this->GroupID))) : false;
	}
	
	function getValueFromData($data) {
		$value = Convert::raw2sql($data[$this->Name]);
		
		$member = DataObject::get_one('Member', "Member.ID = {$value}");
		return ($member) ? $member->getName() : "";
	}
}
?>