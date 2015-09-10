<?php


/**
 * Declares a condition that determines whether an email can be sent to a given recipient
 */
class UserDefinedForm_EmailRecipientCondition extends DataObject {

	/**
	 * List of options
	 *
	 * @config
	 * @var array
	 */
	private static $condition_options = array(
		"IsBlank" => "Is blank",
		"IsNotBlank" => "Is not blank",
		"Equals" => "Equals",
		"NotEquals" => "Doesn't equal"
	);

	private static $db = array(
		'ConditionOption' => 'Enum("IsBlank,IsNotBlank,Equals,NotEquals")',
		'ConditionValue' => 'Varchar'
	);

	private static $has_one = array(
		'Parent' => 'UserDefinedForm_EmailRecipient',
		'ConditionField' => 'EditableFormField'
	);

	/**
	 * Determine if this rule matches the given condition
	 *
	 * @param array $data
	 * @param Form $form
	 * @return bool
	 */
	public function matches($data, $form) {
		$fieldName = $this->ConditionField()->Name;
		$fieldValue = isset($data[$fieldName]) ? $data[$fieldName] : null;
		switch($this->ConditionOption) {
			case 'IsBlank':
				return empty($fieldValue);
			case 'IsNotBlank':
				return !empty($fieldValue);
			default:
				$matches = is_array($fieldValue)
					? in_array($this->ConditionValue, $fieldValue)
					: $this->ConditionValue === (string) $fieldValue;
				return ($this->ConditionOption === 'Equals') === (bool)$matches;
		}
	}
}