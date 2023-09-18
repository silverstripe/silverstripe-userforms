<?php

namespace SilverStripe\UserForms\Tests\Model;

use SilverStripe\Assets\Dev\TestAssetStore;
use SilverStripe\Assets\File;
use SilverStripe\Assets\Storage\AssetStore;
use SilverStripe\Control\Director;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\UserForms\Model\Submission\SubmittedFileField;
use SilverStripe\UserForms\Model\Submission\SubmittedForm;
use SilverStripe\Versioned\Versioned;

class SubmittedFileFieldTest extends SapphireTest
{
    protected $file;
    protected $submittedForm;
    protected $submittedFile;

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
        // Set an explicit base URL so we get a reliable value for the test
        Director::config()->set('alternate_base_url', 'http://mysite.com');
        $fileName = $this->submittedFile->getFileName();
        $link = 'http://mysite.com/assets/3c01bdbb26/test-SubmittedFileFieldTest.txt';

        $this->file->CanViewType = 'OnlyTheseUsers';
        $this->file->write();

        // Userforms submission filled in by non-logged in user being emailed to recipient
        $this->logOut();
        $this->assertEquals(
            sprintf(
                '%s - <a href="%s" target="_blank">%s</a> - <em>%s</em>',
                $fileName,
                $link,
                'Download File',
                'You must be logged in to view this file'
            ),
            $this->submittedFile->getFormattedValue()->value
        );
        $this->logOut();

        // Logged in CMS user without permissions to view file in the CMS
        $this->logInWithPermission('CMS_ACCESS_CMSMain');
        $this->assertEquals(
            sprintf(
                '<i class="icon font-icon-lock"></i> %s - <em>%s</em>',
                $fileName,
                'You don&#039;t have the right permissions to download this file'
            ),
            $this->submittedFile->getFormattedValue()->value
        );
        $this->logOut();

        // Logged in CMS user with permissions to view file in the CMS
        $this->loginWithPermission('ADMIN');
        $this->assertEquals(
            sprintf(
                '%s - <a href="%s" target="_blank">%s</a>',
                $fileName,
                $link,
                'Download File'
            ),
            $this->submittedFile->getFormattedValue()->value
        );
    }
}
