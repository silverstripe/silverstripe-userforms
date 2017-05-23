<?php
/**
 * EditableDropdown
 *
 * Represents a modifiable dropdown (select) box on a form
 *
 * @package userforms
 */

class EditableDropdown extends EditableMultipleOptionField {

	private static $singular_name = 'Dropdown Field';

	private static $plural_name = 'Dropdowns';

    private static $db = array(
        'EmptyString' => 'Varchar(255)',
    );

	/**
	 * @return FieldList
	 */
	public function getCMSFields() {
		$fields = parent::getCMSFields();

		$fields->removeByName('Default');

        $tab = $fields->fieldByName('Root.Options');
        if($tab) {
            $placeholder = TextField::create('EmptyString', 'Placeholder Text')
                ->setDescription('Set some placeholder text on the dropdown field.');

            $tab->insertBefore($placeholder, 'Options');
        }

		return $fields;
	}

	/**
	 * @return DropdownField
	 */
	public function getFormField() {
		$field = DropdownField::create($this->Name, $this->EscapedTitle, $this->getOptionsMap())
			->setFieldHolderTemplate('UserFormsField_holder')
			->setTemplate('UserFormsDropdownField');

        if(!empty(trim($this->EmptyString))) {
            $field->setEmptyString($this->EmptyString);
        }

		// Set default
		$defaultOption = $this->getDefaultOptions()->first();
		if($defaultOption) {
			$field->setValue($defaultOption->EscapedTitle);
		}
		$this->doUpdateFormField($field);
		return $field;
	}

	public function getSelectorField(EditableCustomRule $rule, $forOnLoad = false) {
		return "$(\"select[name='{$this->Name}']\")";
	}
}
