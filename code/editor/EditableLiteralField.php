<?php

/**
 * Editable Literal Field. A literal field is just a blank slate where
 * you can add your own HTML / Images / Flash
 * 
 * @package userforms
 */

class EditableLiteralField extends EditableFormField {
	
	static $singular_name = 'HTML Block';
	
	static $plural_name = 'HTML Blocks';
	
	function ExtraOptions() {
		// eventually replace hard-coded "Fields"?
		$baseName = "Fields[$this->ID]";
		
		$extraFields = new FieldSet();
		$extraFields->push(new TextareaField($baseName . "[CustomSettings][Content]", "Text", 4, 20, $this->getSetting('Content')));
		
		return $extraFields;		
	}

	function getFormField() {
		return new LiteralField("LiteralField[$this->ID]", 
			"<div class='field text'><label class='left'>$this->Title</label><div class='middleColumn literalFieldArea'>". $this->getSetting('Content') ."</div></div>");
	}
}
?>