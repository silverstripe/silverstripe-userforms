<?php

namespace SilverStripe\UserForms\Model\Submission;

use SilverStripe\Assets\File;
use SilverStripe\ORM\FieldType\DBField;
use SilverStripe\Versioned\Versioned;

/**
 * A file uploaded on a {@link UserDefinedForm} and attached to a single
 * {@link SubmittedForm}.
 *
 * @package userforms
 * @property int $UploadedFileID
 * @method File UploadedFile()
 */
class SubmittedFileField extends SubmittedFormField
{
    private static $has_one = [
        'UploadedFile' => File::class
    ];

    private static $table_name = 'SubmittedFileField';

    private static $owns = [
        'UploadedFile'
    ];

    private static $cascade_deletes = [
        'UploadedFile'
    ];

    /**
     * Return the value of this field for inclusion into things such as
     * reports.
     *
     * @return string
     */
    public function getFormattedValue()
    {
        $name = $this->getFileName();
        $link = $this->getLink();
        $title = _t(__CLASS__ . '.DOWNLOADFILE', 'Download File');

        if ($link) {
            return DBField::create_field('HTMLText', sprintf(
                '%s - <a href="%s" target="_blank">%s</a>',
                $name,
                $link,
                $title
            ));
        }

        return false;
    }

    /**
     * Return the value for this field in the CSV export.
     *
     * @return string
     */
    public function getExportValue()
    {
        return ($link = $this->getLink()) ? $link : '';
    }

    /**
     * Return the link for the file attached to this submitted form field.
     *
     * @return string
     */
    public function getLink()
    {
        if ($file = $this->getUploadedFileFromDraft()) {
            if ($file->exists()) {
                return $file->getAbsoluteURL();
            }
        }
    }

    /**
     * As uploaded files are stored in draft by default, this retrieves the
     * uploaded file from draft mode rather than using the current stage.
     *
     * @return File
     */
    public function getUploadedFileFromDraft()
    {
        $fileId = $this->UploadedFileID;

        return Versioned::withVersionedMode(function () use ($fileId) {
            Versioned::set_stage(Versioned::DRAFT);

            return File::get()->byID($fileId);
        });
    }

    /**
     * Return the name of the file, if present
     *
     * @return string
     */
    public function getFileName()
    {
        if ($file = $this->getUploadedFileFromDraft()) {
            return $file->Name;
        }
    }
}
