<?php

/**
 * Base class for multiple option fields such as {@link EditableDropdownField} 
 * and radio sets. 
 * 
 * Implemented as a class but should be viewed as abstract, you should 
 * instantiate a subclass such as {@link EditableDropdownField}
 *
 * @see EditableCheckboxGroupField
 * @see EditableDropdownField
 *
 * @package userforms
 */

class EditableMultipleOptionField extends EditableFormField {
	
	private static $has_many = array(
		"Options" => "EditableOption"
	);
	
	/**
	 * Publishing Versioning support.
	 *
	 * When publishing it needs to handle copying across / publishing
	 * each of the individual field options
	 * 
	 * @return void
	 */
	public function doPublish($fromStage, $toStage, $createNewVersion = false) {
		$live = Versioned::get_by_stage("EditableOption", "Live", "\"EditableOption\".\"ParentID\" = $this->ID");

		if($live) {
			foreach($live as $option) {
				$option->delete();
			}
		}
		
		if($this->Options()) {
			foreach($this->Options() as $option) {
				$option->publish($fromStage, $toStage, $createNewVersion);
			}
		}
		
		$this->publish($fromStage, $toStage, $createNewVersion);
	}
	
	/**
	 * Unpublishing Versioning support
	 * 
	 * When unpublishing the field it has to remove all options attached
	 *
	 * @return void
	 */
	public function doDeleteFromStage($stage) {
		if($this->Options()) {
			foreach($this->Options() as $option) {
				$option->deleteFromStage($stage);
			}
		}
		
		$this->deleteFromStage($stage);
	}
	
	/**
	 * Deletes all the options attached to this field before deleting the 
	 * field. Keeps stray options from floating around
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
	 * Duplicate a pages content. We need to make sure all the fields attached 
	 * to that page go with it
	 * 
	 * @return DataObject
	 */
	public function duplicate($doWrite = true) {
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
	 * On before saving this object we need to go through and keep an eye on 
	 * all our option fields that are related to this field in the form 
	 * 
	 * @param ArrayData
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
	 * Return whether or not this field has addable options such as a 
	 * {@link EditableDropdownField} or {@link EditableRadioField}
	 *
	 * @return bool
	 */
	public function getHasAddableOptions() {
		return true;
	}

	/**
	 * Return the form field for this object in the front end form view
	 *
	 * @return FormField
	 */
	public function getFormField() {
		return user_error('Please implement getFormField() on '. $this->class, E_USER_ERROR);
	}
}
