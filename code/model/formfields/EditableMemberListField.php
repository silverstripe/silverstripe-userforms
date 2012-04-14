<?php
/**
 * Creates an editable field that displays members in a given group
 *
 * @package userforms
 */

class EditableMemberListField extends EditableFormField {
	
	static $singular_name = 'Member List Field';
	
	static $plural_name = 'Member List Fields';
	
	function getFieldConfiguration() {
		$groupID = ($this->getSetting('GroupID')) ? $this->getSetting('GroupID') : 0;
		$groups = DataObject::get("Group");
		
		if($groups) $groups = $groups->toDropdownMap('ID', 'Title');
		
		$fields = new FieldSet(
			new DropdownField("Fields[$this->ID][CustomSettings][GroupID]", _t('EditableFormField.GROUP', 'Group'), $groups, $groupID)
		);
		
		return $fields;
	}
	
	function getFormField() {
		if ($this->getSetting('GroupID')) {
			$members = Member::mapInGroups($this->getSetting('GroupID'));
			
			return new DropdownField($this->Name, $this->Title, $members);
		}
		
		return false;
	}
	
	function getValueFromData($data) {
		if(isset($data[$this->Name])) {
			$value = Convert::raw2sql($data[$this->Name]);
		
			$member = DataObject::get_one('Member', "Member.ID = {$value}");
			
			return ($member) ? $member->getName() : "";
		}
		
		return false;
	}
}