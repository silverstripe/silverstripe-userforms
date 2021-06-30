<?php

namespace SilverStripe\UserForms\Tests\Job;

use SilverStripe\Dev\SapphireTest;
use SilverStripe\ORM\FieldType\DBDatetime;
use SilverStripe\UserForms\Job\BulkDeleteFormSubmissionsJob;
use SilverStripe\UserForms\Model\Submission\SubmittedFormField;
use SilverStripe\UserForms\Model\UserDefinedForm;
use Symbiote\QueuedJobs\Services\QueuedJobService;

class BulkDeleteFormSubmissionsTest extends SapphireTest
{
    /**
     * @var string
     */
    protected static $fixture_file = 'fixtures/BulkDeleteFormFixture.yml';

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        // Mock the current date/time to work with the File records in the fixture
        DBDatetime::set_mock_now("2021-05-27 12:02:02");

        // The shutdown handler doesn't play nicely with SapphireTest's database handling
        QueuedJobService::config()->set('use_shutdown_function', false);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown()
    {
        parent::tearDown();

        // Remove mocked date/time
        DBDatetime::clear_mock_now();
    }

    /**
     * Test if an invalid cutoff date value is supplied on the job
     */
    public function testInvalidCutoffDateValue()
    {
        $job = new BulkDeleteFormSubmissionsJob('123NF');
        $job->setup();

        // Job won't be processed further with an invalid date
        $this->assertContains(
            '123NF is not a valid cutoff date to process',
            $job->getJobData()->messages[0]
        );

        $this->assertTrue($job->getJobData()->isComplete);
    }

    /**
     * Test if submitted forms and children will be deleted with a specified cutoff date
     */
    public function testDeleteFormSubmitted()
    {
        $userForm = UserDefinedForm::get()->first();
        $submittedFormFieldsCount = SubmittedFormField::get()->count();
        // initial number of submissions
        $this->assertEquals(2, $userForm->Submissions()->count());

        // initial number of submitted form fields
        $this->assertEquals(4, $submittedFormFieldsCount);

        // run the job
        $job = new BulkDeleteFormSubmissionsJob('5 days');
        $job->setup();
        $job->process();

        $userForm2 = UserDefinedForm::get()->first();
        $submittedFormFieldsCount2 = SubmittedFormField::get()->count();

        // final number of submissions after running the BulkDeleteFormSubmissions job
        $this->assertEquals(
            1,
            $userForm2->Submissions()->count(),
            'Old submissions should be removed'
        );

        // final number of submitted form fields
        $this->assertEquals(2, $submittedFormFieldsCount2);
    }
}
