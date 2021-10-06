<?php

namespace SilverStripe\UserForms\Tests\Control;

use SilverStripe\Assets\Dev\TestAssetStore;
use SilverStripe\Assets\File;
use SilverStripe\Assets\Folder;
use SilverStripe\Assets\Storage\AssetStore;
use SilverStripe\Assets\Upload_Validator;
use InvalidArgumentException;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Control\Session;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\CSSContentParser;
use SilverStripe\Dev\FunctionalTest;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\FormAction;
use SilverStripe\ORM\DataObject;
use SilverStripe\Security\InheritedPermissions;
use SilverStripe\Security\Security;
use SilverStripe\UserForms\Control\UserDefinedFormAdmin;
use SilverStripe\UserForms\Control\UserDefinedFormController;
use SilverStripe\UserForms\Model\EditableFormField;
use SilverStripe\UserForms\Model\EditableFormField\EditableFileField;
use SilverStripe\UserForms\Model\EditableFormField\EditableTextField;
use SilverStripe\UserForms\Model\Recipient\EmailRecipient;
use SilverStripe\UserForms\Model\Submission\SubmittedFormField;
use SilverStripe\UserForms\Model\UserDefinedForm;
use SilverStripe\View\ArrayData;
use SilverStripe\View\SSViewer;

/**
 * @package userforms
 */
class UserDefinedFormAdminTest extends FunctionalTest
{
    protected static $fixture_file = '../UserFormsTest.yml';

    protected function setUp()
    {
        parent::setUp();

        $submissionFolder = Folder::find('Form-submissions');
        if ($submissionFolder) {
            $submissionFolder->delete();
        }

        foreach (Folder::get() as $folder) {
            $folder->publishSingle();
        }
    }

    public function testConfirmfolderformInvalidRequest()
    {
        $this->logInWithPermission(['CMS_ACCESS_CMSMain']);

        $url = 'admin/user-forms/confirmfolderformschema?';
        $fieldID = $this->idFromFixture(EditableFileField::class, 'file-field-1');

        $response = $this->get($url);
        $this->assertEquals(400, $response->getStatusCode(), 'Request without ID parameter is invalid');

        $response = $this->get($url . http_build_query(['ID' => -1]));
        $this->assertEquals(400, $response->getStatusCode(), 'Request with unknown ID and known UserFormID is invalid');
    }

    public function testConfirmfolderformAccessControl()
    {
        $url = 'admin/user-forms/confirmfolderformschema?';
        $fieldID = $this->idFromFixture(EditableFileField::class, 'file-field-1');
        $restrictedFieldID = $this->idFromFixture(EditableFileField::class, 'file-field-2');

        $this->logInWithPermission(['CMS_ACCESS_CMSMain']);

        $response = $this->get($url . http_build_query(['ID' => $fieldID]));
        $this->assertEquals(200, $response->getStatusCode(), 'CMS editors can access confirm folder form ');

        $response = $this->get($url . http_build_query(['ID' => $restrictedFieldID]));
        $this->assertEquals(
            403,
            $response->getStatusCode(),
            'CMS editors can\'t access confirm folder form for restricted form'
        );

        $this->logInWithPermission('ADMIN');

        $response = $this->get($url . http_build_query(['ID' => $restrictedFieldID]));
        $this->assertEquals(
            200,
            $response->getStatusCode(),
            'Admins can access confirm folder form for restricted form'
        );
    }

    public function testConfirmfolderformFields()
    {
        $url = 'admin/user-forms/confirmfolderformschema?';
        $fieldID = $this->idFromFixture(EditableFileField::class, 'file-field-1');
        $folderID = $this->idFromFixture(Folder::class, 'unrestricted');
        $this->logInWithPermission('ADMIN');

        $response = $this->get(
            $url . http_build_query(['ID' => $fieldID]),
            null,
            ['X-FormSchema-Request' => 'auto,schema,state,errors']
        );
        $schemaData = json_decode($response->getBody(), true);

        $this->assertEquals('ConfirmFolderForm', $schemaData['schema']['name']);
        $this->assertField($schemaData, 'FolderOptions', ['component' => 'OptionsetField']);
        $this->assertField($schemaData, 'FolderID', ['component' => 'TreeDropdownField']);
        $this->assertField($schemaData, 'ID', ['schemaType' =>'Hidden']);

        $this->assertStateValue($schemaData, ['ID' => $fieldID, 'FolderID' => $folderID]);
    }

    public function testConfirmfolderformDefaultFolder()
    {
        $url = 'admin/user-forms/confirmfolderformschema?';
        $fieldID = $this->idFromFixture(EditableFileField::class, 'file-field-2');

        $this->logInWithPermission('ADMIN');

        $response = $this->get(
            $url . http_build_query(['ID' => $fieldID]),
            null,
            ['X-FormSchema-Request' => 'auto,schema,state,errors']
        );
        $schemaData = json_decode($response->getBody(), true);

        $this->assertEquals('ConfirmFolderForm', $schemaData['schema']['name']);
        $this->assertField($schemaData, 'FolderOptions', ['component' => 'OptionsetField']);
        $this->assertField($schemaData, 'FolderID', ['component' => 'TreeDropdownField']);
        $this->assertField($schemaData, 'ID', ['schemaType' =>'Hidden']);

        $folder = Folder::find('Form-submissions');
        $this->assertNotEmpty($folder, 'Default submission folder has been created');

        $this->assertStateValue($schemaData, ['ID' => $fieldID, 'FolderID' => $folder->ID]);

        $this->logOut();
        $this->assertFalse($folder->canView(), 'Default submission folder is protected');
    }

    public function testConfirmfolderInvalidRequest()
    {
        $this->logInWithPermission(['CMS_ACCESS_CMSMain', 'CMS_ACCESS_AssetAdmin']);

        $url = 'admin/user-forms/ConfirmFolderForm';
        $response = $this->post($url, ['ID' => -1]);
        $this->assertEquals(400, $response->getStatusCode(), 'Request without ID parameter is invalid');
    }

    public function testConfirmfolderAccessControl()
    {
        $url = 'admin/user-forms/ConfirmFolderForm';
        $fieldID = $this->idFromFixture(EditableFileField::class, 'file-field-1');
        $restrictedFieldID = $this->idFromFixture(EditableFileField::class, 'file-field-2');

        $this->logInWithPermission(['CMS_ACCESS_CMSMain']);
        $response = $this->post($url, ['ID' => $fieldID]);
        $this->assertEquals(
            403,
            $response->getStatusCode(),
            'Users without CMS_ACCESS_AssetAdmin can\'t confirm folder'
        );

        $this->logInWithPermission(['CMS_ACCESS_CMSMain', 'CMS_ACCESS_AssetAdmin']);
        $response = $this->post($url, ['ID' => $fieldID]);
        $this->assertEquals(200, $response->getStatusCode(), 'CMS editors can access confirm folder form ');

        $response = $this->post($url, ['ID' => $restrictedFieldID]);
        $this->assertEquals(
            403,
            $response->getStatusCode(),
            'CMS editors can\'t confirm folder form for restricted form'
        );

        $this->logInWithPermission('ADMIN');

        $response = $this->post($url, ['ID' => $restrictedFieldID]);
        $this->assertEquals(
            200,
            $response->getStatusCode(),
            'Admins can confirm folder form for restricted form'
        );
    }

    public function testConfirmfolderExistingFolder()
    {
        $this->logInWithPermission(['CMS_ACCESS_CMSMain', 'CMS_ACCESS_AssetAdmin']);

        $url = 'admin/user-forms/ConfirmFolderForm';
        $fieldID = $this->idFromFixture(EditableFileField::class, 'file-field-1');
        $folderID = $this->idFromFixture(Folder::class, 'restricted');

        $response = $this->post($url, ['ID' => $fieldID, 'FolderOptions' => 'existing', 'FolderID' => $folderID]);
        $this->assertEquals(200, $response->getStatusCode(), 'Valid request to confirm an existing folder is successful');
        $this->assertEquals(
            $folderID,
            EditableFileField::get()->byID($fieldID)->FolderID,
            'FileField points to restricted folder'
        );
    }

    public function testConfirmfolderInexistingFolder()
    {
        $this->logInWithPermission(['CMS_ACCESS_CMSMain', 'CMS_ACCESS_AssetAdmin']);

        $url = 'admin/user-forms/ConfirmFolderForm';
        $fieldID = $this->idFromFixture(EditableFileField::class, 'file-field-1');

        $response = $this->post($url, ['ID' => $fieldID, 'FolderOptions' => 'existing', 'FolderID' => -1]);
        $this->assertEquals(400, $response->getStatusCode(), 'Confirm a non-existant folder fails with 400');
    }

    public function testConfirmfolderRootFolder()
    {
        $this->logInWithPermission(['CMS_ACCESS_CMSMain', 'CMS_ACCESS_AssetAdmin']);

        $url = 'admin/user-forms/ConfirmFolderForm';
        $fieldID = $this->idFromFixture(EditableFileField::class, 'file-field-1');

        $response = $this->post($url, ['ID' => $fieldID, 'FolderOptions' => 'existing', 'FolderID' => 0]);
        $this->assertEquals(200, $response->getStatusCode(), 'Valid request to confirm an root folder is successful');
        $this->assertEquals(0, EditableFileField::get()->byID($fieldID)->FolderID, 'FileField points to root folder');
    }

    public function testConfirmfolderNewFolder()
    {
        $this->logInWithPermission(['CMS_ACCESS_CMSMain', 'CMS_ACCESS_AssetAdmin']);

        $url = 'admin/user-forms/ConfirmFolderForm';
        $fieldID = $this->idFromFixture(EditableFileField::class, 'file-field-1');

        $response = $this->post($url, ['ID' => $fieldID, 'FolderOptions' => 'new']);
        $this->assertEquals(200, $response->getStatusCode(), 'Valid request to confirm folder by creating a new one is valid');

        $folder = Folder::find('Form-submissions/Form-with-upload-field');
        $this->assertNotEmpty($folder, 'New folder has been created based on the UserFormPage\'s title');

        $this->logOut();
        $this->assertFalse($folder->canView(), 'New folder is restricted');
    }

    public function testConfirmfolderNewFolderWithSpecificName()
    {
        $this->logInWithPermission(['CMS_ACCESS_CMSMain', 'CMS_ACCESS_AssetAdmin']);

        $url = 'admin/user-forms/ConfirmFolderForm';
        $fieldID = $this->idFromFixture(EditableFileField::class, 'file-field-1');

        $response = $this->post(
            $url,
            ['ID' => $fieldID, 'FolderOptions' => 'new', 'CreateFolder' => 'My-Custom-Folder->\'Pow']
        );
        $this->assertEquals(200, $response->getStatusCode(), 'Valid request to confirm folder by creating a new one is valid');

        $folder = Folder::find('Form-submissions/My-Custom-Folder-Pow');
        $this->assertNotEmpty($folder, 'New folder has been created based the provided CreateFolder value');

        $this->logOut();
        $this->assertFalse($folder->canView(), 'New folder is restricted');
    }

    public function testConfirmfolderWithFieldTypeConversion()
    {
        $this->logInWithPermission('ADMIN');

        $url = 'admin/user-forms/ConfirmFolderForm?';
        $fieldID = $this->idFromFixture(EditableTextField::class, 'become-file-upload');

        $response = $this->post($url, ['ID' => $fieldID, 'FolderOptions' => 'new']);
        $this->assertEquals(200, $response->getStatusCode(), 'Valid request to confirm folder by creating a new one is valid');

        $folder = Folder::find('Form-submissions/Form-editable-only-by-admin');
        $this->assertNotEmpty($folder, 'New folder has been created based on the UserFormPage\'s title');

        $this->logOut();
        $this->assertFalse($folder->canView(), 'New folder is restricted');

        $field = EditableFormField::get()->byID($fieldID);
        $this->assertEquals(
            EditableFileField::class,
            $field->ClassName,
            'EditableTextField has been converted to EditableFileField'
        );
    }

    public function testPreserveSubmissionFolderPermission()
    {
        $folder = Folder::find_or_make('Form-submissions');
        $folder->CanViewType = InheritedPermissions::ANYONE;
        $folder->write();


        $this->logInWithPermission(['CMS_ACCESS_CMSMain', 'CMS_ACCESS_AssetAdmin']);
        $url = 'admin/user-forms/ConfirmFolderForm?';
        $fieldID = $this->idFromFixture(EditableFileField::class, 'file-field-1');

        $this->post($url, ['ID' => $fieldID, 'FolderOptions' => 'new']);

        $folder = Folder::find('Form-submissions');

        $this->assertEquals(
            InheritedPermissions::ANYONE,
            $folder->CanViewType,
            'Submission folder permissions are preserved'
        );
    }

    /**
     * Assert that a field with the provided attribute exists in $schema.
     *
     * @param array $schema
     * @param string $name
     * @param string $component
     * @param $value
     * @param string $message
     */
    private function assertField(array $schema, string $name, array $attributes, $message = '')
    {
        $message = $message ?: sprintf('A %s field exists with %s', $name, var_export($attributes, true));
        $fields = $schema['schema']['fields'];
        $state = $schema['state']['fields'];
        $this->assertNotEmpty($fields, $message);
        $foundField = false;
        foreach ($fields as $field) {
            if ($field['name'] === $name) {
                $foundField = true;
                foreach ($attributes as $attr => $expectedValue) {
                    $this->assertEquals($expectedValue, $field[$attr]);
                }
                break;
            }
        }
        $this->assertTrue($foundField, $message);
    }

    private function assertStateValue(array $schema, $values)
    {
        $fields = $schema['state']['fields'];
        $this->assertNotEmpty($fields);
        $foundField = false;
        foreach ($fields as $field) {
            $key = $field['name'];
            if (isset($values[$key])) {
                $this->assertEquals($values[$key], $field['value'], sprintf('%s is %s', $key, $values[$key]));
            }
        }
    }

    public function testGetFolderPermissionAccessControl()
    {
        $this->logOut();
        $url = 'admin/user-forms/getfoldergrouppermissions?';

        $this->logInWithPermission(['CMS_ACCESS_CMSMain', 'CMS_ACCESS_AssetAdmin']);
        $adminOnlyFolder = Folder::find('admin-only');
        $response = $this->get($url . http_build_query(['FolderID' => $adminOnlyFolder->ID]));
        $this->assertEquals(
            403,
            $response->getStatusCode(),
            'Access denied for getting permission of Folder user does not have read access on'
        );

        $this->logInWithPermission('ADMIN');
        $adminOnlyFolder = Folder::find('admin-only');
        $response = $this->get($url . http_build_query(['FolderID' => $adminOnlyFolder->ID]));
        $this->assertEquals(
            200,
            $response->getStatusCode(),
            'Access denied for getting permission of Folder user does not have read access on'
        );
    }

    public function testGetFolderPermissionNonExistentFolder()
    {
        $this->logInWithPermission(['CMS_ACCESS_CMSMain', 'CMS_ACCESS_AssetAdmin']);
        $url = 'admin/user-forms/getfoldergrouppermissions?';

        $response = $this->get($url . http_build_query(['FolderID' => -1]));
        $this->assertEquals(
            400,
            $response->getStatusCode(),
            'Non existent folder should fail'
        );
    }

    public function testGetFolderPermissionValidRequest()
    {
        $url = 'admin/user-forms/getfoldergrouppermissions?';
        $this->logInWithPermission(['CMS_ACCESS_CMSMain', 'CMS_ACCESS_AssetAdmin']);

        $folder = Folder::find('unrestricted');
        $response = $this->get($url . http_build_query(['FolderID' => $folder->ID]));
        $this->assertEquals(
            200,
            $response->getStatusCode(),
            'Valid request is successfull'
        );
        $this->assertContains('Unrestricted access, uploads will be visible to anyone', $response->getBody());

        $folder = Folder::find('restricted-folder');
        $response = $this->get($url . http_build_query(['FolderID' => 0]));
        $this->assertEquals(
            200,
            $response->getStatusCode(),
            'Valid request for root folder is successful'
        );
        $this->assertContains('Unrestricted access, uploads will be visible to anyone', $response->getBody());

        $folder = Folder::find('restricted-folder');
        $response = $this->get($url . http_build_query(['FolderID' => $folder->ID]));
        $this->assertEquals(
            200,
            $response->getStatusCode(),
            'Valid request for root folder is successful'
        );
        $this->assertContains('Restricted access, uploads will be visible to logged-in users ', $response->getBody());

        $this->logInWithPermission('ADMIN');
        $adminOnlyFolder = Folder::find('admin-only');
        $response = $this->get($url . http_build_query(['FolderID' => $adminOnlyFolder->ID]));
        $this->assertEquals(
            200,
            $response->getStatusCode(),
            'Valid request for folder restricted to group is successful'
        );
        $this->assertContains('Restricted access, uploads will be visible to the following groups: Administrators', $response->getBody());
    }

    public function testGetFormSubmissionFolder()
    {
        $submissionFolder = Folder::find('Form-submissions');
        $this->assertEmpty($submissionFolder, 'Submission folder does not exists initially.');

        // No parameters
        $submissionFolder = UserDefinedFormAdmin::getFormSubmissionFolder();
        $this->assertNotEmpty($submissionFolder, 'Submission folder exists after getFormSubmissionFolder call');
        $this->assertEquals('Form-submissions/', $submissionFolder->getFilename(), 'Submission folder got created under correct name');

        $this->assertEquals(InheritedPermissions::ONLY_THESE_USERS, $submissionFolder->CanViewType, 'Submission folder has correct permissions');
        $this->assertNotEmpty($submissionFolder->ViewerGroups()->find('Code', 'administrators'), 'Submission folder is limited to administrators');

        // subfolder name
        $submissionSubFolder = UserDefinedFormAdmin::getFormSubmissionFolder('test-form');
        $this->assertNotEmpty($submissionSubFolder, 'Submission subfolder has been created');
        $this->assertEquals('Form-submissions/test-form/', $submissionSubFolder->getFilename(), 'Submission sub folder got created under correct name');
        $this->assertEquals(InheritedPermissions::INHERIT, $submissionSubFolder->CanViewType, 'Submission sub folder inherit permission from parent');

        // make sure parent folder permission don't get overridden
        $submissionFolder = Folder::find('Form-submissions');
        $submissionFolder->CanViewType = InheritedPermissions::INHERIT;
        $submissionFolder->write();

        $submissionSubFolder = UserDefinedFormAdmin::getFormSubmissionFolder('test-form-2');
        $submissionFolder = Folder::find('Form-submissions');
        $this->assertEquals(InheritedPermissions::INHERIT, $submissionFolder->CanViewType, 'Submission sub folder inherit permission from parent');

        // Submission folder get recreated
        $submissionFolder->delete();
        $submissionFolder = Folder::find('Form-submissions');
        $this->assertEmpty($submissionFolder, 'Submission folder does has been deleted.');

        $submissionSubFolder = UserDefinedFormAdmin::getFormSubmissionFolder('test-form-3');
        $submissionFolder = Folder::find('Form-submissions');
        $this->assertNotEmpty($submissionFolder, 'Submission folder got recreated');
        $this->assertEquals('Form-submissions/', $submissionFolder->getFilename(), 'Submission folder got recreated under correct name');

        $this->assertEquals(InheritedPermissions::ONLY_THESE_USERS, $submissionFolder->CanViewType, 'Submission folder has correct permissions');
        $this->assertNotEmpty($submissionFolder->ViewerGroups()->find('Code', 'administrators'), 'Submission folder is limited to administrators');
    }
}
