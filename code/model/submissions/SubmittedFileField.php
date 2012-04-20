<?php

/**
 * A file uploaded on a {@link UserDefinedForm} and attached to a single 
 * {@link SubmittedForm}
 *
 * @package userforms
 */

class SubmittedFileField extends SubmittedFormField {
	
	static $has_one = array(
		"UploadedFile" => "File"
	);
	
	/**
	 * Return the value of this field for inclusion into things such as reports
	 * 
	 * @return string
	 */
	function getFormattedValue() {
		$link = $this->getLink();
		$title = _t('SubmittedFileField.DOWNLOADFILE', 'Download File');
		
		if($link) {
			return sprintf('<a href="%s">%s</a>', $link, $title);
		}
		
		return false;
	}

	/**
	 * Return the link for the file attached to this submitted form field
	 * 
	 * @return string
	 */
	function getLink() {
		if($file = $this->UploadedFile()) {
			if(trim($file->getFilename(), '/') != trim(ASSETS_DIR,'/'))  {
				return $this->UploadedFile()->URL;
			}
		}
	}	
}