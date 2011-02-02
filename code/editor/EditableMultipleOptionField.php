<?php

/**
 * Base class for multiple option fields such as dropdowns and 
 * radio sets. Implemented as a class but you would not create 
 * one of these directly, rather you would instantiate a subclass
 * such as {@link EditableDropdownField}
 *
 * @package userforms
 */

class EditableMultipleOptionField extends EditableFormField {
	
	static $db = array();
	
	static $has_one = array();
	
	static $has_many = array(
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

		// go over all the current options and check if ID, Value and Title still exists
		foreach($fieldSet as $option) {
			if(isset($data[$option->ID]) && isset($data[$option->ID]['Value']) && isset($data[$option->ID]['Title']) && $data[$option->ID]['Title'] != "field-node-deleted") {
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
	public function getHasAddableOptions() {
		return true;
	}

	/**
	 * Return the form field for this object in the front 
	 * end form view
	 *
	 * @return FormField
	 */
	public function getFormField() {
		return user_error('Please implement getFormField() on '. $this->class, E_USER_ERROR);
	}
	
	/**
	 * returns a Set of EditableOption Objects for the given entries
	 * 
	 * @param array|string $entries
	 * @uses getOptionFromValueString($value)
	 * @return DataObjectSet
	 */
	function getOptionsFromDataEntries($entries) {
		$entries = (is_array($entries)) ? $entries : array($entries);
		$Options = new DataObjectSet();
		if ($entries) foreach($entries as $value) {
			$Option = $this->getOptionFromValueString($value);
			if ($Option) $Options->push($Option);
		}
		return $Options;
	}
	
	/**
	 * returns a EditableOption Object for the given String
	 * 
	 * @param string $value (when calling from getValueFromData() it will mostlikely be $data[$this->Name])
	 * @return EditableOption|boolean
	 */
	function getOptionFromValueString($value) {
		$parts = explode('-', $value, 3);
		$classString = Convert::raw2sql($parts[0]);
		$id = (int)$parts[1];
		if ($classString == 'EditableOption' || (ClassInfo::exists($classString) && ClassInfo::is_subclass_of($classString, 'EditableOption'))) {
			$Option = DataObject::get_by_id($classString, $id);
			if ($Option) return $Option;	
		}	
		return false;
	}
	
	/**
	 * Loops thorugh all EditableOptions and returns a array of all valid Email Adresses that could be found in EditableOption->Value
	 * @param string|array $data
	 * @return array
	 */
	function getEmailAddressesFromData($data) {
		$EmailAddresses = array();
		$pcrePattern = '^[a-z0-9!#$%&\'*+/=?^_`{|}~-]+(?:\\.[a-z0-9!#$%&\'*+/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?$';
		if (isset($data[$this->Name])) {
			$Options = $this->getOptionsFromDataEntries($data[$this->Name]);
			if ($Options) foreach($Options as $Option) {
					$adress = trim($Option->Value);
					// PHP uses forward slash (/) to delimit start/end of pattern, so it must be escaped
					$pregSafePattern = str_replace('/', '\\/', $pcrePattern);
					if($adress && preg_match('/' . $pregSafePattern . '/i', $adress))
						$EmailAddresses[] = $adress;
			}
		}
		return $EmailAddresses;
	}
	
	/**
	 * Will return all Options as string like this: <code>Option1 (Option1 Value), Option2 (Option2 Value)</code>
	 * @param array $data
	 * @return string|boolean
	 */
	function getValueFromData($data) {
		if (isset($data[$this->Name])) {
			$Options = $this->getOptionsFromDataEntries($data[$this->Name]);
			$Result = '';
			if ($Options) foreach($Options as $Option) {
				if ($Result) $Result .= ", ";
				$Result .= "{$Option->Title} ({$Option->Value})";
			}
			return $Result;
		}
		return false;
	}
}