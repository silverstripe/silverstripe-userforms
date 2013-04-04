<?php

/**
 * Allows a user to add a field that can be used to upload a file.
 *
 * @package userforms
 */

class EditableFileField extends EditableFormField {
	
	private static $singular_name = 'File Upload Field';
	
	private static $plural_names = 'File Fields';
	
	public function getFormField() {
		$field = new FileField($this->Name, $this->Title);

		return $field;
	}
	
	
	public function getSubmittedFormField() {
		return new SubmittedFileField();
	}
}