<?php
/**
 * EditableTextField
 *
 * This control represents a user-defined text field in a user defined form
 *
 * @package userforms
 */
class EditableTextField extends EditableFormField {
	
	public static $db = array(
		"Size" => "Int",
		"MinLength" => "Int",
		"MaxLength" => "Int",
		"Rows" => "Int"
	);
	
	public static $size = 32;
	
	public static $min_length = 1;
	
	public static $max_length = 32;
	
	public static $rows = 1;

	static $singular_name = 'Text field';
	
	static $plural_name = 'Text fields';
	
	function __construct( $record = null, $isSingleton = false ) {
		$this->Size = self::$size;
		$this->MinLength = self::$min_length;
		$this->MaxLength = self::$max_length;
		$this->Rows = self::$rows;
		parent::__construct( $record, $isSingleton );
	}
	
	function ExtraOptions() {
		
		// eventually replace hard-coded "Fields"?
		$baseName = "Fields[$this->ID]";
		
		$extraFields = new FieldSet(
			new TextField($baseName . "[Size]", _t('EditableTextField.TEXTBOXLENGTH', 'Length of text box'), (string)$this->Size),
			new FieldGroup(_t('EditableTextField.TEXTLENGTH', 'Text length'),
				new TextField($baseName . "[MinLength]", "", (string)$this->MinLength),
				new TextField($baseName . "[MaxLength]", " - ", (string)$this->MaxLength)
			),
			new TextField($baseName . "[Rows]", _t('EditableTextField.NUMBERROWS', 'Number of rows'), (string)$this->Rows)
		);
		
		foreach( parent::ExtraOptions() as $extraField )
			$extraFields->push( $extraField );
			
		if( $this->readonly )
			$extraFields = $extraFields->makeReadonly();	
			
		return $extraFields;		
	}
	
	function populateFromPostData( $data ) {

		$this->Size = !empty( $data['Size'] ) ? $data['Size'] : self::$size;
		$this->MinLength = !empty( $data['MinLength'] ) ? $data['MinLength'] : self::$min_length;
		$this->MaxLength = !empty( $data['MaxLength'] ) ? $data['MaxLength'] : self::$max_length;
		$this->Rows = !empty( $data['Rows'] ) ? $data['Rows'] : self::$rows;
		parent::populateFromPostData( $data );
	}
	
	function getFormField() {
		return $this->createField();
	}
	
	function getFilterField() {
		return $this->createField( true );
	}
	
	function createField( $asFilter = false ) {
		if( $this->Rows == 1 )
			return new TextField( $this->Name, $this->Title, ( $asFilter ) ? "" : $this->getField('Default'), $this->MaxLength);
		else
			return new TextareaField( $this->Name, $this->Title, $this->Rows, $this->MaxLength, ( $asFilter ) ? "" : $this->getField('Default') );
	}
	
	/**
	 * Populates the default fields. 
	 */
	function DefaultField() {
		$disabled = '';
		if( $this->readonly ){
			$disabled = " disabled=\"disabled\"";
		} else {
			$disabled = '';
		}
		if( $this->Rows == 1 ){
		        return '<div class="field text"><label class="left">'._t('EditableTextField.DEFAULTTEXT', 'Default Text').' </label> <input class="defaultText" name="Fields['.Convert::raw2att( $this->ID ).'][Default]" type="text" value="'.Convert::raw2att( $this->getField('Default') ).'"'.$disabled.' /></div>';
		}else{
			return '<div class="field text"><label class="left">'._t('EditableTextField.DEFAULTTEXT', 'Default Text').' </label> <textarea class="defaultText" name="Fields['.Convert::raw2att( $this->ID ).'][Default]"'.$disabled.'>'.Convert::raw2att( $this->getField('Default') ).'</textarea></div>';
		}
	}
}
?>