<?php
/**
 * Contents of an UserDefinedForm submission
 *
 * @package userforms
 */

class SubmittedForm extends DataObject {
	
	public static $has_one = array(
		"SubmittedBy" => "Member",
		"Parent" => "UserDefinedForm",
	);
	
	public static $has_many = array( 
		"Values" => "SubmittedFormField"
	);

	public static $summary_fields = array(
		'ID',
		'Created'
	);
	
	/**
	 * Returns the value of a relation or, in the case of this form, the value
	 * of a given child {@link SubmittedFormField}
	 * 
	 * @param string
	 * 
	 * @return mixed
	 */
	public function relField($fieldName) {
		// default case
		if($value = parent::relField($fieldName)) {
			return $value;
		}

		// check values for a form field with the matching name.
		$formField = SubmittedFormField::get()->filter(array(
			'ParentID' => $this->ID,
			'Name' => $fieldName
		))->first();

		if($formField) {
			return $formField->getFormattedValue();
		}
	}

	/**
	 * @return FieldList
	 */
	public function getCMSFields() {
		$fields = parent::getCMSFields();
		$fields->removeByName('Values');

		$values = new GridField(
			"Values", 
			"SubmittedFormField",
			 $this->Values()->sort('Created', 'ASC')
		);

		$config = new GridFieldConfig();
		$config->addComponent(new GridFieldDataColumns());
		$config->addComponent(new GridFieldExportButton());
		$config->addComponent(new GridFieldPrintButton());
		$values->setConfig($config);

		$fields->addFieldToTab('Root.Main', $values);
		
		return $fields;
	}

	/**
	 * Before we delete this form make sure we delete all the
	 * field values so that we don't leave old data round
	 *
	 * @return void
	 */
	protected function onBeforeDelete() {
		if($this->Values()) {
			foreach($this->Values() as $value) {
				$value->delete();
			}
		}
		
		parent::onBeforeDelete();
	}
}