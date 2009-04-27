<?php
/**
 * Creates an editable field that displays members in a given group
 *
 * @package userforms
 */
class EditableMemberListField extends EditableFormField {
	
	static $singular_name = 'Member list field';
	
	static $plural_name = 'Member list fields';
	
	public function DefaultField() {
		
		$groups = DataObject::get('Group');
		
		foreach( $groups as $group )
			$groupArray[$group->ID] = $group->Title;
		
		return new DropdownField( "Fields[{$this->ID}][CustomSetting][GroupID]", 'Group', $groupArray, $this->getSetting('GroupID'));
	}
	
	function getFormField() {
		return new DropdownField( $this->Name, $this->Title, Member::mapInGroups($this->getSetting($this->GroupID)));
	}
	
	function getValueFromData($data) {
		$value = Convert::raw2sql($data[$this->Name]);
		
		$member = DataObject::get_one('Member', "Member.ID = {$value}");
		return $member->getName();
	}
}
?>