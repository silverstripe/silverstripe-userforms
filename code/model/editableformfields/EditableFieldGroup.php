<?php

/**
 * Specifies that this ends a group of fields
 */
class EditableFieldGroup extends EditableFormField {

	/**
	 * Disable selection of group class
	 *
	 * @config
	 * @var bool
	 */
	private static $hidden = true;

	public function getCMSFields() {
		$fields = parent::getCMSFields();
		$fields->removeByName(array('MergeField', 'Default', 'Validation', 'DisplayRules'));
		return $fields;
	}

	public function getInlineClassnameField($column, $fieldClasses) {
		return new LabelField(
			$column,
			_t('EditableFieldGroup.FIELD_GROUP_START', 'Field Group (start)')
		);
	}

	public function showInReports() {
		return false;
	}

	public function getFormField() {
		$field = UserFormsGroupField::create()
			->setTitle($this->EscapedTitle ?: false);
		$this->doUpdateFormField($field);
		return $field;
	}

	protected function updateFormField($field) {
		// set the right title on this field
		if($this->RightTitle) {
			// Since this field expects raw html, safely escape the user data prior
			$field->setRightTitle(Convert::raw2xml($this->RightTitle));
		}
		
		// if this field has an extra class
		if($field->ExtraClass) {
			$field->addExtraClass($field->ExtraClass);
		}
	}
	
}
