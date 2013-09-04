<?php
/**
 * Data received from a UserDefinedForm submission
 *
 * @package userforms
 */

class SubmittedFormField extends DataObject {
	
	private static $db = array(
		"Name" => "Varchar",
		"Value" => "Text",
		"Title" => "Varchar(255)"
	);
	
	private static $has_one = array(
		"Parent" => "SubmittedForm"
	);

	private static $summary_fields = array(
		'Title' => 'Title',
		'FormattedValue' => 'Value'
	);

	/**
	 * @param Member
	 *
	 * @return boolean
	 */
	public function canCreate($member = null) {
		return $this->Parent()->canCreate();
	}

	/**
	 * @param Member
	 *
	 * @return boolean
	 */
	public function canView($member = null) {
		return $this->Parent()->canView();
	}

	/**
	 * @param Member
	 *
	 * @return boolean
	 */
	public function canEdit($member = null) {
		return $this->Parent()->canEdit();
	}

	/**
	 * @param Member
	 *
	 * @return boolean
	 */
	public function canDelete($member = null) {
		return $this->Parent()->canDelete();
	}

	/**
	 * Generate a formatted value for the reports and email notifications.
	 * Converts new lines (which are stored in the database text field) as
	 * <brs> so they will output as newlines in the reports
	 *
	 * @return string
	 */
	public function getFormattedValue() {
		return nl2br($this->dbObject('Value')->ATT());
	}
	
	/**
	 * Return the value of this submitted form field suitable for inclusion
	 * into the CSV
	 *
	 * @return Text
	 */
	public function getExportValue() {
		return $this->Value;
	}
}
