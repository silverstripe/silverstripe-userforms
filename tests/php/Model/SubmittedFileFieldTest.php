<?php

namespace SilverStripe\UserForms\Tests\Model;

use SilverStripe\Assets\Dev\TestAssetStore;
use SilverStripe\Assets\File;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\UserForms\Model\Submission\SubmittedFileField;
use SilverStripe\UserForms\Model\Submission\SubmittedForm;
use SilverStripe\Versioned\Versioned;

class SubmittedFileFieldTest extends SapphireTest
{
    protected function setUp(): void
    {
        parent::setUp();

        TestAssetStore::activate('SubmittedFileFieldTest');
    }

    protected function tearDown(): void
    {
        TestAssetStore::reset();

        parent::tearDown();
    }

    public function testDeletingSubmissionRemovesFile()
    {
        $file = File::create();
        $file->setFromString('ABC', 'test-SubmittedFileFieldTest.txt');
        $file->write();

        $submittedForm = SubmittedForm::create();
        $submittedForm->write();

        $submittedFile = SubmittedFileField::create();
        $submittedFile->UploadedFileID = $file->ID;
        $submittedFile->Name = 'File';
        $submittedFile->ParentID = $submittedForm->ID;
        $submittedFile->write();

        $this->assertStringContainsString('test-SubmittedFileFieldTest', $submittedFile->getFileName(), 'Submitted file is linked');

        $submittedForm->delete();
        $fileId = $file->ID;

        $draftVersion = Versioned::withVersionedMode(function () use ($fileId) {
            Versioned::set_stage(Versioned::DRAFT);

            return File::get()->byID($fileId);
        });

        $this->assertNull($draftVersion, 'Draft file has been deleted');

        $liveVersion = Versioned::withVersionedMode(function () use ($fileId) {
            Versioned::set_stage(Versioned::LIVE);

            return File::get()->byID($fileId);
        });

        $this->assertNull($liveVersion, 'Live file has been deleted');
    }
}
