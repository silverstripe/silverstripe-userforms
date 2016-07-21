<?php
/**
 * EditableEmailField
 *
 * Allow users to define a validating editable email field for a UserDefinedForm
 *
 * @package userforms
 */

class EditableEmailField extends EditableFormField
{

    private static $singular_name = 'Email Field';

    private static $plural_name = 'Email Fields';

    private static $db = array(
        'Placeholder' => 'Varchar(255)'
    );

    public function getCMSFields()
    {
        $this->beforeUpdateCMSFields(function ($fields) {
            $fields->addFieldToTab(
                'Root.Main',
                TextField::create(
                    'Placeholder',
                    _t('EditableTextField.PLACEHOLDER', 'Placeholder')
                )
            );
        });

        return parent::getCMSFields();
    }

    public function getSetsOwnError()
    {
        return true;
    }

    public function getFormField()
    {
        $field = EmailField::create($this->Name, $this->EscapedTitle, $this->Default)
            ->setFieldHolderTemplate('UserFormsField_holder')
            ->setTemplate('UserFormsField');

        $this->doUpdateFormField($field);

        return $field;
    }

    /**
     * Updates a formfield with the additional metadata specified by this field
     *
     * @param FormField $field
     */
    protected function updateFormField($field)
    {
        parent::updateFormField($field);

        $field->setAttribute('data-rule-email', true);

        if ($this->Placeholder) {
            $field->setAttribute('placeholder', $this->Placeholder);
        }
    }
}
