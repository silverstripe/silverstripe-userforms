<?php

/**
 * @package userforms
 */
class UserFormsCheckboxSetField extends CheckboxSetField {

	/**
	 * jQuery validate requires that the value of the option does not contain
	 * the actual value of the input.
	 *
	 * @return ArrayList
	 */
	public function getOptions() {
		$options = parent::getOptions();

		foreach($options as $option) {
			$option->Name = "{$this->name}[]";
		}

		return $options;
	}
}