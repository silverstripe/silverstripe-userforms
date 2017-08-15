<?php

namespace SilverStripe\UserForms\Model\EditableFormField;

use SilverStripe\Forms\HiddenField;
use SilverStripe\Forms\LabelField;
use SilverStripe\Security\Group;
use SilverStripe\UserForms\Model\EditableFormField;
use SilverStripe\UserForms\Model\EditableFormField\EditableFieldGroup;

/**
 * Specifies that this ends a group of fields
 */
class EditableFieldGroupEnd extends EditableFormField
{
    private static $belongs_to = [
        'Group' => EditableFieldGroup::class
    ];

    /**
     * Disable selection of group class
     *
     * @config
     * @var bool
     */
    private static $hidden = true;

    /**
     * Non-data type
     *
     * @config
     * @var bool
     */
    private static $literal = true;

    private static $table_name = 'EditableFieldGroupEnd';

    public function getCMSTitle()
    {
        $group = $this->Group();
        return _t(
            __CLASS__.'.FIELD_GROUP_END',
            '{group} end',
            [
                'group' => ($group && $group->exists()) ? $group->CMSTitle : Group::class
            ]
        );
    }

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->removeByName(['MergeField', 'Default', 'Validation', 'DisplayRules']);
        return $fields;
    }

    public function getInlineClassnameField($column, $fieldClasses)
    {
        return LabelField::create($column, $this->CMSTitle);
    }

    public function getInlineTitleField($column)
    {
        return HiddenField::create($column);
    }

    public function getFormField()
    {
        return null;
    }

    public function showInReports()
    {
        return false;
    }

    public function onAfterWrite()
    {
        parent::onAfterWrite();

        // If this is not attached to a group, find the first group prior to this
        // with no end attached
        $group = $this->Group();
        if (!($group && $group->exists()) && $this->ParentID) {
            $group = EditableFieldGroup::get()
                ->filter([
                    'ParentID' => $this->ParentID,
                    'Sort:LessThanOrEqual' => $this->Sort
                ])
                ->where('"EditableFieldGroup"."EndID" IS NULL OR "EditableFieldGroup"."EndID" = 0')
                ->sort('"Sort" DESC')
                ->first();

            // When a group is found, attach it to this end
            if ($group) {
                $group->EndID = $this->ID;
                $group->write();
            }
        }
    }
}
