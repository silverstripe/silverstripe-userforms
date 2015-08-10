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
		$fields->removeByName('StepID');
		$fields->removeByName('Default');
		$fields->removeByName('Validation');
		$fields->removeByName('CustomRules');

		return $fields;
	}

	/**
	 * @return FormField
	 */
	public function getFormField() {
		return CompositeField::create()->setTitle($this->Title);
	}

	/**
	 * @return boolean
	 */
	public function showInReports() {
		return false;
	}
}
