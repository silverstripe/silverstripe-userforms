<?php
/**
 * Allows CMS user to create forms dynamically.
 *
 * @package userforms
 */
class FieldEditor extends FormField {
	
	protected $hasFormOptions = true;

	function FieldHolder() {
		return $this->renderWith("FieldEditor");
	}
	
	/**
	 * Can a user edit this field
	 *
	 * @return bool
	 */
	public function canEdit() {
		return $this->form->getRecord()->canEdit();
	}
	function Fields() {
		Requirements::css("userforms/css/FieldEditor.css");
		Requirements::javascript("jsparty/jquery/ui/ui.core.js");
		Requirements::javascript("jsparty/jquery/ui/ui.sortable.js");
		Requirements::javascript("userforms/javascript/UserForm.js");
		
		$relationName = $this->name;
		
		$fields = $this->form->getRecord()->$relationName();
		
		foreach($fields as $field) {
			if(!$this->canEdit()) {
				if(is_a($field, 'FormField')) {
					$readonlyFields->push($field->performReadonlyTransformation());
				}
			}
			
			$field->setEditor($this);
		}
		return $fields;
	}
	
	/**
	 * Return a DataObjectSet of all the addable fields to populate 
	 * the add field menu
	 * 
	 * @return DataObjectSet
	 */
	function CreatableFields() {
		$fields = ClassInfo::subclassesFor('EditableFormField');

		if($fields) {
			array_shift($fields); // get rid of subclass 0
			asort($fields); // get in order
			$output = new DataObjectSet();
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
	function saveInto(DataObject $record) {
		$name = $this->name;
		$fieldSet = $record->$name();		        
		
		// @todo shouldn't we deal with customFormActions on that object?
		$record->EmailOnSubmit = isset( $_REQUEST[$name]['EmailOnSubmit'] ) ? "1" : "0";
		$record->SubmitButtonText = isset($_REQUEST[$name]['SubmitButtonText']) ? $_REQUEST[$name]['SubmitButtonText'] : "";
		$record->ShowClearButton = isset($_REQUEST[$name]['ShowClearButton']) ? "1" : "0";
		
		// store the field IDs and delete the missing fields
		// alternatively, we could delete all the fields and re add them
		$missingFields = array();

		foreach($fieldSet as $existingField) {
			$missingFields[$existingField->ID] = $existingField;
		}

		if($_REQUEST[$name]) {
			foreach(array_keys($_REQUEST[$name]) as $newEditableID) {
				if(!is_numeric($newEditableID)) continue;
				
				$newEditableData = $_REQUEST[$name][$newEditableID];
				
				if(defined('Database::USE_ANSI_SQL')) {
			  		$editable = DataObject::get_one('EditableFormField', "(\"ParentID\" = '{$record->ID}' OR \"ParentID\" = '0') AND \"EditableFormField\".\"ID\" = '$newEditableID'"); 
				} else {
			  		$editable = DataObject::get_one('EditableFormField', "(`ParentID` = '{$record->ID}' OR `ParentID` = 0) AND `EditableFormField`.`ID`='$newEditableID'" ); 
				}
	
		  		// check if we are updating an existing field. One odd thing is a 'deleted' field
		 		// still exists in the post data (ID) so we need to check for type.
				
		  		if($editable && isset($missingFields[$editable->ID]) && isset($newEditableData)) {
		  			// check if it has been labelled as deleted
					if(isset($newEditableData['Title']) && $newEditableData['Title'] != 'field-node-deleted') {
						unset($missingFields[$editable->ID]);
					}
				}
				
				if($editable) {
					if($editable->ParentID == 0) {
						$editable->ParentID = $record->ID;
					}
					$editable->populateFromPostData($newEditableData);
				}
		    }
		}

    	// remove the fields not saved
    	foreach($missingFields as $removedField) {
    		if(is_numeric($removedField->ID)) $removedField->delete();
    	}

    	if($record->hasMethod('customFormSave')) {
			$record->customFormSave($_REQUEST[$name], $record);
		}	
		
		if($record->hasMethod('processNewFormFields')) {
			$record->processNewFormFields();
		}
	}
	
	/**
	 * Add a field to the field editor. Called via a ajax get request
	 * from the userdefinedform javascript
	 *
	 * @return bool|html
	 */
	public function addfield() {
		// get the last field in this form editor
		$parentID = $this->form->getRecord()->ID;
		
		if($parentID) {
			$parentID = Convert::raw2sql($parentID); // who knows what could happen
			
			if(defined('Database::USE_ANSI_SQL')) {
				$highestSort = DB::query("SELECT MAX(\"Sort\") FROM \"EditableFormField\" WHERE \"ParentID\" = '$parentID'");
			} else {
				$highestSort = DB::query("SELECT MAX(Sort) FROM EditableFormField WHERE ParentID = '$parentID'");
			}
			
			$sort = $highestSort->value() + 1;

			$className = (isset($_REQUEST['Type'])) ? $_REQUEST['Type'] : '';

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
			
			if(defined('Database::USE_ANSI_SQL')) {
				$highestSort = DB::query("SELECT MAX(\"Sort\") FROM \"EditableOption\" WHERE \"ParentID\" = '$sql_parent'");
			} else {
				$highestSort = DB::query("SELECT MAX(Sort) FROM EditableOption WHERE ParentID = '$sql_parent'");
			}
			
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

	function setHasFormOptions($bool){
		$this->hasFormOptions = $bool;
	}
	
	function hasFormOptions(){
		return $this->hasFormOptions;
	}
	
	function FormOptions() {
		if($this->hasFormOptions()){
		    if($this->form->getRecord()->hasMethod('customFormActions')) {
		        $newFields = $this->form->getRecord()->customFormActions($this->readonly);

		        foreach($newFields as $newField) {
		        	$newField->setName("{$this->name}[{$newField->Name()}]" );
		        }
			    if($this->readonly) {
					$newFields = $newFields->makeReadonly();
			    }
			    return $newFields;
		    }
    	}
	}
}
?>