<?php
/**
 * A step in multi-page user form
 *
 * @package userforms
 */
class EditableFormStep extends EditableFormField {

	/**
	 * Disable selection of step class
	 *
	 * @config
	 * @var bool
	 */
	private static $hidden = true;

	/**
	 * @config
	 * @var string
	 */
	private static $singular_name = 'Step';

	/**
	 * @config
	 * @var string
	 */
	private static $plural_name = 'Steps';

	/**
	 * @return FieldList
	 */
	public function getCMSFields() {
		$fields = parent::getCMSFields();

		$fields->removeByName(array('MergeField', 'Default', 'Validation', 'DisplayRules'));

		return $fields;
	}

	/**
	 * @return FormField
	 */
	public function getFormField() {
		$field = UserFormsStepField::create()
			->setTitle($this->EscapedTitle);
		$this->doUpdateFormField($field);
		return $field;
	}

	protected function updateFormField($field) {
		// if this field has an extra class
		if($field->ExtraClass) {
			$field->addExtraClass($field->ExtraClass);
		}
	}

	/**
	 * @return boolean
	 */
	public function showInReports() {
		return false;
	}

	public function getInlineClassnameField($column, $fieldClasses) {
		return new LabelField(
			$column,
			_t('EditableFieldGroupEnd.PAGE_BREAK', 'Page Break')
		);
	}
}
