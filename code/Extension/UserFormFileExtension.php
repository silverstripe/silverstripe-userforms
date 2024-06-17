<?php

namespace SilverStripe\UserForms\Extension;

use SilverStripe\Assets\File;
use SilverStripe\Assets\Folder;
use SilverStripe\Core\Convert;
use SilverStripe\ORM\DataExtension;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\Queries\SQLUpdate;
use SilverStripe\UserForms\Control\UserDefinedFormController;
use SilverStripe\UserForms\Model\Submission\SubmittedFileField;

/**
 * @property string $UserFormUpload
 * @method SubmittedFileField SubmittedFileField()
 *
 * @extends DataExtension<File&static>
 */
class UserFormFileExtension extends DataExtension
{

    public const USER_FORM_UPLOAD_UNKNOWN = null;

    public const USER_FORM_UPLOAD_FALSE = 'f';

    public const USER_FORM_UPLOAD_TRUE = 't';

    private static $db = [
        'UserFormUpload' => "Enum('f, t', null)",
    ];

    private static $belongs_to = [
        'SubmittedFileField' => SubmittedFileField::class
    ];

    /**
     * Check if the file is associated with a userform submission
     * Save the result in the database as a tri-state for two reasons:
     * a) performance - prevent the need for an extra DB query
     * b) if in the future the UserForm submission is deleted and the uploaded file is not (file is orphaned),
     *    then it is still recorded that the file was originally uploaded from a userform submission
     *
     * @param bool $value
     * @see File::isTrackedFormUpload(), UserDefinedFormController::process()
     */
    public function updateTrackedFormUpload(&$value): void
    {
        $file = $this->owner;
        if ($file->UserFormUpload != UserFormFileExtension::USER_FORM_UPLOAD_UNKNOWN) {
            $value = $file->UserFormUpload == UserFormFileExtension::USER_FORM_UPLOAD_TRUE;
            return;
        }
        if ($file->ClassName == Folder::class) {
            $value = false;
        } else {
            $value = $file->SubmittedFileField()->exists();
        }
        $this->updateDB($value);
    }

    /**
     * Update File.UserFormUpload draft table without altering File.LastEdited
     *
     * @param bool $value
     */
    private function updateDB(bool $value): void
    {
        if (!$this->owner->isInDB()) {
            return;
        }
        $tableName = Convert::raw2sql(DataObject::getSchema()->tableName(File::class));
        $column = 'UserFormUpload';
        $enumVal = $value ? UserFormFileExtension::USER_FORM_UPLOAD_TRUE : UserFormFileExtension::USER_FORM_UPLOAD_FALSE;
        SQLUpdate::create()
            ->setTable(sprintf('"%s"', $tableName))
            ->addWhere(['"ID" = ?' => [$this->owner->ID]])
            ->addAssignments([sprintf('"%s"', $column) => $enumVal])
            ->execute();
    }
}
