<?php

/**
 * {@link TreeDropdownField} subclass for handling loading folders through the
 * nested {@link FormField} instances of the {@link FieldEditor}
 *
 * @deprecated since version 4.0
 * @package userforms
 */
class UserformsTreeDropdownField extends TreeDropdownField
{

    public function __construct($name, $title = null, $sourceObject = 'Group', $keyField = 'ID', $labelField = 'TreeTitle', $showSearch = true)
    {
        parent::__construct($name, $title, $sourceObject, $keyField, $labelField, $showSearch);

        Deprecation::notice('4.0', __CLASS__ . " is deprecated");
    }
}
