<?php

/**
 * Specifies that this ends a group of fields
 */
class EditableFieldGroupEnd extends EditableFormField {

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
			_t('EditableFieldGroupEnd.FIELD_GROUP_END', 'Field Group (end)')
		);
	}

	public function getInlineTitleField($column) {
		return HiddenField::create($column);
	}

	public function getFormField() {
		return null;
	}

	public function showInReports() {
		return false;
	}

	public function canEdit($member = null) {
		return false;
	}
	
}
