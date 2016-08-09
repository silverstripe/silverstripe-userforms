<?php

/**
 * A custom rule for showing / hiding an EditableFormField
 * based the value of another EditableFormField.
 *
 * @method EditableFormField Parent()
 * @package userforms
 */
class EditableCustomRule extends DataObject
{

    private static $condition_options = array(
        "IsBlank" => "Is blank",
        "IsNotBlank" => "Is not blank",
        "HasValue" => "Equals",
        "ValueNot" => "Doesn't equal",
        "ValueLessThan" => "Less than",
        "ValueLessThanEqual" => "Less than or equal",
        "ValueGreaterThan" => "Greater than",
        "ValueGreaterThanEqual" => "Greater than or equal"
    );

    private static $db = array(
        'Display' => 'Enum("Show,Hide")',
        'ConditionOption' => 'Enum("IsBlank,IsNotBlank,HasValue,ValueNot,ValueLessThan,ValueLessThanEqual,ValueGreaterThan,ValueGreaterThanEqual")',
        'FieldValue' => 'Varchar(255)'
    );

    private static $has_one = array(
        'Parent' => 'EditableFormField',
        'ConditionField' => 'EditableFormField'
    );

    /**
     * Built in extensions required
     *
     * @config
     * @var array
     */
    private static $extensions = array(
        "Versioned('Stage', 'Live')"
    );

    /**
     * Publish this custom rule to the live site
     *
     * Wrapper for the {@link Versioned} publish function
     */
    public function doPublish($fromStage, $toStage, $createNewVersion = false)
    {
        $this->publish($fromStage, $toStage, $createNewVersion);
    }

    /**
     * Delete this custom rule from a given stage
     *
     * Wrapper for the {@link Versioned} deleteFromStage function
     */
    public function doDeleteFromStage($stage)
    {
        $this->deleteFromStage($stage);
    }


    /**
     * @param Member $member
     * @return bool
     */
    public function canDelete($member = null)
    {
        return $this->canEdit($member);
    }

    /**
     * @param Member $member
     * @return bool
     */
    public function canEdit($member = null)
    {
        return $this->Parent()->canEdit($member);
    }

    /**
     * @param Member $member
     * @return bool
     */
    public function canView($member = null)
    {
        return $this->Parent()->canView($member);
    }

    /**
     * Return whether a user can create an object of this type
     *
     * @param Member $member
     * @param array $context Virtual parameter to allow context to be passed in to check
     * @return bool
     */
    public function canCreate($member = null)
    {
        // Check parent page
        $parent = $this->getCanCreateContext(func_get_args());
        if ($parent) {
            return $parent->canEdit($member);
        }

        // Fall back to secure admin permissions
        return parent::canCreate($member);
    }

    /**
     * Helper method to check the parent for this object
     *
     * @param array $args List of arguments passed to canCreate
     * @return DataObject Some parent dataobject to inherit permissions from
     */
    protected function getCanCreateContext($args)
    {
        // Inspect second parameter to canCreate for a 'Parent' context
        if (isset($args[1]['Parent'])) {
            return $args[1]['Parent'];
        }
        // Hack in currently edited page if context is missing
        if (Controller::has_curr() && Controller::curr() instanceof CMSMain) {
            return Controller::curr()->currentPage();
        }

        // No page being edited
        return null;
    }

    /**
     * @param Member $member
     * @return bool
     */
    public function canPublish($member = null)
    {
        return $this->canEdit($member);
    }

    /**
     * @param Member $member
     * @return bool
     */
    public function canUnpublish($member = null)
    {
        return $this->canDelete($member);
    }
}
