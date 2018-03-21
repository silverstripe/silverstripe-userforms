<?php

namespace SilverStripe\UserForms\Model\EditableFormField;

use SilverStripe\Core\Convert;
use SilverStripe\ORM\DataObject;
use SilverStripe\Versioned\Versioned;

/**
 * Base Class for EditableOption Fields such as the ones used in
 * dropdown fields and in radio check box groups
 *
 * @method EditableMultipleOptionField Parent()
 * @package userforms
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
        Versioned::class . "('Stage', 'Live')"
    ];

    private static $summary_fields = [
        'Title',
        'Default'
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
        return (bool) self::$allow_empty_values;
    }

    /**
     * Set whether to allow empty values.
     *
     * @param boolean $allow
     */
    public static function set_allow_empty_values($allow)
    {
        self::$allow_empty_values = (bool) $allow;
    }

    /**
     * @deprecated 5.0..6.0 Use "$Title" in templates instead
     * @return string
     */
    public function getEscapedTitle()
    {
        return Convert::raw2xml($this->Title);
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
        if (empty($value) && !self::allow_empty_values()) {
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
}
