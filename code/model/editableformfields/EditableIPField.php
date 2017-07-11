<?php
/**
 * EditableIPField
 *
 * Allows you to track the ip of the person filling in the form.
 *
 * @package userforms
 */

class EditableIPField extends EditableFormField
{

    private static $singular_name = 'IP Field';

    private static $plural_name = 'IP Fields';

    private static $has_placeholder = false;

    public function getSetsOwnError()
    {
        return true;
    }

    public function getFormField()
    {
        $field = HiddenField::create($this->Name, $this->EscapedTitle, $_SERVER['REMOTE_ADDR']);
        return $field;
    }
}
