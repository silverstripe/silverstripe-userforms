<?php

/**
 * Base Class for EditableOption Fields such as the ones used in 
 * dropdown fields and in radio check box groups
 * 
 * @package userforms
 */

class EditableOption extends DataObject {
	
	static $default_sort = "Sort";

	static $db = array(
		"Name" => "Varchar(255)",
		"Title" => "Varchar(255)",
		"Default" => "Boolean",
		"Sort" => "Int"
	);
	
	static $has_one = array(
		"Parent" => "EditableMultipleOptionField",
	);
	
	static $extensions = array(
		"Versioned('Stage', 'Live')"
	);

	/**
	 * Template for the editing view of this option field
	 */
	public function EditSegment() {
		return $this->renderWith('EditableOption');
	}

	/**
	 * The Title Field for this object
	 * 
	 * @return FormField
	 */
	public function TitleField() {
		return new TextField("Fields[{$this->ParentID}][{$this->ID}][Title]", null, $this->Title );
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
}