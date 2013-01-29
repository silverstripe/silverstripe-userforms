<?php

/**
 * A file uploaded on a {@link UserDefinedForm} and attached to a single 
 * {@link SubmittedForm}.
 *
 * @package userforms
 */

class SubmittedFileField extends SubmittedFormField {
	
	public static $has_one = array(
		"UploadedFile" => "File"
	);
	
	/**
	 * Return the value of this field for inclusion into things such as 
	 * reports.
	 * 
	 * @return string
	 */
	public function getFormattedValue() {
		$name = $this->getName();
		$link = $this->getLink();
		$title = _t('SubmittedFileField.DOWNLOADFILE', 'Download File');
		
		if($link) {
			return sprintf(
				'%s - <a href="%s" target="_blank">%s</a>', 
				$name, $link, $title
			);
		}
		
		return false;
	}
	
	/**
	 * Return the value for this field in the CSV export.
	 *
	 * @return string
	 */
	public function getExportValue() {
		return ($link = $this->getLink()) ? $link : "";
	}

	/**
	 * Return the value for the database, link to the file is stored as a
	 * relation so value for the field can be null.
	 *
	 * @return string
	 */
	public function getValueFromData() {
		return null;
	}

	/**
	 * Return the link for the file attached to this submitted form field.
	 * 
	 * @return string
	 */
	public function getLink() {
		if($file = $this->UploadedFile()) {
			if(trim($file->getFilename(), '/') != trim(ASSETS_DIR,'/')) {
				return $this->UploadedFile()->URL;
			}
		}
	}
	
	/**
	 * Return the name of the file, if present
	 *
	 * @return string
	 */
	public function getName() {
		if($this->UploadedFile()) {
			return $this->UploadedFile()->Name;
		}
	}
}