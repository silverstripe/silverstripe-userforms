<?php
/**
 * A form field allowing a user to customize and create form fields.
 * for saving into a {@link UserDefinedForm}
 *
 * @package userforms
 */

class FieldEditor extends FormField {

	public static $allowed_actions = array(
		'addfield',
		'addoptionfield'
	);
	
	/**
	 * Field Editor Template
	 *
	 * @return String
	 */
	public function FieldHolder($properties = array()) {
		$this->setAttribute('data-add-url', '\''.Controller::join_links($this->Link('addfield').'\''));
		return $this->renderWith("FieldEditor");
	}
	
	/**
	 * Returns whether a user can edit the form
	 *
	 * @return boolean
	 */
	public function canEdit($member = null) {
		if($this->readonly) return false;
		
		return $this->form->getRecord()->canEdit();
	}
	
	/**
	 * Returns whether a user delete a field in the form. The {@link EditableFormField}s
	 * check if they can delete themselves but this counts as an {@link self::canEdit()}
	 * function rather than a delete
	 *
	 * @return boolean
	 */
	public function canDelete($member = null) {
		if($this->readonly) return false;
		
		return $this->form->getRecord()->canEdit();
	}

	/**
	 * Transform this form field to a readonly version.
	 *
	 * @return ViewableData_Customised
	 */
	public function performReadonlyTransformation() {
		$clone = clone $this;
		$clone->readonly = true;
		$fields = $clone->Fields();
		if($fields) foreach($fields as $field) {
			$field->setReadonly();
		}
		
		return $clone->customise(array('Fields' => $fields));
	}
	
	/**
	 * Return the fields for the user forms
	 * 
	 * @return DataObjectSet
	 */
	public function Fields() {
		// Don't return any fields unless we actually have the dependent parameters set on the form field
		if($this->form && $this->form->getRecord() && $this->name) {
			$relationName = $this->name;
			$fields = $this->form->getRecord()->getComponents($relationName);
		
			if($fields) {
				foreach($fields as $field) {
					if(!$this->canEdit() && is_a($field, 'FormField')) {
						$fields->remove($field);
						$fields->push($field->performReadonlyTransformation());
					}
				}
			}
			
			return $fields;
		}
	}
	
	/**
	 * Return a DataObjectSet of all the addable fields to populate 
	 * the add field menu
	 * 
	 * @return DataObjectSet
	 */
	public function CreatableFields() {
		$fields = ClassInfo::subclassesFor('EditableFormField');

		if($fields) {
			array_shift($fields); // get rid of subclass 0
			asort($fields); // get in order
			$output = new ArrayList();
			foreach($fields as $field => $title) {
				// get the nice title and strip out field
				$niceTitle = trim(eval("return $title::\$singular_name;")); 
				if($niceTitle) {
					$output->push(new ArrayData(array(
						'ClassName' => $field,
						'Title' => "$niceTitle"
					)));
				}
			}
			return $output;
		}
		return false;
	}

	/**
	 * Handles saving the page. Needs to keep an eye on fields
	 * and options which have been removed / added 
	 *
	 * @param DataObject Record to Save it In
	 */
	public function saveInto(DataObjectInterface $record) {
		$name = $this->name;
		$fieldSet = $record->$name();
		
		// store the field IDs and delete the missing fields
		// alternatively, we could delete all the fields and re add them
		$missingFields = array();

		foreach($fieldSet as $existingField) {
			$missingFields[$existingField->ID] = $existingField;
		}

		if(isset($_REQUEST[$name]) && is_array($_REQUEST[$name])) {
			foreach($_REQUEST[$name] as $newEditableID => $newEditableData) {
				if(!is_numeric($newEditableID)) continue;
				
				// get it from the db
				$editable = DataObject::get_by_id('EditableFormField', $newEditableID); 

				// if it exists in the db update it
				if($editable) {
			
					// remove it from the removed fields list
					if(isset($missingFields[$editable->ID]) && isset($newEditableData) && is_array($newEditableData)) {
						unset($missingFields[$editable->ID]);
					}

					// set form id
					if($editable->ParentID == 0) {
						$editable->ParentID = $record->ID;
					}
					
					// save data
					$editable->populateFromPostData($newEditableData);
				}
			}
		}

		// remove the fields not saved
		if($this->canEdit()) {
			foreach($missingFields as $removedField) {
				if(is_numeric($removedField->ID)) {
						// check we can edit this
						$removedField->delete();
					}
			}
		}
	}
	
	/**
	 * Add a field to the field editor. Called via a ajax get request from the userdefinedform javascript
	 *
	 * @return bool|html
	 */
	public function addfield() {
		// get the last field in this form editor
		$parentID = $this->form->getRecord()->ID;
		
		if($parentID) {
			$parentID = Convert::raw2sql($parentID);
			
			$highestSort = DB::query("SELECT MAX(\"Sort\") FROM \"EditableFormField\" WHERE \"ParentID\" = '$parentID'");
				
			$sort = $highestSort->value() + 1;

			$className = (isset($_REQUEST['Type'])) ? $_REQUEST['Type'] : '';
			if(!$className) user_error('Please select a field type to created', E_USER_WARNING);

			if(is_subclass_of($className, "EditableFormField")) {
				$field = new $className();
				$field->write();
				$field->ParentID = $this->form->getRecord()->ID;
				$field->Name = $field->class . $field->ID;
				$field->Sort = $sort;
				$field->write();
				return $field->EditSegment();
			}
		}
		return false;
	}
	
	/**
	 * Return the html for a field option such as a 
	 * dropdown field or a radio check box field
	 *
	 * @return bool|html
	 */
	public function addoptionfield() {
		// passed via the ajax
		$parent = (isset($_REQUEST['Parent'])) ? $_REQUEST['Parent'] : false;

		// work out the sort by getting the sort of the last field in the form +1
		if($parent) {
			$sql_parent = Convert::raw2sql($parent);
			
			$highestSort = DB::query("SELECT MAX(\"Sort\") FROM \"EditableOption\" WHERE \"ParentID\" = '$sql_parent'");

			$sort = $highestSort->value() + 1;
			
			if($parent) {
				$object = new EditableOption();
				$object->write();
				$object->ParentID = $parent;
				$object->Sort = $sort;
				$object->Name = 'option' . $object->ID;
				$object->write();
				return $object->EditSegment();
			}
		}
		return false;
	}
}
