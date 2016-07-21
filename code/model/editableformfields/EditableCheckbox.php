<?php
/**
 * EditableCheckbox
 *
 * A user modifiable checkbox on a UserDefinedForm
 *
 * @package userforms
 */

class EditableCheckbox extends EditableFormField
{

    private static $singular_name = 'Checkbox Field';

    private static $plural_name = 'Checkboxes';

    private static $db = array(
        'CheckedDefault' => 'Boolean' // from CustomSettings
    );

    /**
     * @return FieldList
     */
    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->replaceField('Default', CheckboxField::create(
            "CheckedDefault",
            _t('EditableFormField.CHECKEDBYDEFAULT', 'Checked by Default?')
        ));

        return $fields;
    }

    public function getFormField()
    {
        $field = CheckboxField::create($this->Name, $this->EscapedTitle, $this->CheckedDefault)
            ->setFieldHolderTemplate('UserFormsCheckboxField_holder')
            ->setTemplate('UserFormsCheckboxField');

        $this->doUpdateFormField($field);

        return $field;
    }

    public function getValueFromData($data)
    {
        $value = (isset($data[$this->Name])) ? $data[$this->Name] : false;

        return ($value) ? _t('EditableFormField.YES', 'Yes') : _t('EditableFormField.NO', 'No');
    }

    public function migrateSettings($data)
    {
        // Migrate 'Default' setting to 'CheckedDefault'
        if (isset($data['Default'])) {
            $this->CheckedDefault = (bool)$data['Default'];
            unset($data['Default']);
        }

        parent::migrateSettings($data);
    }
}
