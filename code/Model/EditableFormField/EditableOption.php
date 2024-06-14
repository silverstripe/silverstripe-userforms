<?php

namespace SilverStripe\UserForms\Model\EditableFormField;

use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Member;
use SilverStripe\Versioned\Versioned;

/**
 * Base Class for EditableOption Fields such as the ones used in
 * dropdown fields and in radio check box groups
 *
 * @package userforms
 * @property int $Default
 * @property string $Name
 * @property int $ParentID
 * @property int $Sort
 * @property string $Value
 * @mixin Versioned
 * @method EditableMultipleOptionField Parent()
 */
class EditableOption extends DataObject
{
    private static $default_sort = 'Sort';

    private static $db = [
        'Name' => 'Varchar(255)',
        'Title' => 'Varchar(255)',
        'Default' => 'Boolean',
        'Sort' => 'Int',
        'Value' => 'Varchar(255)',
    ];

    private static $has_one = [
        'Parent' => EditableMultipleOptionField::class,
    ];

    private static $extensions = [
        Versioned::class . "('Stage', 'Live')",
    ];

    private static $summary_fields = [
        'Title',
        'Default',
    ];

    protected static $allow_empty_values = false;

    private static $table_name = 'EditableOption';

    /**
     * Returns whether to allow empty values or not.
     *
     * @return boolean
     */
    public static function allow_empty_values()
    {
        return (bool) EditableOption::$allow_empty_values;
    }

    /**
     * Set whether to allow empty values.
     *
     * @param boolean $allow
     */
    public static function set_allow_empty_values($allow)
    {
        EditableOption::$allow_empty_values = (bool) $allow;
    }

    /**
     * Fetches a value for $this->Value. If empty values are not allowed,
     * then this will return the title in the case of an empty value.
     *
     * @return string
     */
    public function getValue()
    {
        $value = $this->getField('Value');
        if (empty($value) && !EditableOption::allow_empty_values()) {
            return $this->Title;
        }
        return $value;
    }

    protected function onBeforeWrite()
    {
        if (!$this->Sort) {
            $this->Sort = EditableOption::get()->max('Sort') + 1;
        }

        parent::onBeforeWrite();
    }

    /**
     * @param Member $member
     * @return boolean
     */
    public function canEdit($member = null)
    {
        return $this->Parent()->canEdit($member);
    }
    /**
     * @param Member $member
     * @return boolean
     */
    public function canDelete($member = null)
    {
        return $this->canEdit($member);
    }

    /**
     * @param Member $member
     * @return bool
     */
    public function canView($member = null)
    {
        return $this->Parent()->canView($member);
    }

    /**
     * Return whether a user can create an object of this type
     *
     * @param Member $member
     * @param array $context Virtual parameter to allow context to be passed in to check
     * @return bool
     */
    public function canCreate($member = null, $context = [])
    {
        // Check parent object
        $parent = $this->Parent();
        if ($parent) {
            return $parent->canCreate($member);
        }

        // Fall back to secure admin permissions
        return parent::canCreate($member);
    }

    /**
     * @param Member $member
     * @return bool
     */
    public function canPublish($member = null)
    {
        return $this->canEdit($member);
    }
    /**
     * @param Member $member
     * @return bool
     */
    public function canUnpublish($member = null)
    {
        return $this->canDelete($member);
    }
}
