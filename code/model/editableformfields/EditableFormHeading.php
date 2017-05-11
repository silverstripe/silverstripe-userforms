<?php
/**
 * Allows an editor to insert a generic heading into a field
 *
 * @package userforms
 */

class EditableFormHeading extends EditableFormField
{

    private static $singular_name = 'Heading';

    private static $plural_name = 'Headings';

    private static $literal = true;

    private static $db = array(
        'Level' => 'Int(3)', // From CustomSettings
        'HideFromReports' => 'Boolean(0)' // from CustomSettings
    );

    private static $defaults = array(
        'Level' => 3,
        'HideFromReports' => false
    );

    /**
     * @return FieldList
     */
    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->removeByName(array('Default', 'Validation', 'RightTitle'));

        $levels = array(
            '1' => '1',
            '2' => '2',
            '3' => '3',
            '4' => '4',
            '5' => '5',
            '6' => '6'
        );

        $fields->addFieldsToTab('Root.Main', array(
            DropdownField::create(
                'Level',
                _t('EditableFormHeading.LEVEL', 'Select Heading Level'),
                $levels
            ),
            CheckboxField::create(
                'HideFromReports',
                _t('EditableLiteralField.HIDEFROMREPORT', 'Hide from reports?')
            )
        ));

        return $fields;
    }

    public function getFormField()
    {
        $labelField = HeaderField::create($this->EscapedTitle)
            ->setHeadingLevel($this->Level);
        $labelField->addExtraClass('FormHeading');
        $labelField->setAttribute('data-id', $this->Name);
        $this->doUpdateFormField($labelField);
        return $labelField;
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

    public function showInReports()
    {
        return !$this->HideFromReports;
    }

    public function getFieldValidationOptions()
    {
        return false;
    }

    public function getSelectorHolder()
    {
        return "$(\":header[data-id='{$this->Name}']\")";
    }

    public function getLevel()
    {
        return $this->getField('Level') ?: 3;
    }
}
