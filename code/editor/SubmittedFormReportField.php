<?php
/**
 * Displays a summary of instances of a form submitted to the website
 *
 * @package userforms
 */
class SubmittedFormReportField extends FormField {
	
	function Field() {
		Requirements::css(SAPPHIRE_DIR . "/css/SubmittedFormReportField.css");
		
		return $this->renderWith("SubmittedFormReportField");
	}
	
	/**
	 * Return the submissions from the site
	 *
	 * @return ComponentSet
	 */ 
	function Submissions() {
		return $this->form->getRecord()->Submissions();
	}
	
	/**
	 * Link to the export function of the controller
	 * 
	 * @return String
	 */
	function ExportLink() {
		if($this->Submissions() && $this->Submissions()->Count() > 0) {
			return $this->form->getRecord()->Link() . 'export/' . $this->form->getRecord()->ID;
		}
	}
	
	/**
	 * Link to the delete the submission
	 * 
	 * @return String
	 */
	function DeleteLink() {
		if($this->Submissions() && $this->Submissions()->Count() > 0) {
			return $this->form->getRecord()->Link() . 'deletesubmissions/' . $this->form->getRecord()->ID;
		}		
	}
}
?>