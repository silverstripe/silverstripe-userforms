<?php

/**
 * Allows a user to add a field that can be used to upload a file.
 *
 * @package userforms
 */
class EditableFileField extends EditableFormField
{

    private static $singular_name = 'File Upload Field';

    private static $plural_names = 'File Fields';

    private static $db = array(
        'MaxFileSizeMB' => 'Float',
    );

    private static $has_one = array(
        'Folder' => 'Folder' // From CustomFields
    );

    /**
     * Further limit uploadable file extensions in addition to the restrictions
     * imposed by the File.allowed_extensions global configuration.
     * @config
     */
    private static $allowed_extensions_blacklist = array(
        'htm', 'html', 'xhtml', 'swf', 'xml'
    );

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
                'Folder'
            )
        );

        $fields->addFieldToTab("Root.Main", new LiteralField("FileUploadWarning",
            "<p class=\"message notice\">" . _t("UserDefinedForm.FileUploadWarning",
                "Files uploaded through this field could be publicly accessible if the exact URL is known")
            . "</p>"), "Type");

        $fields->addFieldToTab(
            'Root.Main',
            NumericField::create('MaxFileSizeMB')
                ->setTitle('Max File Size MB')
                ->setDescription("Note: Maximum php allowed size is {$this->getPHPMaxFileSizeMB()} MB")
        );

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
            $result->error("Your max file size limit can't be larger than the server's limit of {$this->getPHPMaxFileSizeMB()}.");
        }

        return $result;
    }

    public function getFormField()
    {
        $field = FileField::create($this->Name, $this->EscapedTitle)
            ->setFieldHolderTemplate('UserFormsField_holder')
            ->setTemplate('UserFormsFileField');

        $field->setFieldHolderTemplate('UserFormsField_holder')
            ->setTemplate('UserFormsFileField');

        $field->getValidator()->setAllowedExtensions(
            array_diff(
            // filter out '' since this would be a regex problem on JS end
                array_filter(Config::inst()->get('File', 'allowed_extensions')),
                $this->config()->allowed_extensions_blacklist
            )
        );

        if ($this->MaxFileSizeMB > 0) {
            $field->getValidator()->setAllowedMaxFileSize($this->MaxFileSizeMB * 1024);
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
        return new SubmittedFileField();
    }


    public function migrateSettings($data)
    {
        // Migrate 'Folder' setting to 'FolderID'
        if (isset($data['Folder'])) {
            $this->FolderID = $data['Folder'];
            unset($data['Folder']);
        }

        parent::migrateSettings($data);
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
        return round(static::get_php_max_file_size() / 1024.0, 1);
    }

}
