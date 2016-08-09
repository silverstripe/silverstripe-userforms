<?php
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

    /**
     * @return FieldList
     */
    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->removeByName(array('MergeField', 'Default', 'Validation', 'RightTitle'));

        return $fields;
    }

    /**
     * @return FormField
     */
    public function getFormField()
    {
        $field = UserFormsStepField::create()
            ->setName($this->Name)
            ->setTitle($this->EscapedTitle);
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
        return new LabelField(
            $column,
            $this->CMSTitle
        );
    }

    public function getCMSTitle()
    {
        $title = $this->getFieldNumber()
            ?: $this->Title
            ?: '';

        return _t(
            'EditableFormStep.STEP_TITLE',
            'Page {page}',
            array(
                'page' => $title
            )
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
