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
            $link = $this->getLink();
            return (!empty($link)) ? '<a href="'.$link.'">'. _t('SubmittedFileField.DOWNLOADFILE', 'Download File') .'</a>' : '';
	}

	/**
	 * Return the Link object for this field
	 * 
	 * @return String
	 */
	function getLink() {
            if ($this->UploadedFile()){
                // Test if there is a filename, not only a filepath to the assets folder
                return ($this->UploadedFile()->getFilename() != ASSETS_DIR.'/') ? $this->UploadedFile()->URL : '';
            }
            return '';
	}	
}