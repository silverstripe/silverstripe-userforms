<?php

/**
 * Allows a user to add a field that can be used to upload a file.
 *
 * @package userforms
 */

class EditableFileField extends EditableFormField {
	
	private static $singular_name = 'File Upload Field';
	
	private static $plural_names = 'File Fields';

	private static $has_one = array(
		'Folder' => 'Folder' // From CustomFields
	);

	/**
	 * Disable file upload fields by default since every implementation
	 * needs to first consider how to secure files uploaded in the webroot.
	 * Please check the installation documenation for more details.
	 */
	private static $hidden = true;

	/**
	 * @return FieldList
	 */
	public function getCMSFields() {
		$fields = parent::getCMSFields();
		
		$fields->addFieldToTab(
			'Root.Main',
			TreeDropdownField::create(
				'FolderID',
				_t('EditableUploadField.SELECTUPLOADFOLDER', 'Select upload folder'),
				'Folder'
			)
		);

		$fields->addFieldToTab("Root.Main", new LiteralField("FileUploadWarning", 
                "<p class=\"message notice\">" . _t("UserDefinedForm.FileUploadWarning", 
                "Files uploaded through this field could be publicly accessible if the exact URL is known")
                . "</p>"), "Type");

		return $fields;
	}

	public function getFormField() {
		$field = FileField::create($this->Name, $this->EscapedTitle)
			->setFieldHolderTemplate('UserFormsField_holder')
			->setTemplate('UserFormsFileField');

		// filter out '' since this would be a regex problem on JS end
		$field->getValidator()->setAllowedExtensions(
			array_filter(Config::inst()->get('File', 'allowed_extensions'))
		);

		$folder = $this->Folder();
		if($folder && $folder->exists()) {
			$field->setFolderName(
				preg_replace("/^assets\//","", $folder->Filename)
			);
		}

		$this->doUpdateFormField($field);

		return $field;
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
	
	public function getSubmittedFormField() {
		return new SubmittedFileField();
	}


	public function migrateSettings($data) {
		// Migrate 'Folder' setting to 'FolderID'
		if(isset($data['Folder'])) {
			$this->FolderID = $data['Folder'];
			unset($data['Folder']);
		}

		parent::migrateSettings($data);
	}
}
