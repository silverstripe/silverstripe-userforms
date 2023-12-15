<?php

namespace SilverStripe\UserForms\Model\EditableFormField;

use SilverStripe\Assets\File;
use SilverStripe\Assets\Folder;
use SilverStripe\Assets\Upload_Validator;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Convert;
use SilverStripe\Forms\FieldList;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Forms\FileField;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\NumericField;
use SilverStripe\Forms\TreeDropdownField;
use SilverStripe\ORM\ValidationResult;
use SilverStripe\Security\Member;
use SilverStripe\Security\InheritedPermissions;
use SilverStripe\UserForms\Control\UserDefinedFormAdmin;
use SilverStripe\UserForms\Control\UserDefinedFormController;
use SilverStripe\UserForms\Model\EditableFormField;
use SilverStripe\UserForms\Model\Submission\SubmittedFileField;

/**
 * Allows a user to add a field that can be used to upload a file.
 *
 * @package userforms
 * @property int $FolderConfirmed
 * @property int $FolderID
 * @property float $MaxFileSizeMB
 * @method Folder Folder()
 */
class EditableFileField extends EditableFormField
{

    private static $singular_name = 'File Upload Field';

    private static $plural_names = 'File Fields';

    private static $db = [
        'MaxFileSizeMB' => 'Float',
        'FolderConfirmed' => 'Boolean',
    ];

    private static $has_one = [
        'Folder' => Folder::class // From CustomFields
    ];

    private static $table_name = 'EditableFileField';

    /**
     * Further limit uploadable file extensions in addition to the restrictions
     * imposed by the File.allowed_extensions global configuration.
     * @config
     */
    private static $allowed_extensions_blacklist = [
        'htm', 'html', 'xhtml', 'swf', 'xml'
    ];

    /**
     * Returns a string describing the permissions of a folder
     * @param Folder|null $folder
     * @return string
     */
    public static function getFolderPermissionString(Folder $folder = null)
    {
        $folderPermissions = static::getFolderPermissionTuple($folder);

        $icon = 'font-icon-user-lock';
        if ($folderPermissions['CanViewType'] == InheritedPermissions::ANYONE) {
            $icon = 'font-icon-address-card-warning';
        }

        return sprintf(
            '<span class="icon %s form-description__icon" aria-hidden="true"></span>%s %s',
            $icon,
            $folderPermissions['CanViewTypeString'],
            htmlspecialchars(implode(', ', $folderPermissions['ViewerGroups']), ENT_QUOTES)
        );
    }

    /**
     * Returns an array with a view type string and the viewer groups
     * @param Folder|null $folder
     * @return array
     */
    private static function getFolderPermissionTuple(Folder $folder = null)
    {
        $viewersOptionsField = [
            InheritedPermissions::INHERIT => _t(__CLASS__.'.INHERIT', 'Visibility for this folder is inherited from the parent folder'),
            InheritedPermissions::ANYONE => _t(__CLASS__.'.ANYONE', 'Unrestricted access, uploads will be visible to anyone'),
            InheritedPermissions::LOGGED_IN_USERS => _t(__CLASS__.'.LOGGED_IN', 'Restricted access, uploads will be visible to logged-in users'),
            InheritedPermissions::ONLY_THESE_USERS => _t(__CLASS__.'.ONLY_GROUPS', 'Restricted access, uploads will be visible to the following groups:')
        ];

        if (!$folder) {
            return [
                'CanViewType' => InheritedPermissions::ANYONE,
                'CanViewTypeString' => $viewersOptionsField[InheritedPermissions::ANYONE],
                'ViewerGroups' => [],
            ];
        }

        $folder = static::getNonInheritedViewType($folder);

        // ViewerGroups may still exist when permissions have been loosened
        $viewerGroups = [];
        if ($folder->CanViewType === InheritedPermissions::ONLY_THESE_USERS) {
            $viewerGroups = $folder->ViewerGroups()->column('Title');
        }

        return [
            'CanViewType' => $folder->CanViewType,
            'CanViewTypeString' => $viewersOptionsField[$folder->CanViewType],
            'ViewerGroups' => $viewerGroups,
        ];
    }

    /**
     * Returns the nearest non-inherited view permission of the provided
     * @param File $file
     * @return File
     */
    private static function getNonInheritedViewType(File $file)
    {
        if ($file->CanViewType !== InheritedPermissions::INHERIT) {
            return $file;
        }
        $parent = $file->Parent();
        if ($parent->exists()) {
            return static::getNonInheritedViewType($parent);
        } else {
            // anyone can view top level files
            $file->CanViewType = InheritedPermissions::ANYONE;
        }
        return $file;
    }

    /**
     * @return FieldList
     */
    public function getCMSFields()
    {
        $this->beforeUpdateCMSFields(function (FieldList $fields) {
            $treeView = TreeDropdownField::create(
                'FolderID',
                _t(__CLASS__.'.SELECTUPLOADFOLDER', 'Select upload folder'),
                Folder::class
            );
            $treeView->setDescription(static::getFolderPermissionString($this->Folder()));
            $fields->addFieldToTab(
                'Root.Main',
                $treeView
            );

            // Warn the user if the folder targeted by this field is not restricted
            if ($this->FolderID && !$this->Folder()->hasRestrictedAccess()) {
                $fields->addFieldToTab("Root.Main", LiteralField::create(
                    'FileUploadWarning',
                    '<p class="alert alert-warning">' . _t(
                        'SilverStripe\\UserForms\\Model\\UserDefinedForm.UnrestrictedFileUploadWarning',
                        'Access to the current upload folder "{path}" is not restricted. Uploaded files will be publicly accessible if the exact URL is known.',
                        ['path' => Convert::raw2att($this->Folder()->Filename)]
                    )
                    . '</p>'
                ), 'Type');
            }

            $fields->addFieldToTab(
                'Root.Main',
                NumericField::create('MaxFileSizeMB')
                    ->setTitle('Max File Size MB')
                    ->setDescription("Note: Maximum php allowed size is {$this->getPHPMaxFileSizeMB()} MB")
            );

            $fields->removeByName('Default');
        });

        return parent::getCMSFields();
    }

    /**
     * @return ValidationResult
     */
    public function validate()
    {
        $result = parent::validate();

        $max = static::get_php_max_file_size();
        if ($this->MaxFileSizeMB * 1024 * 1024 > $max) {
            $result->addError("Your max file size limit can't be larger than the server's limit of {$this->getPHPMaxFileSizeMB()}.");
        }

        return $result;
    }

    public function getFolderExists(): bool
    {
        return $this->Folder()->ID !== 0;
    }

    public function createProtectedFolder(): void
    {
        $folderName = sprintf('page-%d/upload-field-%d', $this->ParentID, $this->ID);
        $folder = UserDefinedFormAdmin::getFormSubmissionFolder($folderName);
        $this->FolderID = $folder->ID;
        $this->write();
    }

    public function getFormField()
    {
        $field = FileField::create($this->Name, $this->Title ?: false)
            ->setFieldHolderTemplate(EditableFormField::class . '_holder')
            ->setTemplate(__CLASS__)
            ->setValidator(Injector::inst()->get(Upload_Validator::class . '.userforms', false));

        $field->setFieldHolderTemplate(EditableFormField::class . '_holder')
            ->setTemplate(__CLASS__);

        $field->getValidator()->setAllowedExtensions(
            array_diff(
                // filter out '' since this would be a regex problem on JS end
                array_filter(Config::inst()->get(File::class, 'allowed_extensions') ?? []),
                $this->config()->get('allowed_extensions_blacklist')
            )
        );

        if ($this->MaxFileSizeMB > 0) {
            $field->getValidator()->setAllowedMaxFileSize($this->MaxFileSizeMB * 1024 * 1024);
        } else {
            $field->getValidator()->setAllowedMaxFileSize(static::get_php_max_file_size());
        }

        $folder = $this->Folder();
        if ($folder && $folder->exists()) {
            $field->setFolderName(
                preg_replace("/^assets\//", "", $folder->Filename ?? '')
            );
        }

        $this->doUpdateFormField($field);

        return $field;
    }


    /**
     * Return the value for the database, link to the file is stored as a
     * relation so value for the field can be null.
     *
     * @return string
     */
    public function getValueFromData()
    {
        return null;
    }

    public function getSubmittedFormField()
    {
        return SubmittedFileField::create();
    }

    /**
     * @return float
     */
    public static function get_php_max_file_size()
    {
        $maxUpload = Convert::memstring2bytes(ini_get('upload_max_filesize'));
        $maxPost = Convert::memstring2bytes(ini_get('post_max_size'));
        return min($maxUpload, $maxPost);
    }

    public function getPHPMaxFileSizeMB()
    {
        return round(static::get_php_max_file_size() / 1024 / 1024, 1);
    }

    public function onBeforeWrite()
    {
        parent::onBeforeWrite();

        $folderChanged = $this->isChanged('FolderID');

        // Default to either an existing sibling's folder, or the default form submissions folder
        if ($this->FolderID === null) {
            $inheritableSibling = EditableFileField::get()->filter([
                'ParentID' => $this->ParentID,
                'FolderConfirmed' => true,
            ])->first();

            if ($inheritableSibling) {
                $this->FolderID = $inheritableSibling->FolderID;
            } else {
                $folder = UserDefinedFormAdmin::getFormSubmissionFolder();
                $this->FolderID = $folder->ID;
            }
        }

        if ($folderChanged) {
            $this->FolderConfirmed = true;
        }
    }
}
