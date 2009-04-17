<?php
/**
 * EditableDateField
 *
 * Allows a user to add a date field to the Field Editor
 *
 * @package userforms
 */
class EditableDateField extends EditableFormField {
	static $singular_name = 'Date field';
	static $plural_name = 'Date fields';
	
	function DefaultField() {
		$dmyField = new CalendarDateField( "Fields[{$this->ID}][Default]", "", $this->getField('Default') );
	
		if( $this->readonly )
			$dmyField = $dmyField->performReadonlyTransformation();
			
		return $dmyField;
	}
	
	function populateFromPostData($data) {
		$fieldPrefix = 'Default-';
		
		if( empty( $data['Default'] ) && !empty( $data[$fieldPrefix.'Year'] ) && !empty( $data[$fieldPrefix.'Month'] ) && !empty( $data[$fieldPrefix.'Day'] ) )
			$data['Default'] = $data['Year'] . '-' . $data['Month'] . '-' . $data['Day'];
			
		// Debug::show( $data );
	
		parent::populateFromPostData( $data );
	}
	
	function getFormField() {
		return new CalendarDateField( $this->Name, $this->Title, $this->getField('Default') );
	}
}
?>