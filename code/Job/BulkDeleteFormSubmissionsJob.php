<?php

namespace SilverStripe\UserForms\Job;

use Exception;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataList;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBDatetime;
use SilverStripe\UserForms\Model\Submission\SubmittedFileField;
use SilverStripe\UserForms\Model\Submission\SubmittedForm;
use SilverStripe\UserForms\Model\Submission\SubmittedFormField;
use Symbiote\QueuedJobs\Services\AbstractQueuedJob;
use Symbiote\QueuedJobs\Services\QueuedJobService;

/**
 * Note: running this job will PERMANENTLY remove data from {@link SubmittedForm}, {@link SubmittedFormField} and
 * {@link SubmittedFileField}. PLEASE make sure that you have exported the data before running this.
 */
class BulkDeleteFormSubmissionsJob extends AbstractQueuedJob
{
    Use Configurable;

    /**
     * Specify a default cutoff date for this job. Files/forms created/submitted older than the cutoff date will be
     * deleted.
     *
     * @var string
     */
    private static $default_cutoff_date = '30 days';

    /**
     * Specify when to schedule the new job once the current one is finished. Defaults to the next day.
     *
     * @var string
     */
    private static $default_new_schedule = '1 day';

    /**
     * Specify batch size to process
     *
     * @var int
     */
    private static $batch_size = 50;

    /**
     * @param string $cutOffDate e.g. 1day or 5 days. Modify `$default_cutoff_date` to modify default cutoff date
     * @param bool $isRescheduled job is rescheduled by default
     * @param int $parentID Parent page of the form
     */
    public function __construct($cutOffDate = '', $isRescheduled = true, $parentID = 0)
    {
        parent::__construct();

        if (!$cutOffDate) {
            // use the default_cutoff_date if nothing is specified
            $cutOffDate = self::config()->uninherited('default_cutoff_date');
        }

        $this->cutOffDate = $cutOffDate;

        if (!$this->validateDateParam($this->cutOffDate)) {
            $this->addMessage(
                sprintf('%s is not a valid cutoff date to process',$this->cutOffDate));
            $this->isComplete = true;
            return;
        }

        $this->isRescheduled = filter_var($isRescheduled, FILTER_VALIDATE_BOOLEAN);
        $this->parentID = filter_var($parentID, FILTER_VALIDATE_INT);
    }

    /**
     * @inheritdoc
     */
    public function setup()
    {
        $this->batchsize = self::config()->uninherited('batch_size');
        $this->offset = 0;
        $this->currentStep = 0;

        // get list of forms to delete
        $this->formList = $this->getFormListToDelete();
        $count = $this->formList->exists() ? $this->formList->count() : 0;

        // No need to process if there are no form submissions to deleted, just reschedule this job
        if (!$count) {
            $this->addMessage('No form submissions deleted.');
            $this->isComplete = true;
            return;
        }

        $this->totalSteps = ceil($count / $this->batchsize);
        $this->addMessage(sprintf('%d total form submission(s) to delete', $count));
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return 'Bulk Delete Form Submissions';
    }

    /**
     * Use random signature to ensure uniqueness so that the job can be rescheduled
     * @return string
     */
    public function getSignature()
    {
        return $this->randomSignature();
    }

    /**
     * Removes all submissions created older than the specified cutoff date
     */
    public function process()
    {
        while ($this->currentStep < $this->totalSteps) {
            // limit the form submissions list using batchsize
            /* @var $limitFormList DataList */
            $limitFormList =  $this->formList->limit($this->batchsize);
            $formListCount = $limitFormList->count();

            // Physically remove files (not just file records)
            $this->deleteSubmittedFiles($limitFormList);

            // Remove submitted forms and form fields
            $limitFormList->removeAll();

            $this->addMessage(sprintf('%d form submission(s) deleted.', $formListCount));

            $this->currentStep += 1;
        }

        $this->isComplete = true;
        $this->addMessage(
            sprintf(
                'Removed all user data on %s, %s and %s tables',
                DataObject::getSchema()->tableName(SubmittedForm::class),
                DataObject::getSchema()->tableName(SubmittedFormField::class),
                DataObject::getSchema()->tableName(SubmittedFileField::class)
            )
        );
    }

    /**
     * Filter list of submitted forms to delete based on cut-off date
     * @return DataList
     */
    protected function getFormListToDelete()
    {
        $timeToRemove = strtotime('-' . $this->cutOffDate, DBDatetime::now()->getTimestamp());
        $dateToRemove = DBDatetime::create()->setValue($timeToRemove);
        $filters['Created:LessThan'] = $dateToRemove;

        if ($this->parentID) {
            $filters['ParentID'] = $this->parentID;
        }

        return SubmittedForm::get()->filter($filters);
    }

    /**
     * Validates this datetime parameter
     * @param string $cutOffDate
     * @return bool
     */
    protected function validateDateParam($cutOffDate)
    {
        $date = date_parse($cutOffDate);
        return $date['error_count'] == 0 && $date['warning_count'] == 0;
    }

    /**
     * This is where we remove the files permanently from the Asset store and its File records
     * @param $fileList ArrayList
     * @return int
     */
    protected function deleteFiles($fileList)
    {
        $counter = 0;
        if ($fileList->exists()) {
            $fileList->each(function($file) use(&$counter) {
                /* @var $file File */
                // delete file from the asset store
                $file->deleteFile();

                // check if file has live version
                if ($file->isPublished()) {
                    $file->doUnpublish();
                }

                // remove the DB record of this file
                $file->delete();

                $counter += 1;
            });
        }

        return $counter;
    }

    /**
     * Physically remove submitted files and records from the asset store
     * @param $listFormToDelete DataList
     */
    protected function deleteSubmittedFiles($listFormToDelete)
    {
        $fileList = ArrayList::create();
        if ($listFormToDelete->exists()) {
            $submittedFormIDArray = $listFormToDelete->column('ID');
            $formFieldList = SubmittedFormField::get()->filter([
                    'ClassName' => SubmittedFileField::class,
                    'ParentID' => $submittedFormIDArray,
            ]);

            $formFieldList->each(function($submittedFileField) use($fileList) {
                $fileList->push($submittedFileField->UploadedFile());
            });

            $this->deleteFiles($fileList);
        }
    }

    /**
     * @inheritdoc
     */
    public function afterComplete()
    {
        if ($this->isRescheduled) {
            /* @var BulkDeleteFormSubmissionsJob $bulkDeleteJob */
            $bulkDeleteJob = Injector::inst()->create(self::class, $this->cutOffDate, $this->isRescheduled);

            /* @var $queuedJobService QueuedJobService */
            $queuedJobService = Injector::inst()->get(QueuedJobService::class);

            $dateconfig = self::config()->uninherited('default_new_schedule');
            $newDate = DBDatetime::now()->modify('+' . $dateconfig)->Rfc2822();

            try {
                // schedule the new job
                $queuedJobService->queueJob($bulkDeleteJob, $newDate);
            } catch (Exception $e) {
                $this->addMessage($e->getMessage());
            }
        }
    }
}
