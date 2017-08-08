<?php

/**
 * Specifies that this ends a group of fields
 */
class EditableFieldGroup extends EditableFormField
{

    private static $has_one = array(
        'End' => 'EditableFieldGroupEnd'
    );

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
     * @var type
     */
    private static $literal = true;

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->removeByName(array('MergeField', 'Default', 'Validation', 'DisplayRules'));
        return $fields;
    }

    public function getCMSTitle()
    {
        $title = $this->getFieldNumber()
            ?: $this->Title
            ?: 'group';

        return _t(
            'EditableFieldGroupEnd.FIELD_GROUP_START',
            'Group {group}',
            array(
                'group' => $title
            )
        );
    }

    public function getInlineClassnameField($column, $fieldClasses)
    {
        return new LabelField($column, $this->CMSTitle);
    }

    public function showInReports()
    {
        return false;
    }

    public function getFormField()
    {
        $field = UserFormsGroupField::create()
            ->setTitle($this->EscapedTitle ?: false)
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

    protected function onBeforeDelete()
    {
        parent::onBeforeDelete();

        // Ensures EndID is lazy-loaded for onAfterDelete
        $this->EndID;
    }

    protected function onAfterDelete()
    {
        parent::onAfterDelete();

        // Delete end
        if (($end = $this->End()) && $end->exists()) {
            $end->delete();
        }
    }
}
