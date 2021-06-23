<?php

namespace SilverStripe\UserForms\Model\EditableFormField;

use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\FormField;
use SilverStripe\Forms\LabelField;
use SilverStripe\UserForms\FormField\UserFormsStepField;
use SilverStripe\UserForms\Model\EditableFormField;

/**
 * A step in multi-page user form
 *
 * @package userforms
 */
class EditableFormStep extends EditableFormField
{
    private static $singular_name = 'Page Break';

    private static $plural_name = 'Page Breaks';

    /**
     * Disable selection of step class
     *
     * @config
     * @var bool
     */
    private static $hidden = true;

    private static $table_name = 'EditableFormStep';

    /**
     * @return FieldList
     */
    public function getCMSFields()
    {
        $this->beforeUpdateCMSFields(function (FieldList $fields) {
            $fields->removeByName(['MergeField', 'Default', 'Validation', 'RightTitle']);
        });

        return parent::getCMSFields();
    }

    /**
     * @return FormField
     */
    public function getFormField()
    {
        $field = UserFormsStepField::create()
            ->setName($this->Name)
            ->setTitle($this->Title);
        $this->doUpdateFormField($field);

        return $field;
    }

    protected function updateFormField($field)
    {
        // if this field has an extra class
        if ($this->ExtraClass) {
            $field->addExtraClass($this->ExtraClass);
        }
    }

    /**
     * @return boolean
     */
    public function showInReports()
    {
        return false;
    }

    public function getInlineClassnameField($column, $fieldClasses)
    {
        return LabelField::create($column, $this->CMSTitle);
    }

    public function getCMSTitle()
    {
        $title = $this->getFieldNumber()
            ?: $this->Title
            ?: '';

        return _t(
            __CLASS__.'.STEP_TITLE',
            'Page {page}',
            ['page' => $title]
        );
    }

    /**
     * Get the JS expression for selecting the holder for this field
     *
     * @return string
     */
    public function getSelectorHolder()
    {
        return "$(\".step-button-wrapper[data-for='{$this->Name}']\")";
    }
}
