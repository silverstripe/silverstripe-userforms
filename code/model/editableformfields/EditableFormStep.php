<?php
/**
 * A step in multi-page user form
 *
 * @package userforms
 */
class EditableFormStep extends EditableFormField {

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

		$fields->removeByName('MergeField');
		$fields->removeByName('Default');
		$fields->removeByName('Validation');
		$fields->removeByName('CustomRules');

		return $fields;
	}

	/**
	 * @return FormField
	 */
	public function getFormField() {
		$field = CompositeField::create()
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
}
