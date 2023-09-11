<?php

namespace SilverStripe\UserForms\Model\Submission;

use SilverStripe\Assets\File;
use SilverStripe\Control\Director;
use SilverStripe\ORM\FieldType\DBField;
use SilverStripe\Versioned\Versioned;
use SilverStripe\Security\Member;
use SilverStripe\Security\Security;

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
        $link = $this->getLink(false);
        if ($link) {
            $title = _t(__CLASS__ . '.DOWNLOADFILE', 'Download File');
            $file = $this->getUploadedFileFromDraft();
            if (!$file->canView()) {
                if (Security::getCurrentUser()) {
                    // Logged in CMS user without permissions to view file in the CMS
                    $default = 'You don\'t have the right permissions to download this file';
                    $message = _t(__CLASS__ . '.INSUFFICIENTRIGHTS', $default);
                    return DBField::create_field('HTMLText', sprintf(
                        '<i class="icon font-icon-lock"></i> %s - <em>%s</em>',
                        htmlspecialchars($name, ENT_QUOTES),
                        htmlspecialchars($message, ENT_QUOTES)
                    ));
                } else {
                    // Userforms submission filled in by non-logged in user being emailed to recipient
                    $message = _t(__CLASS__ . '.YOUMUSTBELOGGEDIN', 'You must be logged in to view this file');
                    return DBField::create_field('HTMLText', sprintf(
                        '%s - <a href="%s" target="_blank">%s</a> - <em>%s</em>',
                        htmlspecialchars($name, ENT_QUOTES),
                        htmlspecialchars($link, ENT_QUOTES),
                        htmlspecialchars($title, ENT_QUOTES),
                        htmlspecialchars($message, ENT_QUOTES)
                    ));
                }
            } else {
                // Logged in CMS user with permissions to view file in the CMS
                return DBField::create_field('HTMLText', sprintf(
                    '%s - <a href="%s" target="_blank">%s</a>',
                    htmlspecialchars($name, ENT_QUOTES),
                    htmlspecialchars($link, ENT_QUOTES),
                    htmlspecialchars($title, ENT_QUOTES)
                ));
            }
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
    public function getLink($grant = true)
    {
        if ($file = $this->getUploadedFileFromDraft()) {
            if ($file->exists()) {
                $url = $file->getURL($grant);
                if ($url) {
                    return Director::absoluteURL($url);
                }
                return null;
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
