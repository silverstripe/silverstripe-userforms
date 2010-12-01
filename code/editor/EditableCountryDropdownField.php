<?php

/**
 * A dropdown field which allows the user to select a country
 *
 * @package userforms
 */
class EditableCountryDropdownField extends EditableFormField {

	static $singular_name = 'Country Dropdown';
	
	static $plural_name = 'Country Dropdowns';
	
	public function getFormField() {
		return new DropdownField($this->Name, $this->Title, Geoip::getCountryDropDown());
	}
	
	public function getValueFromData($data) {
		if(isset($data[$this->Name])) {
			
			return Geoip::countryCode2name($data[$this->Name]);
		}
	}
	
	public function getIcon() {
		return 'userforms/images/editabledropdown.png';
	}
}