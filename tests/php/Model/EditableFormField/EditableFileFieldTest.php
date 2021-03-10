<?php

namespace SilverStripe\UserForms\Tests\Model\EditableFormField;

use SilverStripe\Assets\Folder;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\ORM\ValidationException;
use SilverStripe\UserForms\Model\EditableFormField\EditableFileField;

/**
 * @package userforms
 */
class EditableFileFieldTest extends SapphireTest
{
    protected static $fixture_file = '../EditableFormFieldTest.yml';

    /**
     * @var
     */
    private $php_max_file_size;

    /**
     * Hold the server default max file size upload limit for later
     */
    protected function setUp()
    {
        parent::setUp();

        $editableFileField = singleton(EditableFileField::class);
        $this->php_max_file_size = $editableFileField::get_php_max_file_size();
    }

    /**
     * Test that the field validator has the server default as the max file size upload
     */
    public function testDefaultMaxFileSize()
    {
        $fileField = $this->objFromFixture(EditableFileField::class, 'file-field');
        $formField = $fileField->getFormField();

        $this->assertEquals($this->php_max_file_size, $formField->getValidator()->getAllowedMaxFileSize());
    }

    /**
     * Test that validation prevents the provided upload size limit to be less than or equal to the max php size
     */
    public function testValidateFileSizeFieldValue()
    {

        $fileField = $this->objFromFixture(EditableFileField::class, 'file-field');
        $this->setExpectedException(ValidationException::class);
        $fileField->MaxFileSizeMB = $this->php_max_file_size * 2;
        $fileField->write();
    }

    /**
     * Test the field validator has the updated allowed max file size
     */
    public function testUpdatedMaxFileSize()
    {
        $fileField = $this->objFromFixture(EditableFileField::class, 'file-field');
        $fileField->MaxFileSizeMB = .25;
        $fileField->write();

        $formField = $fileField->getFormField();
        $this->assertEquals($formField->getValidator()->getAllowedMaxFileSize(), 262144);
    }

    public function testAllowEmptyTitle()
    {
        /** @var EditableFileField $field */
        $field = EditableFileField::create();
        $field->Name = 'EditableFormField_123456';
        $this->assertEmpty($field->getFormField()->Title());
    }

    public function testOnBeforeWrite()
    {
        $this->logOut();

        /** @var EditableFileField $fileField */
        $fileField = $this->objFromFixture(EditableFileField::class, 'file-field');

        $defaultFolder = Folder::find('Form-submissions');
        $this->assertNotEmpty($defaultFolder, 'Default Folder was created along with the EditableFileField');
        $this->assertFalse($defaultFolder->canView(), 'Default Folder default to being restricted');
        $this->assertFalse((boolean)$fileField->FolderConfirmed, 'EditableFileField are not Folder Confirmed initially');

        $this->assertEquals(
            $defaultFolder->ID,
            $fileField->FolderID,
            'EditableFileField default to default form submission folder'
        );

        $fileField->FolderID = Folder::find_or_make('boom')->ID;
        $fileField->write();
        $this->assertTrue(
            (boolean)$fileField->FolderConfirmed,
            'EditableFileField are Folder Confirmed once you assigned them a folder'
        );

        $secondField = EditableFileField::create();
        $secondField->ParentID = $fileField->ParentID;
        $secondField->write();

        $this->assertEquals(
            $fileField->FolderID,
            $secondField->FolderID,
            'Second EditableFileField defaults to first field FolderID'
        );
    }
}
