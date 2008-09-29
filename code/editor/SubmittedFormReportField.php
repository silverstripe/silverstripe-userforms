<?php
/**
 * Displays a summary of instances of a form submitted to the website
 * @package cms
 */
class SubmittedFormReportField extends FormField {
	
	function Field() {
		Requirements::css(SAPPHIRE_DIR . "/css/SubmittedFormReportField.css");
		
		return $this->renderWith("SubmittedFormReportField");
	}
	
	function Submissions() {
		return $this->form->getRecord()->Submissions();
	}
	
	function ExportLink() {
		if($this->Submissions() && $this->Submissions()->Count() > 0) {
			return $this->form->getRecord()->Link() . 'export/' . $this->form->getRecord()->ID;
		}
	}
	
}
?>