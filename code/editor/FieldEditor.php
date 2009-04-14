<?php
/**
 * Allows CMS user to create forms dynamically.
 * @package forms
 * @subpackage fieldeditor
 */
class FieldEditor extends FormField {
	
	protected $readonly = false;
	
	function isReadonly() {
		return $this->readonly;
	}
	
	function performReadonlyTransformation() {
		$clone = clone $this;
		$clone->setReadonly(true);
		return $clone;
	}
	
	function makeReadonly() {
		return $this->performReadonlyTransformation();
	}
	
	function FieldHolder() {
		return $this->renderWith("FieldEditor");
	}
	
	function Fields() {
		Requirements::css("userform/css/FieldEditor.css");
		Requirements::javascript("userform/javascript/FieldEditor.js");
		
		$relationName = $this->name;
		
		$fields = $this->form->getRecord()->$relationName();
		
		if( $this->readonly ) {
			$readonlyFields = new DataObjectSet();
			
			foreach( $fields as $field ) {
				$field->setEditor( $this );
				$readonlyFields->push( $field->makeReadonly() );
			}
				
			$fields = $readonlyFields;
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
			$output = new DataObjectSet();
			foreach($fields as $field => $title) {
				// get the nice title and strip out field
				$niceTitle = trim(str_ireplace("Field", "", eval("return $title::\$singular_name;"))); 
				
				// keep old javascript happy
				$title = trim(str_ireplace("Editable", "", $title));
				
				$output->push(new ArrayData(array(
					'ClassName' => $title,
					'Title' => "$niceTitle"
				)));
			}
			return $output;
		}
		return false;
	}
	
	
	function saveInto(DataObject $record) {
		
		$name = $this->name;
		$fieldSet = $record->$name();		        
		
		// @todo shouldn't we deal with customFormActions on that object?
		$record->EmailTo = $_REQUEST[$name]['EmailTo'];
		$record->EmailOnSubmit = isset( $_REQUEST[$name]['EmailOnSubmit'] ) ? "1" : "0";
		$record->SubmitButtonText = $_REQUEST[$name]['SubmitButtonText'];
		$record->ShowClearButton = isset($_REQUEST[$name]['ShowClearButton']) ? "1" : "0";
		
		// store the field IDs and delete the missing fields
		// alternatively, we could delete all the fields and re add them
		$missingFields = array();
        
	    foreach( $fieldSet as $existingField ){
	    	$missingFields[$existingField->ID] = $existingField;
	    }    	    
         
	   	// write the new fields to the database
		if($_REQUEST[$name]){
			foreach( array_keys( $_REQUEST[$name] ) as $newEditableID ) {
				$newEditableData  = $_REQUEST[$name][$newEditableID];
				
				// `ParentID`=0 is for the new page
		  		$editable = DataObject::get_one( 'EditableFormField', "(`ParentID`='{$record->ID}' OR `ParentID`=0) AND `EditableFormField`.`ID`='$newEditableID'" ); 
		  		
		  		// check if we are updating an existing field
		  		if( $editable && isset($missingFields[$editable->ID]))
		  			unset( $missingFields[$editable->ID] );
		  		
		  		// create a new object
		  		// this should now be obsolete
		  		if(!$editable && !empty($newEditableData['Type']) && class_exists($newEditableData['Type'])) {
		  			$editable = new $newEditableData['Type']();
		  			$editable->ID = 0;
		  			$editable->ParentID = $record->ID;
		  			
		  			if(!is_subclass_of($editable, 'EditableFormField')) {
		  				$editable = null;
		  			}
		  		}
		  		
		  		if($editable) {
		  			if($editable->ParentID == 0) {
		  				$editable->ParentID = $record->ID;
		  			}
		  			$editable->populateFromPostData($newEditableData);
		  			//$editable->write();
		  		}
		    }
		}
    
    	// remove the fields not saved
    	foreach($missingFields as $removedField) {
    		if(is_numeric($removedField->ID)) $removedField->delete();
    	}
        
    	if($record->hasMethod('customFormSave')) {
			$record->customFormSave( $_REQUEST[$name], $record );
		}	
		//$record->writeWithoutVersion();
		
		if($record->hasMethod( 'processNewFormFields')) {
			$record->processNewFormFields();
		}
	}

	function addfield() {
		// get the last field in this form editor
		$parentID = $this->form->getRecord()->ID;
		$lastField = DataObject::get('EditableFormField', "`ParentID`='$parentID'", "`Sort` DESC", null, 1 );
		
		$nextSort = 1;
		
		// the new sort value is the value of the last sort + 1 if a field exists
		if( $lastField )
			$nextSort += $lastField->Sort; 
		
		$className = "Editable" . ucfirst($_REQUEST['Type']);
		$name = $this->name;
		if(is_subclass_of($className, "EditableFormField")) {
			$e = new $className();
			// $fields = $this->form->getRecord()->$name()->Count();
			// $e->Name = $this->name . "[NewFields][]";
			// Debug::show($fields);
			
			/*if( $this->form->getRecord()->hasMethod('addField') )
				$this->form->getRecord()->addField( $e );
			else*/
				$e->ParentID = $this->form->getRecord()->ID;
			
			//Debug::show($e);
			$e->write();
			//$e->ID = "new-" . ( $_REQUEST['NewID'] + 1 );
			$e->Name = $e->class . $e->ID;
			$e->write();
			
			return $e->EditSegment();
		} else {
			user_error("FieldEditor::addfield: Tried to create a field of class '$className'", E_USER_ERROR);
		}
	}
	
	function adddropdownfield() {
		return $this->addNewField( new EditableDropdown() );		
	}
	
	function addcheckboxfield() {
		return $this->addNewField( new EditableCheckbox() );		
	}
	
	protected $haveFormOptions = true;
	
	function setHaveFormOptions($bool){
		$this->haveFormOptions = $bool;
	}
	
	function getHaveFormOptions(){
		return $this->haveFormOptions;
	}
	
	function FormOptions() {
		if($this->haveFormOptions){
		    if($this->form->getRecord()->hasMethod('customFormActions')) {
		        $newFields = $this->form->getRecord()->customFormActions($this->readonly);
		        
		        foreach( $newFields as $newField ) {
		        	$newField->setName( "{$this->name}[{$newField->Name()}]" );
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