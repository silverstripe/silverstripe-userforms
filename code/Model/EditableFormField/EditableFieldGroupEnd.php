<?php

namespace SilverStripe\UserForms\Model\EditableFormField;

use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\HiddenField;
use SilverStripe\Forms\LabelField;
use SilverStripe\UserForms\Model\EditableFormField;

/**
 * Specifies that this ends a group of fields
 *
 * @method EditableFieldGroup Group()
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
                'group' => ($group && $group->exists()) ? $group->CMSTitle : 'Group'
            ]
        );
    }

    public function getCMSFields()
    {
        $this->beforeUpdateCMSFields(function (FieldList $fields) {
            $fields->removeByName(['MergeField', 'Default', 'Validation', 'DisplayRules']);
        });

        return parent::getCMSFields();
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
