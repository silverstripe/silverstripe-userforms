<?php
/**
 * Creates an editable field that displays members in a given group
 *
 * @package userforms
 */
class EditableMemberListField extends EditableFormField {
	
	static $singular_name = 'Member list field';
	
	static $plural_name = 'Member list fields';
	
	function ExtraOptions() {
		$groupID = ($this->getSetting('GroupID')) ? $this->getSetting('GroupID') : 0;
		$groups = DataObject::get("Group");
		if($groups) $groups = $groups->toDropdownMap('ID', 'Title');
		$fields = new FieldSet(
			new DropdownField("Fields[$this->ID][CustomSettings][GroupID]", _t('EditableFormField.GROUP', 'Group'), $groups, $groupID)
		);
		$fields->merge(parent::ExtraOptions());
		
		return $fields;
	}
	function getFormField() {
		return ($this->getSetting('GroupID')) ? new DropdownField( $this->Name, $this->Title, Member::mapInGroups($this->getSetting('GroupID'))) : false;
	}
	
	function getValueFromData($data) {
		$value = Convert::raw2sql($data[$this->Name]);
		
		$member = DataObject::get_one('Member', "Member.ID = {$value}");
		return ($member) ? $member->getName() : "";
	}
}
?>