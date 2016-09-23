<?php

/**
 * Base Class for EditableOption Fields such as the ones used in
 * dropdown fields and in radio check box groups
 *
 * @method EditableMultipleOptionField Parent()
 * @package userforms
 */
class EditableOption extends DataObject
{

	private static $default_sort = "Sort";

	private static $db = array(
		"Name" => "Varchar(255)",
		"Title" => "Varchar(255)",
		"Default" => "Boolean",
        "Sort" => "Int",
        "Value" => "Varchar(255)",
	);

	private static $has_one = array(
		"Parent" => "EditableMultipleOptionField",
	);

	private static $extensions = array(
		"Versioned('Stage', 'Live')"
	);

	private static $summary_fields = array(
		'Title',
		'Default'
	);

    protected static $allow_empty_values = false;

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
	 * @param Member $member
	 *
	 * @return boolean
	 */
    public function canEdit($member = null)
    {
		return $this->Parent()->canEdit($member);
	}

	/**
	 * @param Member $member
	 *
	 * @return boolean
	 */
    public function canDelete($member = null)
    {
		return $this->canEdit($member);
	}

    public function getEscapedTitle()
    {
		return Convert::raw2att($this->Title);
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
    public function canCreate($member = null)
    {
		// Check parent page
        $parent = $this->getCanCreateContext(func_get_args());
        if($parent) {
            return $parent->canEdit($member);
        }

        // Fall back to secure admin permissions
        return parent::canCreate($member);
	}

    /**
     * Helper method to check the parent for this object
     *
     * @param array $args List of arguments passed to canCreate
     * @return DataObject Some parent dataobject to inherit permissions from
     */
    protected function getCanCreateContext($args)
    {
        // Inspect second parameter to canCreate for a 'Parent' context
        if(isset($args[1]['Parent'])) {
            return $args[1]['Parent'];
        }
        // Hack in currently edited page if context is missing
        if(Controller::has_curr() && Controller::curr() instanceof CMSMain) {
            return Controller::curr()->currentPage();
        }

        // No page being edited
        return null;
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
