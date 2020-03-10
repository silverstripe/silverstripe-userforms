<?php

namespace SilverStripe\UserForms\Task;

use SilverStripe\Assets\File;
use SilverStripe\Assets\Folder;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Dev\BuildTask;
use SilverStripe\UserForms\Model\Submission\SubmittedFileField;
use SilverStripe\Versioned\Versioned;

/**
 * Class RectifyUploadLocationsTask
 * @package SilverStripe\UserForms\Task
 */
class RectifyUploadLocationsTask extends BuildTask
{
    protected $title = 'Rectify Upload Locations Task';

    protected $description = 'Moves files uploaded through user forms to their correct upload folder.';

    /**
     * Implement this method in the task subclass to
     * execute via the TaskRunner
     *
     * @param HTTPRequest $request
     * @return
     */
    public function run($request)
    {
        /** @var SubmittedFileField $submittedFileField */
        foreach (SubmittedFileField::get() as $submittedFileField) {
            # get folder the file is in
            /** @var File $file */
            $file = $submittedFileField->UploadedFile;
            /** @var Folder $folder */
            $folder = $file->getParent();

            # skip files without folders
            if (!$folder) {
                continue;
            }

            # get folder the file is supposed to be in
            $editableFormField = $submittedFileField->getEditableField();
            $expectedFolderID = $editableFormField->FolderID;
            $expectedFolderVersions = Versioned::get_all_versions(Folder::class, $expectedFolderID);
            /** @var Folder $expectedFolder */
            $expectedFolder = $expectedFolderVersions->last();

            # check that the file is in the correct folder
            if ($folder->ID != $expectedFolder->ID) {
                # create the expected folder if it does not exist
                if ($expectedFolder->isArchived()) {
                    $expectedFolder->writeToStage(Versioned::DRAFT);
                    $expectedFolder->writeToStage(Versioned::LIVE);
                }

                # move the file to the expected folder
                $file->Parent = $expectedFolder;
                $file->write();
            }
        }
    }
}
