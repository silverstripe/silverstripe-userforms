<?php
/**
 * EditableRadioField
 *
 * Represents a set of selectable radio buttons
 *
 * @package userforms
 */

class EditableRadioField extends EditableMultipleOptionField {
	private static $singular_name = 'Radio field';
	
	private static $plural_name = 'Radio fields';

	private static $form_field_class = 'OptionsetField';
}
