<?php

/**
 * Assists with upgrade of userforms to 3.0
 *
 * @author dmooyman
 */
class UserFormsUpgradeTask extends BuildTask {

	protected $title = "UserForms 3.0 Migration Tool";

	protected $description = "Upgrade tool for sites upgrading to userforms 3.0";

	public function run($request) {
		$this->log("Upgrading userforms module");
		$this->upgradeRules();
		$this->log("Done");
	}

	protected function log($message) {
		if(Director::is_cli()) {
			echo "{$message}\n";
		} else {
			echo "{$message}<br />";
		}
	}

	protected function upgradeRules() {
		$this->log("Upgrading formfield rules");

		// Upgrade rules from EditableFormField.CustomRules into dataobjects
		$fields = DB::fieldList('EditableFormField');
		if(!isset($fields['CustomRules'])) {
			return;
		}

		// List of rules that have been created in all stages
		$fields = Versioned::get_including_deleted('EditableFormField');
		foreach($fields as $field) {
			$this->upgradeFieldRules($field);
		}
	}

	/**
	 * Migrate a versioned field in all stages
	 *
	 * @param EditableFormField $field
	 */
	protected function upgradeFieldRules(EditableFormField $field) {
		$this->log("Upgrading formfield ID = ".$field->ID);
	
		// Check versions this field exists on
		$filter = sprintf('"EditableFormField"."ID" = \'%d\'', $field->ID);
		$stageField = Versioned::get_one_by_stage('EditableFormField', 'Stage', $filter);
		$liveField = Versioned::get_one_by_stage('EditableFormField', 'Live', $filter);

		if($stageField) {
			$this->upgradeFieldRulesInStage($stageField, 'Stage');
		}

		if($liveField) {
			$this->upgradeFieldRulesInStage($liveField, 'Live');
		}
	}

	/**
	 * Migrate a versioned field in a single stage
	 *
	 * @param EditableFormField $field
	 * @param stage $stage
	 */
	protected function upgradeFieldRulesInStage(EditableFormField $field, $stage) {
		Versioned::reading_stage($stage);

		// Skip rules with empty data
		$rulesData = $this->getRuleData($field->ID);
		if(empty($rulesData)) {
			return;
		}

		// Skip migrated records
		if($field->CustomRules()->count()) {
			return;
		}

		// Check value of this condition
		foreach($rulesData as $ruleDataItem) {
			if(empty($ruleDataItem['ConditionOption']) || empty($ruleDataItem['Display'])) {
				continue;
			}

			// Get data for this rule
			$conditionOption = $ruleDataItem['ConditionOption'];
			$display = $ruleDataItem['Display'];
			$conditionFieldName = empty($ruleDataItem['ConditionField']) ? null : $ruleDataItem['ConditionField'];
			$value = isset($ruleDataItem['Value'])
				? $ruleDataItem['Value']
				: null;

			// Create rule
			$rule = $this->findOrCreateRule($field, $stage, $conditionOption, $display, $conditionFieldName, $value);
			$this->log("Upgrading rule ID = " . $rule->ID);
		}
	}

	/**
	 * Create or find an existing field with the matched specification
	 *
	 * @param EditableFormField $field
	 * @param string $stage
	 * @param string $conditionOption
	 * @param string $display
	 * @param string $conditionFieldName
	 * @param string $value
	 * @return EditableCustomRule
	 */
	protected function findOrCreateRule(EditableFormField $field, $stage, $conditionOption, $display, $conditionFieldName, $value) {
		// Get id of field
		$conditionField = $conditionFieldName
			? EditableFormField::get()->filter('Name', $conditionFieldName)->first()
			: null;

		// If live, search stage record for matching one
		if($stage === 'Live') {
			$list = Versioned::get_by_stage('EditableCustomRule', 'Stage')
				->filter(array(
					'ParentID' => $field->ID,
					'ConditionFieldID' => $conditionField ? $conditionField->ID : 0,
					'Display' => $display,
					'ConditionOption' => $conditionOption
				));
			if($value) {
				$list = $list->filter('FieldValue', $value);
			} else {
				$list = $list->where('"FieldValue" IS NULL OR "FieldValue" = \'\'');
			}
			$rule = $list->first();
			if($rule) {
				$rule->write();
				$rule->publish("Stage", "Live");
				return $rule;
			}
		}

		// If none found, or in stage, create new record
		$rule = new EditableCustomRule();
		$rule->ParentID = $field->ID;
		$rule->ConditionFieldID = $conditionField ? $conditionField->ID : 0;
		$rule->Display = $display;
		$rule->ConditionOption = $conditionOption;
		$rule->FieldValue = $value;
		$rule->write();
		return $rule;
	}

	/**
	 * Get deserialised rule data for a field
	 *
	 * @param type $id
	 */
	protected function getRuleData($id) {
		$rules = DB::query(sprintf(
			'SELECT "CustomRules" FROM "EditableFormField" WHERE "ID" = %d',
			$id
		))->value();
		return $rules ? unserialize($rules) : array();
	}
}
