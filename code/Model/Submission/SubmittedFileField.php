<?php

namespace SilverStripe\UserForms\Model\Submission;

use SilverStripe\Assets\File;
use SilverStripe\ORM\FieldType\DBField;

/**
 * A file uploaded on a {@link UserDefinedForm} and attached to a single
 * {@link SubmittedForm}.
 *
 * @package userforms
 */

class SubmittedFileField extends SubmittedFormField
{
    private static $has_one = [
        'UploadedFile' => File::class
    ];

    private static $table_name = 'SubmittedFileField';

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
        $title = _t(__CLASS__.'.DOWNLOADFILE', 'Download File');

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
        if ($file = $this->UploadedFile()) {
            if (trim($file->getFilename(), '/') != trim(ASSETS_DIR, '/')) {
                return $this->UploadedFile()->AbsoluteLink();
            }
        }
    }

    /**
     * Return the name of the file, if present
     *
     * @return string
     */
    public function getFileName()
    {
        if ($this->UploadedFile()) {
            return $this->UploadedFile()->Name;
        }
    }
}
