<?php

namespace SilverStripe\UserForms\Tests\Model;

use SilverStripe\Assets\Dev\TestAssetStore;
use SilverStripe\Assets\File;
use SilverStripe\Assets\Storage\AssetStore;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\UserForms\Model\Submission\SubmittedFileField;
use SilverStripe\UserForms\Model\Submission\SubmittedForm;
use SilverStripe\Versioned\Versioned;

class SubmittedFileFieldTest extends SapphireTest
{
    protected $file;
    protected $submittedForm;

    protected function setUp(): void
    {
        parent::setUp();

        TestAssetStore::activate('SubmittedFileFieldTest');

        $this->file = File::create();
        $this->file->setFromString('ABC', 'test-SubmittedFileFieldTest.txt');
        $this->file->write();

        $this->submittedForm = SubmittedForm::create();
        $this->submittedForm->write();

        $this->submittedFile = SubmittedFileField::create();
        $this->submittedFile->UploadedFileID = $this->file->ID;
        $this->submittedFile->Name = 'File';
        $this->submittedFile->ParentID = $this->submittedForm->ID;
        $this->submittedFile->write();
    }

    protected function tearDown(): void
    {
        TestAssetStore::reset();

        parent::tearDown();
    }

    public function testDeletingSubmissionRemovesFile()
    {
        $this->assertStringContainsString('test-SubmittedFileFieldTest', $this->submittedFile->getFileName(), 'Submitted file is linked');

        $this->submittedForm->delete();
        $fileId = $this->file->ID;

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

    public function testGetFormattedValue()
    {
        $fileName = $this->submittedFile->getFileName();
        $message = "You don&#039;t have the right permissions to download this file";

        $this->file->CanViewType = 'OnlyTheseUsers';
        $this->file->write();
        
        $this->loginWithPermission('ADMIN');
        $this->assertEquals(
            sprintf(
                '%s - <a href="/assets/3c01bdbb26/test-SubmittedFileFieldTest.txt" target="_blank">Download File</a>',
                $fileName
            ),
            $this->submittedFile->getFormattedValue()->value
        );

        $this->logOut();
        $this->loginWithPermission('CMS_ACCESS_CMSMain');
        $this->assertEquals(
            sprintf(
                '<i class="icon font-icon-lock"></i> %s - <em>%s</em>',
                $fileName,
                $message
            ),
            $this->submittedFile->getFormattedValue()->value
        );

        $store = Injector::inst()->get(AssetStore::class);
        $this->assertFalse(
            $store->canView($fileName, $this->file->getHash()),
            'Users without canView rights on the file should not have been session granted access to it'
        );
    }
}
