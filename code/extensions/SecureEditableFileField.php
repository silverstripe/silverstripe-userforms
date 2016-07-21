<?php

/**
 * Provides additional file security for uploaded files when the securefiles module is installed
 *
 * {@see EditableFileField}
 */
class SecureEditableFileField extends DataExtension
{

    /**
     * Path to secure files location under assets
     *
     * @config
     * @var type
     */
    private static $secure_folder_name = 'SecureUploads';

    /**
     * Disable file security if a user-defined mechanism is in place
     *
     * @config
     * @var bool
     */
    private static $disable_security = false;

    /*
     * Check if file security is enabled
     *
     * @return bool
     */
    public function getIsSecurityEnabled()
    {
        // Skip if requested
        if ($this->owner->config()->disable_security) {
            return false;
        }

        // Check for necessary security module
        if (!class_exists('SecureFileExtension')) {
            trigger_error('SecureEditableFileField requires secureassets module', E_USER_WARNING);
            return false;
        }

        return true;
    }

    public function requireDefaultRecords()
    {
        // Skip if disabled
        if (!$this->getIsSecurityEnabled()) {
            return;
        }

        // Update all instances of editablefilefield which do NOT have a secure folder assigned
        foreach (EditableFileField::get() as $fileField) {
            // Skip if secured
            if ($fileField->getIsSecure()) {
                continue;
            }

            // Force this field to secure itself on write
            $fileField->write(false, false, true);
            DB::alteration_message(
                "Restricting editable file field \"{$fileField->Title}\" to secure folder",
                "changed"
            );
        }
    }

    /**
     * Secure this field before saving
     */
    public function onBeforeWrite()
    {
        $this->makeSecure();
    }

    /**
     * Ensure this field is secured, but does not write changes to the database
     */
    public function makeSecure()
    {
        // Skip if disabled or already secure
        if (!$this->getIsSecurityEnabled() || $this->owner->getIsSecure()) {
            return;
        }

        // Ensure folder exists
        $folder = $this->owner->Folder();
        if (!$folder || !$folder->exists()) {
            // Create new folder in default location
            $folder = Folder::find_or_make($this->owner->config()->secure_folder_name);
            $this->owner->FolderID = $folder->ID;
        } elseif ($this->isFolderSecured($folder)) {
            // If folder exists and is secure stop
            return;
        }

        // Make secure
        $folder->CanViewType = 'OnlyTheseUsers';
        $folder->ViewerGroups()->add($this->findAdminGroup());
        $folder->write();
    }

    /**
     * Find target group to record
     *
     * @return Group
     */
    protected function findAdminGroup()
    {
        singleton('Group')->requireDefaultRecords();
        return Permission::get_groups_by_permission('ADMIN')->First();
    }

    /**
     * Determine if the field is secure
     *
     * @return bool
     */
    public function getIsSecure()
    {
        return $this->isFolderSecured($this->owner->Folder());
    }

    /**
     * Check if a Folder object is secure
     *
     * @param Folder $folder
     * @return boolean
     */
    protected function isFolderSecured($folder)
    {
        if (! ($folder instanceof Folder) || !$folder->exists()) {
            return false;
        }

        switch ($folder->CanViewType) {
            case 'OnlyTheseUsers':
                return true;
            case 'Inherit':
                $parent = $folder->Parent();
                return $parent && $parent->exists() && $this->isFolderSecured($parent);
            case 'Anyone':
            case 'LoggedInUsers':
            default:
                return false;
        }
    }
}
