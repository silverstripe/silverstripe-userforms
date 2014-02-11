<?php
/**
 * EditableNumericField
 *
 * This control represents a user-defined numeric field in a user defined form
 *
 * @package userforms
 */

class EditableNumericField extends EditableTextField {

	private static $singular_name = 'Numeric Field';
	
	private static $plural_name = 'Numeric Fields';
	

	/**
	 * @return TextareaField|TextField
	 */
	public function getFormField() {
		if($this->getSetting('Rows') && $this->getSetting('Rows') > 1) {
			$taf = new NumericField($this->Name, $this->Title);
			$taf->setRows($this->getSetting('Rows'));
			$taf->addExtraClass('number');
		}
		else {
			$taf = new NumericField($this->Name, $this->Title, null, $this->getSetting('MaxLength'));
			$taf->addExtraClass('number');
		}
		if ($this->Required) {
			//  Required and numeric validation can conflict so add the Required validation messages
			// as input attributes
			$errorMessage = $this->getErrorMessage()->HTML();
			$taf->setAttribute('data-rule-required','true');
			$taf->setAttribute('data-msg-required',$errorMessage);
		}
		return $taf;
	}
}
