<?php

/**
 * Editable Spam Protecter Field. Used with the User Defined Forms module (if 
 * installed) to allow the user to have captcha fields with their custom forms
 * 
 * @package SpamProtection
 */

class EditableLiteralField extends EditableFormField {
	
	static $db = array(
		'Content' => 'Text'
	);
	
	static $singular_name = 'HTML Block';
	static $plural_name = 'HTML Blocks';
	
	function __construct( $record = null, $isSingleton = false ) {

		parent::__construct( $record, $isSingleton );
	}
	
	function ExtraOptions() {
		
		// eventually replace hard-coded "Fields"?
		$baseName = "Fields[$this->ID]";
		
		$extraFields = new FieldSet();
		$extraFields->push(new TextareaField($baseName . "[Content]", "Text", 4, 20, $this->Content));
		
		return $extraFields;		
	}
	
	function populateFromPostData($data) {

		$this->Content = $data['Content'];
		parent::populateFromPostData($data);
	}
	
	function getFormField() {
		return $this->createField();
	}
	
	function getFilterField() {
		return $this->createField(true);
	}
	
	function createField() {
		return new LiteralField("LiteralField[$this->ID]", 
			"<label class='left'>$this->Name</label><div class='middleColumn literalFieldArea'>". $this->Content."</div>");
	}
	/**
	 * Populates the default fields. 
	 */
	function DefaultField() {
		return "";
	}
	
	function EditSegment() {
		return $this->renderWith( $this->class );
	}
}
?>