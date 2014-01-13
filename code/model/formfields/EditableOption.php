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
		"Value" => "Varchar(255)",
		"Default" => "Boolean",
		"Sort" => "Int"
	);
	
	private static $has_one = array(
		"Parent" => "EditableMultipleOptionField",
	);
	
	private static $extensions = array(
		"Versioned('Stage', 'Live')"
	);

	public function getValue() {
		if ($this->getField('Value')) {
			return $this->getField('Value');
		}
		return $this->Title;
	}

	/**
	 * Template for the editing view of this option field
	 */
	public function EditSegment() {
		return $this->renderWith('EditableOption');
	}

	/**
	 * Name of this field in the form
	 * 
	 * @return String
	 */
	public function FieldName() {
		return "Fields[{$this->ParentID}][{$this->ID}]";
	}

	/**
	 * Populate this option from the form field
	 *
	 * @param Array Data
	 */
	public function populateFromPostData($data) {
		$this->Title = (isset($data['Title'])) ? $data['Title'] : "";
		$this->Value = (isset($data['Value'])) ? $data['Value'] : "";
		$this->Default = (isset($data['Default'])) ? $data['Default'] : "";
		$this->Sort = (isset($data['Sort'])) ? $data['Sort'] : 0;
		$this->write();
	}
	
	/**
	 * Make this option readonly 
	 */
	public function ReadonlyOption() {
		$this->readonly = true;
		return $this->EditSegment();
	}

    public function getEscapedTitle() {
        return Convert::raw2att($this->Title);
    }
}
