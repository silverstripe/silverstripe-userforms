<?php
/**
 * Creates an editable field that displays members in a given group
 *
 * @package userforms
 */

class EditableMemberListField extends EditableFormField {
	
	private static $singular_name = 'Member List Field';
	
	private static $plural_name = 'Member List Fields';

	/**
	 * @return FieldList
	 */
	public function getCMSFields() {
		$fields = parent::getCMSFields();

		$fields->removeByName('Default');
		$fields->removeByName('Validation');

		$groupID = ($this->getSetting('GroupID')) ? $this->getSetting('GroupID') : 0;
		$groups = DataObject::get('Group');
		
		if($groups) {
			$groups = $groups->map('ID', 'Title');
		}
		
		$fields->addFieldToTab('Root.Main', DropdownField::create(
			"Fields[$this->ID][CustomSettings][GroupID]",
			_t('EditableFormField.GROUP', 'Group'),
			$groups,
			$groupID
		));

		return $fields;
	}
	
	public function getFormField() {
		if ($this->getSetting('GroupID')) {
			$members = Member::map_in_groups($this->getSetting('GroupID'));
			
			return new DropdownField($this->Name, $this->Title, $members);
		}
		
		return false;
	}
	
	public function getValueFromData($data) {
		if(isset($data[$this->Name])) {
			$value = Convert::raw2sql($data[$this->Name]);
		
			$member = DataObject::get_one('Member', "Member.ID = {$value}");
			
			return ($member) ? $member->getName() : "";
		}
		
		return false;
	}
}