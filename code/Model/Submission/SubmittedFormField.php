<?php

namespace SilverStripe\UserForms\Model\Submission;

use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBField;
use SilverStripe\Security\Member;
use SilverStripe\UserForms\Model\EditableFormField;

/**
 * Data received from a UserDefinedForm submission
 *
 * @package userforms
 * @property string $Name
 * @property string $Value
 * @method SubmittedForm Parent()
 */
class SubmittedFormField extends DataObject
{
    private static $db = [
        'Name' => 'Varchar',
        'Value' => 'Text',
        'Title' => 'Varchar(255)',
        'Displayed' => 'Boolean',
    ];

    private static $has_one = [
        'Parent' => SubmittedForm::class
    ];

    private static $summary_fields = [
        'Title' => 'Title',
        'FormattedValue' => 'Value'
    ];

    private static $table_name = 'SubmittedFormField';

    private static $indexes = [
        'Name' => 'Name',
    ];

    /**
     * @param Member $member
     * @param array $context
     * @return boolean
     */
    public function canCreate($member = null, $context = [])
    {
        return $this->Parent()->canCreate();
    }

    /**
     * @param Member $member
     *
     * @return boolean
     */
    public function canView($member = null)
    {
        return $this->Parent()->canView();
    }

    /**
     * @param Member $member
     *
     * @return boolean
     */
    public function canEdit($member = null)
    {
        return $this->Parent()->canEdit();
    }

    /**
     * @param Member $member
     *
     * @return boolean
     */
    public function canDelete($member = null)
    {
        return $this->Parent()->canDelete();
    }

    /**
     * Generate a formatted value for the reports and email notifications.
     * Converts new lines (which are stored in the database text field) as
     * <brs> so they will output as newlines in the reports.
     *
     * @return DBField
     */
    public function getFormattedValue()
    {
        return $this->dbObject('Value');
    }

    /**
     * Return the value of this submitted form field suitable for inclusion
     * into the CSV
     *
     * @return DBField
     */
    public function getExportValue()
    {
        return $this->Value;
    }

    /**
     * Find equivalent editable field for this submission.
     *
     * Note the field may have been modified or deleted from the original form
     * so this may not always return the data you expect. If you need to save
     * a particular state of editable form field at time of submission, copy
     * that value to the submission.
     *
     * @return EditableFormField
     */
    public function getEditableField()
    {
        return $this->Parent()->Parent()->Fields()->filter([
            'Name' => $this->Name
        ])->First();
    }
}
