<?php


/**
 * Declares a condition that determines whether an email can be sent to a given recipient
 *
 * @method UserDefinedForm_EmailRecipient Parent()
 */
class UserDefinedForm_EmailRecipientCondition extends DataObject
{

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
    public function matches($data, $form)
    {
        $fieldName = $this->ConditionField()->Name;
        $fieldValue = isset($data[$fieldName]) ? $data[$fieldName] : null;
        switch ($this->ConditionOption) {
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
     * @return SiteTree Parent page instance
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
     * @param Member
     *
     * @return boolean
     */
    public function canView($member = null)
    {
        return $this->Parent()->canView($member);
    }

    /**
     * @param Member
     *
     * @return boolean
     */
    public function canEdit($member = null)
    {
        return $this->Parent()->canEdit($member);
    }

    /**
     * @param Member
     *
     * @return boolean
     */
    public function canDelete($member = null)
    {
        return $this->canEdit($member);
    }
}
