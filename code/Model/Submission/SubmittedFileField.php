<?php

namespace SilverStripe\UserForms\Model\Submission;

use SilverStripe\Assets\File;
use SilverStripe\Control\Director;
use SilverStripe\ORM\FieldType\DBField;
use SilverStripe\ORM\ManyManyList;
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
 * @method ManyManyList<File> UploadedFiles()
 */
class SubmittedFileField extends SubmittedFormField
{
    private static $has_one = [
        'UploadedFile' => File::class
    ];

    private static $many_many = [
        'UploadedFiles' => File::class
    ];

    private static $table_name = 'SubmittedFileField';

    private static $owns = [
        'UploadedFile',
        'UploadedFiles',
    ];

    private static $cascade_deletes = [
        'UploadedFile',
        'UploadedFiles'
    ];

    /**
     * Cache of the uploaded files
     *
     * @var array<File>|null
     */
    private $uploadedFilesCache = [];

    /**
     * Return the value of this field for inclusion into things such as
     * reports.
     *
     * @return string|bool
     */
    public function getFormattedValue()
    {
        $title = _t(__CLASS__ . '.DOWNLOADFILE', 'Download File');
        $values = [];
        $links = $this->getLinks(false);
        if ($links) {
            foreach ($this->getUploadedFilesFromDraft() ?: [] as $file) {
                if (!$link = $links[$file->ID] ?? null) {
                    continue;
                }

                $name = $file->Name;

                if (!$file->canView()) {
                    if (Security::getCurrentUser()) {
                        // Logged in CMS user without permissions to view file in the CMS
                        $default = 'You don\'t have the right permissions to download this file';
                        $message = _t(__CLASS__ . '.INSUFFICIENTRIGHTS', $default);
                        $values[] = sprintf(
                            '<i class="icon font-icon-lock"></i> %s - <em>%s</em>',
                            htmlspecialchars($name, ENT_QUOTES),
                            htmlspecialchars($message, ENT_QUOTES)
                        );
                    } else {
                        // Userforms submission filled in by non-logged in user being emailed to recipient
                        $message = _t(__CLASS__ . '.YOUMUSTBELOGGEDIN', 'You must be logged in to view this file');
                        $values[] = sprintf(
                            '%s - <a href="%s" target="_blank">%s</a> - <em>%s</em>',
                            htmlspecialchars($name, ENT_QUOTES),
                            htmlspecialchars($link, ENT_QUOTES),
                            htmlspecialchars($title, ENT_QUOTES),
                            htmlspecialchars($message, ENT_QUOTES)
                        );
                    }
                } else {
                    // Logged in CMS user with permissions to view file in the CMS
                    $values[] = sprintf(
                        '%s - <a href="%s" target="_blank">%s</a>',
                        htmlspecialchars($name, ENT_QUOTES),
                        htmlspecialchars($link, ENT_QUOTES),
                        htmlspecialchars($title, ENT_QUOTES)
                    );
                }
            }
        }
        return $values ? DBField::create_field('HTMLText', implode('<br>', $values)) : false;
    }

    /**
     * Return the value for this field in the CSV export.
     *
     * @return string
     */
    public function getExportValue()
    {
        return ($links = $this->getLinks()) ? implode("\r", $links) : '';
    }

    /**
     * Return the link for the file attached to this submitted form field.
     *
     * @return array|null
     */
    public function getLinks($grant = true)
    {
        if ($files = $this->getUploadedFilesFromDraft()) {
            return array_reduce($files, function ($links, $file) use ($grant) {
                if ($file->exists()) {
                    $url = $file->getURL($grant);
                    if ($url) {
                        $links[$file->ID] = Director::absoluteURL($url);
                    }
                }
                return $links;
            }, []);
        }
        return null;
    }

    /**
     * As uploaded files are stored in draft by default, this retrieves the
     * uploaded files from draft mode rather than using the current stage.
     *
     * @return array<File>|null
     */
    public function getUploadedFilesFromDraft()
    {
        if (array_key_exists($this->ID, $this->uploadedFilesCache)) {
            return $this->uploadedFilesCache[$this->ID];
        }

        $fileId = $this->UploadedFileID;

        return Versioned::withVersionedMode(function () use ($fileId) {
            Versioned::set_stage(Versioned::DRAFT);

            if ($uploadedFiles = $this->UploadedFiles()->map('ID', 'ID')->toArray()) {
                $files = File::get()->byIDs($uploadedFiles)->toArray();
            } else {
                $files = ($file = File::get()->byID($fileId)) ? [$file] : null;
            }

            return $this->uploadedFilesCache[$this->ID] = $files;
        });
    }

    /**
     * Return the names of the files, if present
     *
     * @return array|null
     */
    public function getFileNames()
    {
        if ($files = $this->getUploadedFilesFromDraft()) {
            return array_map(fn ($file) => $file->Name, $files);
        }
        return null;
    }
}
