<?php

namespace SilverStripe\UserForms\Extensions;

use SilverStripe\Admin\Forms\UsedOnTable;
use SilverStripe\Core\Extension;
use SilverStripe\ORM\DataObject;
use SilverStripe\UserForms\Model\EditableFormField;
use SilverStripe\UserForms\Model\Submission\SubmittedFileField;
use SilverStripe\UserForms\Model\Submission\SubmittedForm;
use SilverStripe\UserForms\Model\UserDefinedForm;

/**
 * Update DataObjects on the file "Used On" table
 *
 * @extends Extension<UsedOnTable>
 */
class UsedOnTableExtension extends Extension
{
    /**
     * Link submitted file fields to their parent page
     *
     * @param array $ancestorDataObjects
     * @param DataObject $dataObject|null
     */
    public function updateUsageDataObject(?DataObject &$dataObject)
    {
        if (!($dataObject instanceof SubmittedFileField)) {
            return;
        }
        $submittedForm = $dataObject->Parent();
        if (!$submittedForm) {
            $dataObject = null;
            return;
        }
        // Display the submitted form instead of the submitted file field
        $dataObject = $submittedForm;
    }

    /**
     * @param array $ancestorDataObjects
     * @param DataObject $dataObject
     */
    public function updateUsageAncestorDataObjects(array &$ancestorDataObjects, DataObject $dataObject)
    {
        // SubmittedFileField was changed to a Submitted Form in updateUsageModifyOrExcludeDataObject()
        if (!($dataObject instanceof SubmittedForm)) {
            return;
        }
        /** @var UserDefinedForm $page */
        $page = $dataObject->Parent();
        if (!$page) {
            return;
        }
        // Use an un-persisted DataObject with a 'Title' field to display the word 'Submissions'
        $submissions = new EditableFormField();
        $submissions->Title = 'Submissions';
        $ancestorDataObjects[] = $submissions;
        $ancestorDataObjects[] = $page;
    }
}
