<?php

namespace SilverStripe\UserForms\Model\EditableFormField;

use SilverStripe\Core\Convert;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\LabelField;
use SilverStripe\UserForms\FormField\UserFormsGroupField;
use SilverStripe\UserForms\Model\EditableFormField;

/**
 * Specifies that this ends a group of fields
 *
 * @property int $EditableFieldGroupEndID
 * @method EditableFieldGroupEnd End()
 */
class EditableFieldGroup extends EditableFormField
{
    private static $has_one = [
        'End' => EditableFieldGroupEnd::class,
    ];

    private static $owns = [
        'End',
    ];

    private static $cascade_deletes = [
        'End',
    ];

    /**
     * Disable selection of group class
     *
     * @config
     * @var bool
     */
    private static $hidden = true;

    /**
     * Non-data field type
     *
     * @var bool
     */
    private static $literal = true;

    private static $table_name = 'EditableFieldGroup';

    public function getCMSFields()
    {
        $this->beforeUpdateCMSFields(function (FieldList $fields) {
            $fields->removeByName(['MergeField', 'Default', 'Validation', 'DisplayRules']);
        });

        return parent::getCMSFields();
    }

    public function getCMSTitle()
    {
        $title = $this->getFieldNumber()
            ?: $this->Title
            ?: 'group';

        return _t(
            'SilverStripe\\UserForms\\Model\\EditableFormField\\EditableFieldGroupEnd.FIELD_GROUP_START',
            'Group {group}',
            ['group' => $title]
        );
    }

    public function getInlineClassnameField($column, $fieldClasses)
    {
        return LabelField::create($column, $this->CMSTitle);
    }

    public function showInReports()
    {
        return false;
    }

    public function getFormField()
    {
        $field = UserFormsGroupField::create()
            ->setTitle($this->Title ?: false)
            ->setName($this->Name);
        $this->doUpdateFormField($field);
        return $field;
    }

    protected function updateFormField($field)
    {
        // set the right title on this field
        if ($this->RightTitle) {
            // Since this field expects raw html, safely escape the user data prior
            $field->setRightTitle(Convert::raw2xml($this->RightTitle));
        }

        // if this field has an extra class
        if ($this->ExtraClass) {
            $field->addExtraClass($this->ExtraClass);
        }
    }
}
