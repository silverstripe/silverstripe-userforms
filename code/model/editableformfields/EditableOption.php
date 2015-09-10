<?php

/**
 * Base Class for EditableOption Fields such as the ones used in
 * dropdown fields and in radio check box groups
 *
 * @package userforms
 */

class EditableOption extends DataObject {

	private static $default_sort = "Sort";

	private static $db = array(
		"Name" => "Varchar(255)",
		"Title" => "Varchar(255)",
		"Default" => "Boolean",
		"Sort" => "Int"
	);

	private static $has_one = array(
		"Parent" => "EditableMultipleOptionField",
	);

	private static $extensions = array(
		"Versioned('Stage', 'Live')"
	);

	private static $summary_fields = array(
		'Title',
		'Default'
	);

	/**
	 * @param Member $member
	 *
	 * @return boolean
	 */
	public function canEdit($member = null) {
		return ($this->Parent()->canEdit($member));
	}

	/**
	 * @param Member $member
	 *
	 * @return boolean
	 */
	public function canDelete($member = null) {
		return ($this->Parent()->canDelete($member));
	}

	public function getEscapedTitle() {
		return Convert::raw2att($this->Title);
	}
}
