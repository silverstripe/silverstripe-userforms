<?php

/**
 * Service to support upgrade of userforms module
 */
class UserFormsUpgradeService
{

    /**
     * @var bool
     */
    protected $quiet;

    public function run()
    {
        $this->log("Upgrading formfield rules and custom settings");

        // List of rules that have been created in all stages
        $fields = Versioned::get_including_deleted('EditableFormField');
        foreach ($fields as $field) {
            $this->upgradeField($field);
        }
    }

    /**
     * Migrate a versioned field in all stages
     *
     * @param EditableFormField $field
     */
    protected function upgradeField(EditableFormField $field)
    {
        $this->log("Upgrading formfield ID = ".$field->ID);

        // Check versions this field exists on
        $filter = sprintf('"EditableFormField"."ID" = \'%d\' AND "Migrated" = 0', $field->ID);
        $stageField = Versioned::get_one_by_stage('EditableFormField', 'Stage', $filter);
        $liveField = Versioned::get_one_by_stage('EditableFormField', 'Live', $filter);

        if ($stageField) {
            $this->upgradeFieldInStage($stageField, 'Stage');
        }

        if ($liveField) {
            $this->upgradeFieldInStage($liveField, 'Live');
        }
    }

    /**
     * Migrate a versioned field in a single stage
     *
     * @param EditableFormField $field
     * @param stage $stage
     */
    protected function upgradeFieldInStage(EditableFormField $field, $stage)
    {
        Versioned::reading_stage($stage);

        // Migrate field rules
        $this->migrateRules($field, $stage);

        // Migrate custom settings
        $this->migrateCustomSettings($field, $stage);

        // Flag as migrated
        $field->Migrated = true;
        $field->write();
    }

    /**
     * Migrate custom rules for the given field
     *
     * @param EditableFormField $field
     * @param string $stage
     */
    protected function migrateRules(EditableFormField $field, $stage)
    {
        $rulesData = $field->CustomRules
            ? unserialize($field->CustomRules)
            : array();

        // Skip blank rules or fields with custom rules already
        if (empty($rulesData) || $field->DisplayRules()->count()) {
            return;
        }

        // Check value of this condition
        foreach ($rulesData as $ruleDataItem) {
            if (empty($ruleDataItem['ConditionOption']) || empty($ruleDataItem['Display'])) {
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
     * Migrate custom settings for the given field
     *
     * @param EditableFormField $field
     * @param string $stage
     */
    protected function migrateCustomSettings(EditableFormField $field, $stage)
    {
        // Custom settings include:
        // - ExtraClass
        // - RightTitle
        // - ShowOnLoad (show or '' are treated as true)
        //
        // - CheckedDefault (new field on EditableCheckbox - should be read from old "default" value)
        // - Default (EditableCheckbox)
        // - DefaultToToday (EditableDateField)
        // - Folder (EditableFileField)
        // - Level (EditableFormHeading)
        // - HideFromReports (EditableFormHeading / EditableLiteralField)
        // - Content (EditableLiteralField)
        // - GroupID (EditableMemberListField)
        // - MinValue (EditableNumericField)
        // - MaxValue (EditableNumericField)
        // - MinLength (EditableTextField)
        // - MaxLength (EditableTextField)
        // - Rows (EditableTextField)

        $customSettings = $field->CustomSettings
            ? unserialize($field->CustomSettings)
            : array();

        // Skip blank rules or fields with custom rules already
        if (empty($customSettings)) {
            return;
        }

        $field->migrateSettings($customSettings);
        $field->write();
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
    protected function findOrCreateRule(EditableFormField $field, $stage, $conditionOption, $display, $conditionFieldName, $value)
    {
        // Get id of field
        $conditionField = $conditionFieldName
            ? EditableFormField::get()->filter('Name', $conditionFieldName)->first()
            : null;

        // If live, search stage record for matching one
        if ($stage === 'Live') {
            $list = Versioned::get_by_stage('EditableCustomRule', 'Stage')
                ->filter(array(
                    'ParentID' => $field->ID,
                    'ConditionFieldID' => $conditionField ? $conditionField->ID : 0,
                    'Display' => $display,
                    'ConditionOption' => $conditionOption
                ));
            if ($value) {
                $list = $list->filter('FieldValue', $value);
            } else {
                $list = $list->where('"FieldValue" IS NULL OR "FieldValue" = \'\'');
            }
            $rule = $list->first();
            if ($rule) {
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

    public function log($message)
    {
        if ($this->getQuiet()) {
            return;
        }
        if (Director::is_cli()) {
            echo "{$message}\n";
        } else {
            echo "{$message}<br />";
        }
    }

    /**
     * Set if this service should be quiet
     *
     * @param bool $quiet
     * @return $ths
     */
    public function setQuiet($quiet)
    {
        $this->quiet = $quiet;
        return $this;
    }

    public function getQuiet()
    {
        return $this->quiet;
    }
}
