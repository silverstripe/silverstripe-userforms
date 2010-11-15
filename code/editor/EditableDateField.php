<?php
/**
 * EditableDateField
 *
 * Allows a user to add a date field to the Field Editor
 *
 * @todo Localization, Time Field / Date time field combinations. Set ranges of dates,
 * 		set default date
 *
 * @package userforms
 */

class EditableDateField extends EditableFormField {
	
	static $singular_name = 'Date Field';
	
	static $plural_name = 'Date Fields';
	
	function getFieldConfiguration() {
		$defaultToToday = ($this->getSetting('DefaultToToday')) ? $this->getSetting('DefaultToToday') : false;
		
		return new FieldSet(
			new CheckboxField("Fields[$this->ID][CustomSettings][DefaultToToday]", _t('EditableFormField.DEFAULTTOTODAY', 'Default to Today?'), $defaultToToday)
		);
	}
	
	function populateFromPostData($data) {
		$fieldPrefix = 'Default-';
		
		if(empty($data['Default']) && !empty($data[$fieldPrefix.'Year']) && !empty($data[$fieldPrefix.'Month']) && !empty($data[$fieldPrefix.'Day'])) {
			$data['Default'] = $data['Year'] . '-' . $data['Month'] . '-' . $data['Day'];		
		}
		
		parent::populateFromPostData($data);
	}
	
	/**
	 * Return the form field.
	 *
	 * @todo Make a jQuery safe form field. The current CalendarDropDown
	 * 			breaks on the front end.
	 */
	public function getFormField() {
		// scripts for jquery date picker
		Requirements::javascript(THIRDPARTY_DIR .'/jquery-ui/jquery.ui.core.js');
		Requirements::javascript(THIRDPARTY_DIR .'/jquery-ui/jquery.ui.datepicker.js');
		
		$dateFormat = DateField_View_JQuery::convert_iso_to_jquery_format(i18n::get_date_format());

		Requirements::customScript(<<<JS
			(function(jQuery) {
				$(document).ready(function() {
					$('input[name^=EditableDateField]').attr('autocomplete', 'off').datepicker({ dateFormat: '$dateFormat' });
				});
			})(jQuery);
JS
, 'UserFormsDate');

		// css for jquery date picker
		Requirements::css(THIRDPARTY_DIR .'/jquery-ui-themes/smoothness/jquery-ui-1.8rc3.custom.css');
		
		$default = ($this->getSetting('DefaultToToday')) ? date('d/m/Y') : $this->Default;
		
		return new DateField( $this->Name, $this->Title, $default);
	}
	
	/**
	 * Return the validation information related to this field. This is 
	 * interrupted as a JSON object for validate plugin and used in the 
	 * PHP. 
	 *
	 * @see http://docs.jquery.com/Plugins/Validation/Methods
	 * @return Array
	 */
	public function getValidation() {
		return array(
			'date' => true
		);
	}
}