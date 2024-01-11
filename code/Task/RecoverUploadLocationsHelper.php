<?php

namespace SilverStripe\UserForms\Task;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use RuntimeException;
use SilverStripe\Assets\File;
use SilverStripe\Assets\Folder;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Convert;
use SilverStripe\Core\Environment;
use SilverStripe\Core\Injector\Injectable;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\ORM\DataList;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\Queries\SQLSelect;
use SilverStripe\Security\InheritedPermissions;
use SilverStripe\UserForms\Model\EditableFormField;
use SilverStripe\UserForms\Model\EditableFormField\EditableFileField;
use SilverStripe\UserForms\Model\Submission\SubmittedFileField;
use SilverStripe\UserForms\Model\Submission\SubmittedForm;
use SilverStripe\UserForms\Model\Submission\SubmittedFormField;
use SilverStripe\UserForms\Model\UserDefinedForm;
use SilverStripe\Versioned\Versioned;

/**
 * A helper to recover the UserForm uploads targeting folders incorrectly migrated from Silverstripe CMS 3
 *
 * In short, the migrated folders do not have Live version records in the database, as such
 * all the files uploaded through UserForms EditableFileField end up in a default fallback folder (/Uploads by default)
 *
 * If your project has not been migrated from Silverstripe CMS 3, you do not need this helper.
 * For more details see CVE-2020-9280
 *
 * @internal This class is not a part of Silverstripe CMS public API
 */
class RecoverUploadLocationsHelper
{
    use Injectable;
    use Configurable;

    private static $dependencies = [
        'logger' => '%$' . LoggerInterface::class . '.quiet',
    ];

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Versioned
     */
    private $versionedExtension;

    /**
     * Whether File class has Versioned extension installed
     *
     * @var bool
     */
    private $filesVersioned;

    /**
     * Cache of the EditableFileField versions
     *
     * @var EditableFileField
     */
    private $fieldFolderCache = array();

    public function __construct()
    {
        $this->logger = new NullLogger();

        // Set up things before going into the loop
        $this->versionedExtension = Injector::inst()->get(Versioned::class);
        $this->filesVersioned = $this->versionedExtension->canBeVersioned(File::class);
    }

    /**
     * @param LoggerInterface $logger
     * @return $this
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * Process the UserForm uplodas
     *
     * @return int Number of files processed
     */
    public function run()
    {
        // Set max time and memory limit
        Environment::increaseTimeLimitTo();
        Environment::setMemoryLimitMax(-1);
        Environment::increaseMemoryLimitTo(-1);

        $this->logger->notice('Begin UserForm uploaded files destination folders recovery');

        if (!class_exists(Versioned::class)) {
            $this->logger->warning('Versioned extension is not installed. Skipping recovery.');
            return 0;
        }

        if (!$this->versionedExtension->canBeVersioned(UserDefinedForm::class)) {
            $this->logger->warning('Versioned extension is not set up for UserForms. Skipping recovery.');
            return 0;
        }

        return $this->process();
    }

    /**
     * Process all the files and return the number
     *
     * @return int Number of files processed
     */
    protected function process()
    {
        // Check if we have folders to migrate
        $totalCount = $this->getCountQuery()->count();
        if (!$totalCount) {
            $this->logger->warning('No UserForm uploads found');
            return 0;
        }

        $this->logger->notice(sprintf('Processing %d file records', $totalCount));

        $processedCount = 0;
        $recoveryCount = 0;
        $errorsCount = 0;

        // Loop over the files to process
        foreach ($this->chunk() as $uploadRecord) {
            ++$processedCount;

            $fileId = $uploadRecord['UploadedFileID'];
            $fieldId = $uploadRecord['FieldID'];
            $fieldVersion = $uploadRecord['FieldVersion'];

            try {
                $expectedFolderId = $this->getExpectedUploadFolderId($fieldId, $fieldVersion);
                if ($expectedFolderId == 0) {
                    $this->logger->warning(sprintf(
                        'The upload folder was not set for the file %d, SKIPPING',
                        $fileId
                    ));
                    continue;
                }
                $recoveryCount += $this->recover($fileId, $expectedFolderId);
            } catch (\Exception $e) {
                $this->logger->error(sprintf('Could not process the file: %d', $fileId), ['exception' => $e]);
                ++$errorsCount;
            }
        }

        // Show summary of results
        if ($processedCount > 0) {
            $this->logger->notice(sprintf('%d file records have been processed.', $processedCount));
            $this->logger->notice(sprintf('%d files recovered', $recoveryCount));
            $this->logger->notice(sprintf('%d errors', $errorsCount));
        } else {
            $this->logger->notice('No files found');
        }

        return $processedCount;
    }

    /**
     * Fetches the EditableFileField version from cache and returns its FolderID
     *
     * @param int $fieldId EditableFileField.ID
     * @param int EditableFileField Version
     *
     * @return int
     */
    protected function getExpectedUploadFolderId($fieldId, $fieldVersion)
    {
        // return if cache is warm
        if (isset($this->fieldFolderCache[$fieldId][$fieldVersion])) {
            return $this->fieldFolderCache[$fieldId][$fieldVersion]->FolderID;
        }

        // fetch the version
        $editableFileField = Versioned::get_version(EditableFileField::class, $fieldId, $fieldVersion);

        // populate the cache
        $this->fieldFolderCache[$fieldId][$fieldVersion] = $editableFileField;

        return $editableFileField->FolderID;
    }

    /**
     * Fetches a Folder by its ID, gracefully handling
     * deleted folders
     *
     * @param int $id Folder.ID
     *
     * @return Folder
     *
     * @throws RuntimeException when folder could not be found
     */
    protected function getFolder($id)
    {
        $folder = Folder::get()->byID($id);

        if (!$folder && $this->filesVersioned) {
            // The folder might have been deleted, let's look up its latest version
            $folder = Versioned::get_latest_version(Folder::class, $id);

            if ($folder) {
                $this->logger->warning(sprintf('Restoring (as protected) a deleted folder: "%s"', $folder->Filename));
                if ($folder->CanViewType === InheritedPermissions::INHERIT) {
                    // enforce restored top level folders to be protected
                    $folder->CanViewType = InheritedPermissions::ONLY_THESE_USERS;
                }

                $folder->publishSingle();
            }
        }

        if (!$folder) {
            throw new RuntimeException(sprintf('Could not fetch the folder with id "%d"', $id));
        }

        return $folder;
    }

    /**
     * Recover an uploaded file location
     *
     * @param int $fileId File.ID
     * @param int $expectedFolderId ID of the folder where the file should have end up
     *
     * @return int Number of files recovered
     */
    protected function recover($fileId, $expectedFolderId)
    {
        /* @var File */
        $draft = null;

        /* @var File */
        $live = null;

        if ($this->filesVersioned) {
            $draftVersion = Versioned::get_versionnumber_by_stage(File::class, Versioned::DRAFT, $fileId);
            $liveVersion = Versioned::get_versionnumber_by_stage(File::class, Versioned::LIVE, $fileId);

            if ($draftVersion && $draftVersion != $liveVersion) {
                $draft = Versioned::get_version(File::class, $fileId, $draftVersion);
            } else {
                $draft = null;
            }

            if ($liveVersion) {
                $live = Versioned::get_version(File::class, $fileId, $liveVersion);
            }
        } else {
            $live = File::get()->byID($fileId);
        }

        if (!$live) {
            $this->logger->notice(sprintf('Could not find file with id %d (perhaps it has been deleted)', $fileId));
            return 0;
        }

        // Check whether the file has been modified (moved) after the upload
        if ($live->Version > 1) {
            if ($live->ParentID != $expectedFolderId) {
                // The file was updated after upload (perhaps was moved)
                // We should assume that was intentional and do not process
                // it, but rather make a warning here
                $this->logger->notice(sprintf(
                    'The file was updated after initial upload, skipping! "%s"',
                    $live->getField('FileFilename')
                ));
            }

            // check for residual files in the original folder
            return $this->checkResidual($fileId, $live, $draft);
        }

        if ($live->ParentID == $expectedFolderId) {
            $this->logger->info(sprintf('OK: "%s"', $live->getField('FileFilename')));
            return 0;
        }

        $this->logger->warning(sprintf('Found a misplaced file: "%s"', $live->getField('FileFilename')));

        $expectedFolder = $this->getFolder($expectedFolderId);

        if ($draft) {
            return $this->recoverWithDraft($live, $draft, $expectedFolder);
        } else {
            return $this->recoverLiveOnly($live, $expectedFolder);
        }
    }

    /**
     * Handles gracefully a bug in UserForms that prevents
     * some uploaded files from being removed on the filesystem level
     * when manually moving them to another folder through CMS
     *
     * @see https://github.com/silverstripe/silverstripe-userforms/issues/944
     *
     * @param int $fileId File.ID
     * @param File $file The live version of the file
     * @param File|null $draft The draft version of the file
     *
     * @return int Number of files recovered
     */
    protected function checkResidual($fileId, File $file, File $draft = null)
    {
        if (!$this->filesVersioned) {
            return 0;
        }

        $upload = Versioned::get_version(File::class, $fileId, 1);

        if ($upload->ParentID == $file->ParentID) {
            // The file is published in the original folder, so we're good
            return 0;
        }

        if ($draft && $upload->ParentID == $draft->ParentID) {
            // The file draft is residing in the same folder where it
            // has been uploaded originally. It's under the draft's control now
            return 0;
        }

        $deleted = 0;
        $dbFile = $upload->File;

        if ($dbFile->exists()) {
            // Find if another file record refer to the same physical location
            $another = Versioned::get_by_stage(File::class, Versioned::LIVE, [
                '"ID" != ?' => $fileId,
                '"FileFilename"' => $dbFile->Filename,
                '"FileHash"' => $dbFile->Hash,
                '"FileVariant"' => $dbFile->Variant
            ])->exists();

            // A lazy check for draft (no check if we already found live)
            $another = $another || Versioned::get_by_stage(File::class, Versioned::DRAFT, [
                '"ID" != ?' => $fileId,
                '"FileFilename"' => $dbFile->Filename,
                '"FileHash"' => $dbFile->Hash,
                '"FileVariant"' => $dbFile->Variant
            ])->exists();

            if (!$another) {
                $this->logger->warning(sprintf('Found a residual file on the filesystem, going to delete it: "%s"', $dbFile->Filename));
                if ($dbFile->deleteFile()) {
                    $this->logger->warning(sprintf('DELETE: "%s"', $dbFile->Filename));
                    ++$deleted;
                } else {
                    $this->logger->warning(sprintf('FAILED TO DELETE: "%s"', $dbFile->Filename));
                }
            }
        }

        return $deleted;
    }

    /**
     * Recover a file with only Live version (with no draft)
     *
     * @param File $file the file instance
     * @param int $expectedFolder The expected folder
     *
     * @return int How many files have been recovered
     */
    protected function recoverLiveOnly(File $file, Folder $expectedFolder)
    {
        $this->logger->warning(sprintf('MOVE: "%s" to %s', $file->Filename, $expectedFolder->Filename));
        return $this->moveFileToFolder($file, $expectedFolder);
    }

    /**
     * Recover a live version of the file preserving the draft
     *
     * @param File $live Live version of the file
     * @param File $draft Draft version of the file
     * @param Folder $expectedFolder The expected folder
     *
     * @return int How many files have been recovered
     */
    protected function recoverWithDraft(File $live, File $draft, Folder $expectedFolder)
    {
        $this->logger->warning(sprintf(
            'MOVE: "%s" to "%s", preserving draft "%s"',
            $live->Filename,
            $expectedFolder->Filename,
            $draft->Filename
        ));

        $result = $this->moveFileToFolder($live, $expectedFolder);

        // Restore the DB record of the draft deleted after publishing
        $draft->writeToStage(Versioned::DRAFT);

        // This hack makes it copy the file on the filesystem level.
        // The file under the Filename link of the draft has been removed
        // when we published the updated live version of the file.
        $draft->File->Filename = $live->File->Filename;

        // If the draft parent folder has been deleted (e.g. the draft file was alone there)
        // we explicitly restore it here, otherwise it
        // will be lost and saved in the root directory
        $draft->Parent = $this->getFolder($draft->ParentID);

        // Save the draft and copy over the file from the Live version
        // on the filesystem level
        $draft->write();

        return $result;
    }

    protected function moveFileToFolder(File $file, Folder $folder)
    {
        $file->Parent = $folder;
        $file->write();
        $file->publishSingle();

        return 1;
    }

    /**
     * Split queries into smaller chunks to avoid using too much memory
     * @param int $chunkSize
     * @return Generator
     */
    private function chunk($chunkSize = 100)
    {
        $greaterThanID = 0;

        do {
            $count = 0;

            $chunk = $this->getQuery()
                ->setLimit($chunkSize)
                ->addWhere([
                    '"SubmittedFileFieldTable"."UploadedFileID" > ?' => $greaterThanID
                ])->execute();

            foreach ($chunk as $item) {
                yield $item;
                $greaterThanID = $item['UploadedFileID'];
                ++$count;
            }
        } while ($count > 0);
    }

    /**
     * Returns SQLQuery instance
     *
select
    SubmittedFileField.UploadedFileID,
    EditableFileField_Versions.RecordID as FieldID,
    MAX(EditableFileField_Versions.Version) as FieldVersion
from
    SubmittedFileField
left join
    SubmittedFormField
on
    SubmittedFormField.ID = SubmittedFileField.ID
left join
    SubmittedForm
on
    SubmittedForm.ID = SubmittedFormField.ParentID
left join
    EditableFormField_Versions
on
    EditableFormField_Versions.ParentID = SubmittedForm.ParentID
and
    EditableFormField_Versions.Name = SubmittedFormField.Name
and
    EditableFormField_Versions.LastEdited < SubmittedForm.Created
inner join
    EditableFileField_Versions
on
    EditableFileField_Versions.RecordID = EditableFormField_Versions.RecordID
and
    EditableFileField_Versions.Version = EditableFormField_Versions.Version
where
    SubmittedFileField.UploadedFileID != 0
group by
    SubmittedFileField.UploadedFileID,
    EditableFileField_Versions.RecordID
order by
    SubmittedFileField.UploadedFileID
limit 100
     */
    private function getQuery()
    {
        $schema = DataObject::getSchema();
        $submittedFileFieldTable = $schema->tableName(SubmittedFileField::class);
        $submittedFormFieldTable = $schema->tableName(SubmittedFormField::class);

        $submittedFormTable = $schema->tableName(SubmittedForm::class);

        $editableFileFieldTable = $schema->tableName(EditableFileField::class);
        $editableFileFieldVersionsTable = sprintf('%s_Versions', $editableFileFieldTable);

        $editableFormFieldTable = $schema->tableName(EditableFormField::class);
        $editableFormFieldVersionsTable = sprintf('%s_Versions', $editableFormFieldTable);

        return SQLSelect::create()
            ->setSelect([
                '"SubmittedFileFieldTable"."UploadedFileID"',
                '"EditableFileFieldVersions"."RecordID" as "FieldID"',
                'MAX("EditableFileFieldVersions"."Version") as "FieldVersion"'
            ])
            ->setFrom(sprintf('%s as "SubmittedFileFieldTable"', Convert::symbol2sql($submittedFileFieldTable)))
            ->setWhere([
                '"SubmittedFileFieldTable"."UploadedFileID" != 0'
            ])
            ->setGroupBy([
                '"SubmittedFileFieldTable"."UploadedFileID"',
                '"EditableFileFieldVersions"."RecordID"'
            ])
            ->addLeftJoin(
                $submittedFormFieldTable,
                '"SubmittedFormFieldTable"."ID" = "SubmittedFileFieldTable"."ID"',
                'SubmittedFormFieldTable'
            )
            ->addLeftJoin(
                $submittedFormTable,
                '"SubmittedFormTable"."ID" = "SubmittedFormFieldTable"."ParentID"',
                'SubmittedFormTable'
            )
            ->addLeftJoin(
                $editableFormFieldVersionsTable,
                sprintf(
                    '%s AND %s AND %s',
                    '"EditableFormFieldVersions"."ParentID" = "SubmittedFormTable"."ParentID"',
                    '"EditableFormFieldVersions"."Name" = "SubmittedFormFieldTable"."Name"',
                    '"EditableFormFieldVersions"."LastEdited" < "SubmittedFormTable"."Created"'
                ),
                'EditableFormFieldVersions'
            )
            ->addInnerJoin(
                $editableFileFieldVersionsTable,
                sprintf(
                    '%s AND %s',
                    '"EditableFileFieldVersions"."RecordID" = "EditableFormFieldVersions"."RecordID"',
                    '"EditableFileFieldVersions"."Version" = "EditableFormFieldVersions"."Version"'
                ),
                'EditableFileFieldVersions'
            )
            ->addOrderBy('"SubmittedFileFieldTable"."UploadedFileID"', 'ASC')
        ;
    }

    /**
     * Returns DataList object containing every
     * uploaded file record
     *
     * @return DataList<SubmittedFileField>
     */
    private function getCountQuery()
    {
        return SubmittedFileField::get()->filter(['UploadedFileID:NOT' => 0]);
    }
}
