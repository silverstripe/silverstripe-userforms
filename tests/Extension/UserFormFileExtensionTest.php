<?php

namespace SilverStripe\UserForms\Tests\Extension;

use SilverStripe\Assets\File;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\UserForms\Model\Submission\SubmittedFileField;
use SilverStripe\UserForms\Extension\UserFormFileExtension;

class UserFormFileExtensionTest extends SapphireTest
{
    protected $usesDatabase = true;

    public function testUpdateIsUserFormUploadFalse()
    {
        $file = File::create();
        $file->write();
        $this->assertNull($file->UserFormUpload);

        $value = true;
        $file->invokeWithExtensions('updateTrackedFormUpload', $value);
        $this->assertFalse($value);

        // refresh DataObject to get latest DB changes
        $file = File::get()->byID($file->ID);

        $this->assertEquals(UserFormFileExtension::USER_FORM_UPLOAD_FALSE, $file->UserFormUpload);
    }

    public function testUpdateIsUserFormUploadTrue()
    {
        $file = File::create();
        $file->write();
        $this->assertNull($file->UserFormUpload);

        $submittedFileField = SubmittedFileField::create();
        $submittedFileField->UploadedFileID = $file->ID;
        $submittedFileField->write();

        $value = false;
        $file->invokeWithExtensions('updateTrackedFormUpload', $value);
        $this->assertTrue($value);

        // refresh DataObject to get latest DB changes
        $file = File::get()->byID($file->ID);

        $this->assertEquals(UserFormFileExtension::USER_FORM_UPLOAD_TRUE, $file->UserFormUpload);
    }
}
