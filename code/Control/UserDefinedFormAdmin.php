<?php

namespace SilverStripe\UserForms\Control;

use SilverStripe\Admin\AdminRootController;
use SilverStripe\Admin\LeftAndMain;
use SilverStripe\Assets\Folder;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Control\HTTPResponse_Exception;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\HiddenField;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\OptionsetField;
use SilverStripe\Forms\RequiredFields;
use SilverStripe\Forms\Schema\FormSchema;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\TreeDropdownField;
use SilverStripe\ORM\ValidationException;
use SilverStripe\Security\Group;
use SilverStripe\Security\InheritedPermissions;
use SilverStripe\Security\Permission;
use SilverStripe\Security\PermissionFailureException;
use SilverStripe\Security\Security;
use SilverStripe\UserForms\Model\EditableFormField;
use SilverStripe\UserForms\Model\EditableFormField\EditableFileField;
use SilverStripe\UserForms\Model\UserDefinedForm;
use SilverStripe\Versioned\Versioned;

/**
 * Provides a few endpoints the user form CMS UI targets with some AJAX request.
 *
 * @note While this is a LeftAndMain controller, it doesn't actually appear in the Left side CMS navigation.
 */
class UserDefinedFormAdmin extends LeftAndMain
{
    private static $allowed_actions = [
        'confirmfolderformschema',
        'ConfirmFolderForm',
        'confirmfolder',
        'getfoldergrouppermissions',
    ];

    private static $required_permission_codes = 'CMS_ACCESS_CMSMain';

    private static $url_segment = 'user-forms';

    /**
     * @var string The name of the folder where form submissions will be placed by default
     */
    private static $form_submissions_folder = 'Form-submissions';

    /**
     * Returns a TextField for entering a folder name.
     * @param string $folder The current folder to set the field to
     * @param string $title The title of the text field
     * @return TextField
     */
    private static function getRestrictedAccessField(string $folder, string $title)
    {
        $textField = TextField::create('CreateFolder', '');

        /** @var Folder $formSubmissionsFolder */
        $formSubmissionsFolder = Folder::find($folder);
        $textField->setDescription(EditableFileField::getFolderPermissionString($formSubmissionsFolder));
        $textField->addExtraClass('pt-2 userform-confirm-folder');
        $textField->setSchemaData([
            'data' => [
                'prefix' => static::config()->get('form_submissions_folder') . '/',
            ],
            'attributes' => [
                'placeholder' => $title
            ]
        ]);

        return $textField;
    }


    public function index(HTTPRequest $request): HTTPResponse
    {
        // Don't serve anythign under the main URL.
        return $this->httpError(404);
    }

    /**
     * This returns a Confirm Folder form schema used to verify the upload folder for EditableFileFields
     * @param HTTPRequest $request
     * @return HTTPResponse
     */
    public function confirmfolderformschema(HTTPRequest $request)
    {
        // Retrieve editable form field by its ID
        $id = $request->requestVar('ID');
        if (!$id) {
            throw new HTTPResponse_Exception(_t(__CLASS__.'.INVALID_REQUEST', 'This request was invalid.'), 400);
        }
        $editableFormField = EditableFormField::get()->byID($id);
        if (!$editableFormField) {
            $editableFormField = Versioned::get_by_stage(EditableFormField::class, Versioned::DRAFT)
                ->byID($id);
        }
        if (!$editableFormField) {
            throw new HTTPResponse_Exception(_t(__CLASS__.'.INVALID_REQUEST', 'This request was invalid.'), 400);
        }

        // Retrieve the editable form fields Parent
        $userForm = $editableFormField->Parent();
        if (!$userForm) {
            throw new HTTPResponse_Exception(_t(__CLASS__.'.INVALID_REQUEST', 'This request was invalid.'), 400);
        }
        if (!$userForm->canEdit()) {
            throw new PermissionFailureException();
        }

        // Get the folder we want to associate to this EditableFileField
        $folderId = 0;
        if ($editableFormField instanceof EditableFileField) {
            $folderId = $editableFormField->FolderID;
        }
        $folder = Folder::get()->byID($folderId);
        if (!$folder) {
            $folder = $this->getFormSubmissionFolder();
            $folderId = $folder->ID;
        }

        $form = $this->buildConfirmFolderForm(
            $userForm->Title ?: '',
            EditableFileField::getFolderPermissionString($folder)
        );
        $form->loadDataFrom(['FolderID' => $folderId, 'ID' => $id]);

        // Convert the EditableFormField to an EditableFileField if it's not already one.
        if (!$editableFormField instanceof EditableFileField) {
            $editableFormField = $editableFormField->newClassInstance(EditableFileField::class);
            $editableFormField->write();
        }

        // create the schema response
        $parts = $this->getRequest()->getHeader(static::SCHEMA_HEADER);
        $schemaID = $this->getRequest()->getURL();
        $data = FormSchema::singleton()->getMultipartSchema($parts, $schemaID, $form);

        // return the schema response
        $response = HTTPResponse::create(json_encode($data));
        $response->addHeader('Content-Type', 'application/json');
        return $response;
    }

    /**
     * Return the ConfirmFolderForm. This is only exposed so the treeview has somewhere to direct it's AJAX calss.
     * @return Form
     */
    public function ConfirmFolderForm(): Form
    {
        return $this->buildConfirmFolderForm();
    }

    /**
     * Build the ConfirmFolderForm
     * @param string $suggestedFolderName Suggested name for the folder name field
     * @param string $permissionFolderString Description to append to the treeview field
     * @return Form
     */
    private function buildConfirmFolderForm(string $suggestedFolderName = '', string $permissionFolderString = ''): Form
    {
        // Build our Field list for the Form we will return to the front end.
        $fields = FieldList::create(
            LiteralField::create(
                'LabelA',
                _t(__CLASS__.'.CONFIRM_FOLDER_LABEL_A', 'Files that your users upload should be stored carefully to reduce the risk of exposing sensitive data. Ensure the folder you select can only be viewed by appropriate parties. Folder permissions can be managed within the Files area.')
            )->addExtraClass(' mb-2'),
            LiteralField::create(
                'LabelB',
                _t(__CLASS__.'.CONFIRM_FOLDER_LABEL_B', 'The folder selected will become the default for this form. This can be changed on an individual basis in the <i>File upload field.</i>')
            )->addExtraClass(' mb-3'),
            static::getRestrictedAccessField($this->config()->get('form_submissions_folder'), $suggestedFolderName),
            OptionsetField::create('FolderOptions', _t(__CLASS__.'.FOLDER_OPTIONS_TITLE', 'Form folder options'), [
                "new" => _t(__CLASS__.'.FOLDER_OPTIONS_NEW', 'Create a new folder (recommended)'),
                "existing" => _t(__CLASS__.'.FOLDER_OPTIONS_EXISTING', 'Use an existing folder')
            ], "new"),
            TreeDropdownField::create('FolderID', '', Folder::class)
                ->addExtraClass('pt-1')
                ->setDescription($permissionFolderString),
            HiddenField::create('ID')
        );

        $actions = FieldList::create(
            FormAction::create('confirmfolder', _t(__CLASS__.'.FORM_ACTION_CONFIRM', 'Save and continue'))
                ->setUseButtonTag(false)
                ->addExtraClass('btn btn-primary'),
            FormAction::create("cancel", _t(__CLASS__ . '.CANCEL', "Cancel"))
                ->addExtraClass('btn btn-secondary')
                ->setUseButtonTag(true)
        );

        return Form::create($this, 'ConfirmFolderForm', $fields, $actions, RequiredFields::create('ID'))
            ->setFormAction($this->Link('ConfirmFolderForm'))
            ->addExtraClass('form--no-dividers');
    }

    /**
     * Sets the selected folder as the upload folder for an EditableFileField
     * @param array $data
     * @param Form $form
     * @param HTTPRequest $request
     * @return HTTPResponse
     * @throws ValidationException
     */
    public function confirmfolder(array $data, Form $form, HTTPRequest $request)
    {
        if (!Permission::checkMember(null, "CMS_ACCESS_AssetAdmin")) {
            throw new PermissionFailureException();
        }

        // retrieve the EditableFileField
        $id = $data['ID'];
        if (!$id) {
            throw new HTTPResponse_Exception(_t(__CLASS__.'.INVALID_REQUEST', 'This request was invalid.'), 400);
        }
        $editableFormField = EditableFormField::get()->byID($id);
        if (!$editableFormField) {
            $editableFormField = Versioned::get_by_stage(EditableFormField::class, Versioned::DRAFT)->byID($id);
        }
        if (!$editableFormField) {
            throw new HTTPResponse_Exception(_t(__CLASS__.'.INVALID_REQUEST', 'This request was invalid.'), 400);
        }
        // change the class if it is incorrect
        if (!$editableFormField instanceof EditableFileField) {
            $editableFormField = $editableFormField->newClassInstance(EditableFileField::class);
        }
        if (!$editableFormField) {
            throw new HTTPResponse_Exception(_t(__CLASS__.'.INVALID_REQUEST', 'This request was invalid.'), 400);
        }
        $editableFileField = $editableFormField;

        if (!$editableFileField->canEdit()) {
            throw new PermissionFailureException();
        }

        // check if we're creating a new folder or using an existing folder
        $option = isset($data['FolderOptions']) ? $data['FolderOptions'] : '';
        if ($option === 'existing') {
            // set existing folder
            $folderID = $data['FolderID'];
            if ($folderID != 0) {
                $folder = Folder::get()->byID($folderID);
                if (!$folder) {
                    throw new HTTPResponse_Exception(_t(__CLASS__.'.INVALID_REQUEST', 'This request was invalid.'), 400);
                }
            }
        } else {
            // create the folder
            $createFolder = isset($data['CreateFolder']) ? $data['CreateFolder'] : $editableFormField->Parent()->Title;
            $folder = $this->getFormSubmissionFolder($createFolder);
        }

        // assign the folder
        $editableFileField->FolderID = isset($folder) ? $folder->ID : 0;
        $editableFileField->write();

        // respond
        return HTTPResponse::create(json_encode([]))->addHeader('Content-Type', 'application/json');
    }

    /**
     * Get the permission for a specific folder
     * @return HTTPResponse
     */
    public function getfoldergrouppermissions()
    {
        $folderID = $this->getRequest()->requestVar('FolderID');
        if ($folderID) {
            $folder = Folder::get()->byID($folderID);
            if (!$folder) {
                throw new HTTPResponse_Exception(_t(__CLASS__.'.INVALID_REQUEST', 'This request was invalid.'), 400);
            }
            if (!$folder->canView()) {
                throw new PermissionFailureException();
            }
        } else {
            $folder = null;
        }

        // respond
        $response = HTTPResponse::create(json_encode(EditableFileField::getFolderPermissionString($folder)));
        $response->addHeader('Content-Type', 'application/json');
        return $response;
    }

    /**
     * Set the permission for the default submisison folder.
     * @throws ValidationException
     */
    private static function updateFormSubmissionFolderPermissions()
    {
        // ensure the FormSubmissions folder is only accessible to Administrators
        $formSubmissionsFolder = Folder::find(static::config()->get('form_submissions_folder'));
        $formSubmissionsFolder->CanViewType = InheritedPermissions::ONLY_THESE_USERS;
        $formSubmissionsFolder->ViewerGroups()->removeAll();
        $formSubmissionsFolder->ViewerGroups()->add(Group::get_one(Group::class, ['"Code"' => 'administrators']));
        $formSubmissionsFolder->write();
    }

    /**
     * Returns the form submission folder or a sub folder if provided.
     * Creates the form submission folder if it doesn't exist.
     * Updates the form submission folder permissions if it is created.
     * @param string $subFolder Sub-folder to be created or returned.
     * @return Folder
     * @throws ValidationException
     */
    public static function getFormSubmissionFolder(string $subFolder = null): ?Folder
    {
        $folderPath = static::config()->get('form_submissions_folder');
        if ($subFolder) {
            $folderPath .= '/' . $subFolder;
        }
        $formSubmissionsFolderExists = !!Folder::find(static::config()->get('form_submissions_folder'));
        $folder = Folder::find_or_make($folderPath);

        // Set default permissions if this is the first time we create the form submission folder
        if (!$formSubmissionsFolderExists) {
            UserDefinedFormAdmin::updateFormSubmissionFolderPermissions();
            // Make sure we return the folder with the latest permission
            $folder = Folder::find($folderPath);
        }

        return $folder;
    }
}
