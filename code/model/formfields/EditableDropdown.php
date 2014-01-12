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

	private static $form_field_class = 'DropdownField';
}