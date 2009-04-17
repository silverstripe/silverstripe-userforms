<?php

/**
 * Base class for multiple option fields such as dropdowns and 
 * radio sets. Implemented as a class but you would not create 
 * one of these directly, rather you would instantiate a subclass
 * such as EditableDropdownField
 *
 * @todo Make it would make more sense to have dropdownfield and
 * 			checkboxset just transformations on this class
 *
 * @package userforms
 */

class EditableMultipleOptionField extends EditableFormField {
	
	static $db = array();
	
	static $has_one = array();
	
	static $has_many = array(
		"Options" => "EditableOption"
	);
	
	protected $readonly;
	
	/**
	 * Deletes all the options attached to this field before
	 * deleting the field. Keeps stray options from floating 
	 * around
	 *
	 * @return void
	 */
	public function delete() {
  		$options = $this->Options();
		if($options) {
			foreach($options as $option) {
				$option->delete();
			}
		}
		parent::delete();   
	}
	
	/**
	 * Duplicate a pages content. We need to make sure all
	 * the fields attached to that page go with it
	 * 
	 * @return DataObject a Clone of this node
	 */
	public function duplicate() {
		$clonedNode = parent::duplicate();
		
		if($this->Options()) {
			foreach($this->Options() as $field) {
				$newField = $field->duplicate();
				$newField->ParentID = $clonedNode->ID;
				$newField->write();
			}
		}
		return $clonedNode;
	}
	
	/**
	 * On before saving this object we need to go through and keep
	 * an eye on all our option fields that are related to this
	 * field in the form 
	 * 
	 * @param Array Data
	 */
	public function populateFromPostData($data) {
		parent::populateFromPostData($data);
		
		// get the current options
		$fieldSet = $this->Options();
		
		// go over all the current options and check if ID and Title still exists
		foreach($fieldSet as $option) {
			if(isset($data[$option->ID]) && isset($data[$option->ID]['Title']) && $data[$option->ID]['Title'] != "field-node-deleted") {
				$option->populateFromPostData($data[$option->ID]);
			}
			else {
				$option->delete();
			}
		}
	}
	
	/**
	 * Return whether or not this field has addable options
	 * such as a dropdown field or radio set
	 *
	 * @return bool
	 */
	public function hasAddableOptions() {
		return true;
	}

	/**
	 * Set this multipleoptionfield to readonly
	 */
	protected function ReadonlyOption() {
		$this->readonly = true;
	}

	/**
	 * Is this multipleoption field readonly to the user
	 *
	 * @return bool
	 */
	public function isReadonly() {
		return $this->readonly;
	}
	
	/**
	 * Return the form field for this object in the front 
	 * end form view
	 *
	 * @return FormField
	 */
	public function getFormField() {
		return $this->createField();
	}
	
	/**
	 * Return the form field as a field suitable for insertion 
	 * into the filter form
	 *
	 * @return FormField
	 */
	public function getFilterField() {
		return $this->createField(true);
	}
	
	/**
	 * Return the correct form field for this object. Note this
	 * does a transformation between being a field on the form and
	 * a field in the filter search form
	 * 
	 * This should be extended on your subclass
	 *
	 * @param bool - Filter Field?
	 * @return UserError - You should implement it on your subclass
	 */
	public function createField($filter = false) {
		return user_error('Please implement createField() on '. $this->class, E_USER_ERROR);
	}
	
	/**
	 * Checkbox to show if this option is the default option selected 
	 * in the form  
	 *
	 * @return HTML
	 */
	public function DefaultSelect() {
		$disabled = ($this->readonly) ? " disabled=\"disabled\"" : '';
		$default = ($this->Parent()->getField('Default') == $this->ID) ? " checked=\"checked\"" : '';

		return "<input class=\"radio\" type=\"radio\" name=\"Fields[{$this->ParentID}][Default]\" value=\"{$this->ID}\"".$disabled.$default." />";
	}
}
?>