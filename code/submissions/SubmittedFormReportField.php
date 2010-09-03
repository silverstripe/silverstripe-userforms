<?php
/**
 * Displays a summary of instances of a form submitted to the website
 *
 * @package userforms
 */

class SubmittedFormReportField extends FormField {
	
	function Field() {
		Requirements::css(SAPPHIRE_DIR . "/css/SubmittedFormReportField.css");
		Requirements::javascript("userforms/javascript/UserForm.js");
		return $this->renderWith("SubmittedFormReportField");
	}
	
	/**
	 * Return the submissions from the site
	 *
	 * @return ComponentSet
	 */ 
	function Submissions() {
		$pageStart = isset($_REQUEST['start']) && is_numeric($_REQUEST['start']) ? $_REQUEST['start'] : 0;
		$pageLength = 10;
		
		$items = $this->form->getRecord()->getComponents('Submissions', null, "\"Created\" DESC", null, "$pageStart,$pageLength");
		$formId = $this->form->getRecord()->ID;

		foreach(DB::query("SELECT COUNT(*) AS \"CountRows\" FROM \"SubmittedForm\" WHERE \"ParentID\" = $formId") as $r) $totalCount = $r['CountRows'];

		$items->setPageLimits($pageStart, $pageLength, $totalCount);
		$items->NextStart = $pageStart + $pageLength;
		$items->PrevStart = $pageStart - $pageLength;
		$items->Start = $pageStart;
		$items->StartPlusOffset = $pageStart+$pageLength;
		$items->TotalCount = $totalCount;

		return $items;
	}
	
	function getSubmissions() {
		return $this->customise(array(
			'Submissions' => $this->Submissions()
		))->renderWith(array('SubmittedFormReportField'));
	}

	/**
	 * ID of this forms record
	 * 
	 * @return int
	 */
	function RecordID() {
		return $this->form->getRecord()->ID;
	}
	
	/**
	 * Export each of the form submissions for this UserDefinedForm
	 * instance into a CSV file.
	 * 
	 * In order to run this export function, the user must be
	 * able to edit the page, so we check canEdit()
	 *
	 * @return HTTPResponse / bool
	 */
	public function export() {
		$now = Date("Y-m-d_h.i.s");                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     
		$fileName = "export-$now.csv";
		$separator = ",";
		
		// Get the UserDefinedForm to export data from the URL
		$SQL_ID = (isset($_REQUEST['id'])) ? Convert::raw2sql($_REQUEST['id']) : false;

		return $this->generateExport($SQL_ID);
	}
	
	/**
	 * Delete all the submissions listed in the user defined form
	 *
	 * @return Redirect|Boolean
	 */
	public function deletesubmissions() {
		$SQL_ID = (isset($_REQUEST['id'])) ? Convert::raw2sql($_REQUEST['id']) : false;
		if($SQL_ID) {
			$udf = DataObject::get_by_id("UserDefinedForm", $SQL_ID);
			$submissions = $udf->Submissions();
			if($submissions) {
				foreach($submissions as $submission) {
					// delete the submission @see $submission->onBeforeDelete() for more info
					$submission->delete();
				}
				return (Director::is_ajax()) ? true : Director::redirectBack();
			}
		}
		return (Director::is_ajax()) ? false : Director::redirectBack();
	}
	
	/**
	 * Delete a given submission from a user defined form
	 *
	 * @return Redirect|Boolean
	 */
	public function deletesubmission() {
		$SQL_ID = (isset($_REQUEST['id'])) ? Convert::raw2sql($_REQUEST['id']) : false;
		if($SQL_ID) {
			$submission = DataObject::get_by_id("SubmittedForm", $SQL_ID);
			if($submission) {
				$submission->delete();
				
				return (Director::is_ajax()) ? true : Director::redirectBack();
			}
		}
		return (Director::is_ajax()) ? false : Director::redirectBack();
	}
}