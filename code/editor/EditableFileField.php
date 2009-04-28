<?php
/**
 * Allows a user to add a field that can be used to upload a file
 *
 * @package userforms
 */
class EditableFileField extends EditableFormField {
	
	// this needs to be moved.
	static $has_one = array(
		"UploadedFile" => "File"
	);
	
	/**
	 * @see Upload->allowedMaxFileSize
	 * @var int
	 */
	public static $allowed_max_file_size;
	
	/**
	 * @see Upload->allowedExtensions
	 * @var array
	 */
	public static $allowed_extensions = array();
	
	static $singular_name = 'File field';
	
	static $plural_names = 'File fields';
	
	public function getFormField() {
		return new FileField($this->Name, $this->Title);
	}
	
}
?>