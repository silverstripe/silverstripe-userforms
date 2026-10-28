<?php

namespace SilverStripe\UserForms\Tests\Model;

use SilverStripe\Assets\Dev\TestAssetStore;
use SilverStripe\Assets\File;
use SilverStripe\Assets\Flysystem\FlysystemAssetStore;
use SilverStripe\Assets\Folder;
use SilverStripe\Assets\Storage\AssetStore;
use SilverStripe\Control\Controller;
use SilverStripe\Control\Director;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Security\InheritedPermissions;
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
                '<span class="icon font-icon-lock" aria-hidden="true"></span> %s - <em>%s</em>',
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

    public function testGetExportValue()
    {
        // Set an explicit base URL so we get a reliable value for the test
        Director::config()->set('alternate_base_url', 'http://mysite.com');
        $link = 'http://mysite.com/assets/3c01bdbb26/test-SubmittedFileFieldTest.txt';
        $fileID = '3c01bdbb26/test-SubmittedFileFieldTest.txt';
        $session = Controller::curr()->getRequest()->getSession();

        $this->file->CanViewType = 'OnlyTheseUsers';
        $this->file->write();

        // Logged in CMS user without permissions to view the file gets the link, but no grant to
        // go with it, so the file itself stays inaccessible
        $this->logInWithPermission('CMS_ACCESS_CMSMain');
        $session->clear(FlysystemAssetStore::GRANTS_SESSION);
        $this->assertSame($link, $this->submittedFile->getExportValue());
        $this->assertNull($session->get(FlysystemAssetStore::GRANTS_SESSION));
        $this->logOut();

        // Recipient email of a submission by a non-logged in user, likewise
        $session->clear(FlysystemAssetStore::GRANTS_SESSION);
        $this->assertSame($link, $this->submittedFile->getExportValue());
        $this->assertNull($session->get(FlysystemAssetStore::GRANTS_SESSION));

        // Logged in CMS user with permissions to view the file gets the link and the grant to use it
        $this->logInWithPermission('ADMIN');
        $session->clear(FlysystemAssetStore::GRANTS_SESSION);
        $this->assertSame($link, $this->submittedFile->getExportValue());
        $this->assertSame([$fileID => true], $session->get(FlysystemAssetStore::GRANTS_SESSION));
    }

    /**
     * The reported vulnerability restricted the upload folder rather than the file itself, leaving
     * the file on the default CanViewType of Inherit
     */
    public function testGetExportValueWithFolderRestrictedFile()
    {
        $session = Controller::curr()->getRequest()->getSession();

        // No viewer groups, so only an administrator can view anything in the folder
        $folder = Folder::find_or_make('restricted-uploads');
        $folder->CanViewType = InheritedPermissions::ONLY_THESE_USERS;
        $folder->write();

        $file = File::create();
        $file->setFromString('Please review my private application details.', 'restricted-uploads/POC.txt');
        $file->ParentID = $folder->ID;
        $file->write();

        $field = SubmittedFileField::create();
        $field->Name = 'FileUpload';
        $field->Title = 'Attach a file';
        $field->ParentID = $this->submittedForm->ID;
        $field->UploadedFileID = $file->ID;
        $field->write();

        $this->logInWithPermission('CMS_ACCESS_CMSMain');
        $session->clear(FlysystemAssetStore::GRANTS_SESSION);

        // The CMS view shows this user the padlock rather than a link, and exporting must not
        // hand their session access to the file either
        $this->assertStringContainsString('font-icon-lock', $field->getFormattedValue()->value);
        $this->assertStringContainsString('restricted-uploads/', $field->getExportValue());
        $this->assertNull($session->get(FlysystemAssetStore::GRANTS_SESSION));
    }

    public function testGetExportValueWithoutFile()
    {
        $field = SubmittedFileField::create();
        $field->Name = 'File';
        $field->ParentID = $this->submittedForm->ID;
        $field->write();

        $this->logInWithPermission('ADMIN');
        $this->assertSame('', $field->getExportValue());

        // File record with no underlying asset
        $emptyFile = File::create();
        $emptyFile->write();
        $field->UploadedFileID = $emptyFile->ID;
        $field->write();

        $this->assertSame('', $field->getExportValue());
    }
}
