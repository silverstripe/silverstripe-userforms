<?php

namespace SilverStripe\UserForms\Model\EditableFormField;

use SilverStripe\Assets\File;
use SilverStripe\Assets\Folder;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Convert;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\FileField;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\NumericField;
use SilverStripe\Forms\TreeDropdownField;
use SilverStripe\ORM\ValidationResult;
use SilverStripe\Security\Member;
use SilverStripe\UserForms\Model\EditableFormField;
use SilverStripe\UserForms\Model\Submission\SubmittedFileField;

/**
 * Allows a user to add a field that can be used to upload a file.
 *
 * @method Folder Folder
 * @property int FolderID
 * @property float MaxFileSizeMB
 * @package userforms
 */
class EditableFileField extends EditableFormField
{

    private static $singular_name = 'File Upload Field';

    private static $plural_names = 'File Fields';

    private static $db = [
        'MaxFileSizeMB' => 'Float',
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
     * @return FieldList
     */
    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->addFieldToTab(
            'Root.Main',
            TreeDropdownField::create(
                'FolderID',
                _t('EditableUploadField.SELECTUPLOADFOLDER', 'Select upload folder'),
                Folder::class
            )
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

        return $fields;
    }

    /**
     * @return ValidationResult
     */
    public function validate()
    {
        $result = parent::validate();

        $max = static::get_php_max_file_size();
        if ($this->MaxFileSizeMB * 1024 > $max) {
            $result->addError("Your max file size limit can't be larger than the server's limit of {$this->getPHPMaxFileSizeMB()}.");
        }

        return $result;
    }

    public function getFormField()
    {
        $field = FileField::create($this->Name, $this->Title ?: false)
            ->setFieldHolderTemplate(EditableFormField::class . '_holder')
            ->setTemplate(__CLASS__);

        $field->setFieldHolderTemplate(EditableFormField::class . '_holder')
            ->setTemplate(__CLASS__);

        $field->getValidator()->setAllowedExtensions(
            array_diff(
                // filter out '' since this would be a regex problem on JS end
                array_filter(Config::inst()->get(File::class, 'allowed_extensions')),
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
                preg_replace("/^assets\//", "", $folder->Filename)
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
        $maxUpload = File::ini2bytes(ini_get('upload_max_filesize'));
        $maxPost = File::ini2bytes(ini_get('post_max_size'));
        return min($maxUpload, $maxPost);
    }

    public function getPHPMaxFileSizeMB()
    {
        return round(static::get_php_max_file_size() / 1024 / 1024, 1);
    }
}
