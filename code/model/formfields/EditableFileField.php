<?php

/**
 * Allows a user to add a field that can be used to upload a file.
 *
 * @package userforms
 */

class EditableFileField extends EditableFormField {
	
	private static $singular_name = 'File Upload Field';
	
	private static $plural_names = 'File Fields';

	public function getFieldConfiguration() {
		$field = parent::getFieldConfiguration();
		$folder = ($this->getSetting('Folder')) ? $this->getSetting('Folder') : null;

		$tree = UserformsTreeDropdownField::create(
			$this->getSettingName("Folder"),
			_t('EditableUploadField.SELECTUPLOADFOLDER', 'Select upload folder'),
			"Folder"
		);

		$tree->setValue($folder);

		$field->push($tree);

		return $field;
	}

	public function getFormField() {
		$field = new FileField($this->Name, $this->Title);

		if($this->getSetting('Folder')) {
			$folder = Folder::get()->byId($this->getSetting('Folder'));

			if($folder) {
				$field->setFolderName(
					preg_replace("/^assets\//","", $folder->Filename)
				);
			}
		}

		return $field;
	}
	
	
	public function getSubmittedFormField() {
		return new SubmittedFileField();
	}
}
