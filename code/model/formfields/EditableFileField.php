<?php

/**
 * Allows a user to add a field that can be used to upload a file.
 *
 * @package userforms
 */

class EditableFileField extends EditableFormField {
	
	private static $singular_name = 'File Upload Field';
	
	private static $plural_names = 'File Fields';
	
	function getFieldConfiguration() {
		$field = parent::getFieldConfiguration();

		$folder = ($this->getSetting('Folder')) ? $this->getSetting('Folder') : '';

		$folderList = array();
		$folders = Folder::get()->filter('ParentID',0);
		$mapMethod = function( $ID,$level=0 ) use ( &$mapMethod ) {
			$folders = Folder::get()->filter('ParentID',$ID);
			$result = array();
			foreach ($folders as $folder) {
				$result[$folder->ID] = str_repeat('- ',$level).$folder->Title;
				$children = $mapMethod($folder->ID,$level+1);
				if (count($children) > 0) {
					$result = $result + $children;
				}
			}
			return $result;
		};
		$map = $mapMethod(0);

		$folderField = DropdownField::create($this->getSettingName("Folder"), _t('EditableUploadField.FOLDER', 'Select upload folder'),$map);
		$folderField->setValue($folder);
		$field->push($folderField);

		return $field;
	}
	
	public function getFormField() {
		$field = new FileField($this->Name, $this->Title);
		if ($this->getSetting('Folder')) {
			$folder = DataObject::get_by_id('Folder',$this->getSetting('Folder'));
			$field->setFolderName(preg_replace("/^assets\//","",$folder->Filename));
		}
		return $field;
	}
	
	
	public function getSubmittedFormField() {
		return new SubmittedFileField();
	}
}
