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
	 * @config
	 * @var array
	 */
	private static $has_many = array(
		'Fields' => 'EditableFormField'
	);

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
		return false;
	}

	/**
	 * @return boolean
	 */
	public function showInReports() {
		return false;
	}
}
