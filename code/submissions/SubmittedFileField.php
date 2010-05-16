<?php
/**
 * A file uploaded on a UserDefinedForm field
 *
 * @package userforms
 */

class SubmittedFileField extends SubmittedFormField {
	
	static $has_one = array(
		"UploadedFile" => "File"
	);
	
	/**
	 * Return the Value of this Field
	 * 
	 * @return String
	 */
	function getFormattedValue() {
		return '<a href="'.$this->getLink().'">'. _t('SubmittedFileField.DOWNLOADFILE', 'Download File') .'</a>';
	}

	/**
	 * Return the Link object for this field
	 * 
	 * @return String
	 */
	function getLink() {
		return ($this->UploadedFile()) ? $this->UploadedFile()->URL : "";
	}	
}