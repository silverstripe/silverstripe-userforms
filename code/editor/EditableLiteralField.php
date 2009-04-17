<?php

/**
 * Editable Literal Field. A literal field is just a blank slate where
 * you can add your own HTML / Images / Flash
 * 
 * @package userforms
 */

class EditableLiteralField extends EditableFormField {
	
	static $db = array(
		'Content' => 'Text'
	);
	
	static $singular_name = 'HTML Block';
	
	static $plural_name = 'HTML Blocks';
	
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
	
	function createField() {
		return new LiteralField("LiteralField[$this->ID]", 
			"<div class='field text'><label class='left'>$this->Title</label><div class='middleColumn literalFieldArea'>". $this->Content."</div></div>");
	}
}
?>