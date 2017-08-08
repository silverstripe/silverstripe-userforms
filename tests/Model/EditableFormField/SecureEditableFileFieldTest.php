<?php

/**
 * Tests integration of EditableFileField with the securefiles module
 *
 * @author dmooyman
 */
class SecureEditableFileFieldTest extends SapphireTest
{

    protected $usesDatabase = true;

    public function setUp()
    {
        parent::setUp();

        if (!class_exists('SecureFileExtension')) {
            $this->skipTest = true;
            $this->markTestSkipped(get_class() . ' skipped unless running with securefiles');
        }
        Config::inst()->update('EditableFileField', 'secure_folder_name', 'SecureEditableFileFieldTest/SecureUploads');
        $this->clearPath();
    }

    public function tearDown()
    {
        $this->clearPath();
        parent::tearDown();
    }

    protected function clearPath()
    {
        if (file_exists(ASSETS_PATH . '/SecureEditableFileFieldTest')) {
            Filesystem::removeFolder(ASSETS_PATH . '/SecureEditableFileFieldTest');
        }
    }

    /**
     * Test that newly created folders are secure
     */
    public function testCreateFolder()
    {
        $field = new EditableFileField();
        $field->write();
        $this->assertTrue($field->getIsSecure());
        $this->assertTrue($field->Folder()->exists());
        $this->assertEquals('assets/SecureEditableFileFieldTest/SecureUploads/', $field->Folder()->Filename);
        $this->assertEquals('OnlyTheseUsers', $field->Folder()->CanViewType);
        $this->assertEquals(1, $field->Folder()->ViewerGroups()->first()->Permissions()->filter('code', 'ADMIN')->count());
    }

    /**
     * Test new folders that are created without security enabled
     */
    public function testCreateInsecure()
    {
        Config::inst()->update('EditableFileField', 'disable_security', true);

        // Esure folder is created without a folder
        $field = new EditableFileField();
        $field->write();
        $this->assertFalse($field->getIsSecure());
        $this->assertFalse($field->Folder()->exists());

        // Assigning a non-secure folder doesn't secure this
        $folder = Folder::find_or_make('SecureEditableFileFieldTest/PublicFolder');
        $field->FolderID = $folder->ID;
        $field->write();

        $this->assertFalse($field->getIsSecure());
        $this->assertTrue($field->Folder()->exists());
        $this->assertEquals('assets/SecureEditableFileFieldTest/PublicFolder/', $field->Folder()->Filename);
        $this->assertEquals('Inherit', $field->Folder()->CanViewType);

        // Enabling security and re-saving will force this field to be made secure (but not changed)
        Config::inst()->update('EditableFileField', 'disable_security', false);
        singleton('EditableFileField')->requireDefaultRecords();

        // Reload record from DB
        $field = EditableFileField::get()->byID($field->ID);

        // Existing folder is now secured (retro-actively secures any old uploads)
        $this->assertTrue($field->getIsSecure());
        $this->assertTrue($field->Folder()->exists());
        $this->assertEquals('assets/SecureEditableFileFieldTest/PublicFolder/', $field->Folder()->Filename);
        $this->assertEquals('OnlyTheseUsers', $field->Folder()->CanViewType);
        $this->assertEquals(1, $field->Folder()->ViewerGroups()->first()->Permissions()->filter('code', 'ADMIN')->count());
    }
}
