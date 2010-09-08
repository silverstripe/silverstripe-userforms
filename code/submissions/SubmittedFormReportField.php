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
	public function export($id = false) {
		if($id && is_int($id)) {
			$SQL_ID = $id;
		}
		else {
			if(isset($_REQUEST['id'])) {
				$SQL_ID = Convert::raw2sql($_REQUEST['id']);
			}
			else {
				user_error("No UserDefinedForm Defined.", E_USER_ERROR);
				
				return false;
			}
		}

		$now = Date("Y-m-d_h.i.s");                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     
		$fileName = "export-$now.csv";
		$separator = ",";

		$udf = DataObject::get_by_id("UserDefinedForm", $SQL_ID);
		
		if($udf) {
			$csvHeaderNames = array();
			$csvHeaderTitle = array();
			
			$submissions = $udf->Submissions();
			
			if($submissions && $submissions->exists()) {
				
				// Get all the submission IDs (so we know what names/titles to get - helps for sites with many UDF's)
				$inClause = array();
				foreach($submissions as $submission) {
					$inClause[] = $submission->ID;
				}

				// Get the CSV header rows from the database
				
				$tmp = DB::query("
					SELECT DISTINCT \"SubmittedFormField\".\"ID\", \"Name\", \"Title\"
					FROM \"SubmittedFormField\"
					LEFT JOIN \"SubmittedForm\" ON \"SubmittedForm\".\"ID\" = \"SubmittedFormField\".\"ParentID\"
					WHERE \"SubmittedFormField\".\"ParentID\" IN (" . implode(',', $inClause) . ")
					GROUP BY \"Name\"
					ORDER BY \"SubmittedFormField\".\"ID\"
				");

				// Sort the Names and Titles from the database query into separate keyed arrays
				foreach($tmp as $array) {
					$csvHeaderNames[] = $array['Name'];
					$csvHeaderTitle[] = $array['Title'];
				}
				
				// For every submission...
				$i = 0;
				foreach($submissions as $submission) {
					
					// Get the rows for this submission (One row = one form field)
					$dataRow = $submission->Values();
					
					$rows[$i] = array();
					
					// For every row/field, get all the columns
					foreach($dataRow as $column) {
						// If the Name of this field is in the $csvHeaderNames array, get an array of all the places it exists
						if($index = array_keys($csvHeaderNames, $column->Name)) {
							if(is_array($index)) {
								// Set the final output array for each index that we want to insert this value into
								foreach($index as $idx) {
									$rows[$i][$idx] = $column->Value;
								}						
							}
						}
					}
					
					$rows[$i]['Submitted'] = $submission->Created;
					
					$i++;
				}
				
				// CSV header row
				$csvData = '"' . implode('","', $csvHeaderTitle) . '"' . ',"Submitted"'."\n";

				// For every row of data (one form submission = one row)
				foreach($rows as $row) {

					for($i=0;$i<count($csvHeaderNames);$i++) {
						
						if(!isset($row[$i]) || !$row[$i]) $csvData .= '"",';    // If there is no data for this column, output it as blank instead 
						else {
							$tmp = str_replace('"', '""', $row[$i]); 
							$csvData .= '"' . $tmp . '",';
						}
					}
					
					// Start a new row for each submission (re-check we have 'Submitted' in this entry)
					if(isset($row['Submitted'])) $csvData .= '"'.$row['Submitted'].'"'."\n";
					else $csvData .= "\n";
				}
			} else {
				user_error("No submissions to export.", E_USER_ERROR);
			}

			if(SapphireTest::is_running_test()) {
				return $csvData;
			}
			else {
				SS_HTTPRequest::send_file($csvData, $fileName)->output();	
			}
		} else {
			user_error("'$SQL_ID' is a valid type, but we can't find a UserDefinedForm in the database that matches the ID.", E_USER_ERROR);
		}
	}
	
	/**
	 * Delete all the submissions listed in the user defined form
	 *
	 * @return Redirect|Boolean
	 */
	public function deletesubmissions($id = false) {
		if($id && is_int($id)) {
			$SQL_ID = $id;
		}
		else {
			if(isset($_REQUEST['id'])) {
				$SQL_ID = Convert::raw2sql($_REQUEST['id']);
			}
		}
		
		if(isset($SQL_ID)) {
			$udf = DataObject::get_by_id("UserDefinedForm", $SQL_ID);
			$submissions = $udf->Submissions();
			
			if($submissions) {
				foreach($submissions as $submission) {
					$submission->delete();
				}
				return (Director::is_ajax() || SapphireTest::is_running_test()) ? true : Director::redirectBack();
			}
		}
		return (Director::is_ajax() || SapphireTest::is_running_test()) ? false : Director::redirectBack();
	}
	
	/**
	 * Delete a given submission from a user defined form
	 *
	 * @return Redirect|Boolean
	 */
	public function deletesubmission($id = false) {
		if($id && is_int($id)) {
			$SQL_ID = $id;
		}
		else {
			if(isset($_REQUEST['id'])) {
				$SQL_ID = Convert::raw2sql($_REQUEST['id']);
			}
		}
		
		if(isset($SQL_ID)) {
			$submission = DataObject::get_by_id("SubmittedForm", $SQL_ID);
			if($submission) {
				$submission->delete();
				
				return (Director::is_ajax() || SapphireTest::is_running_test()) ? true : Director::redirectBack();
			}
		}
		return (Director::is_ajax() || SapphireTest::is_running_test()) ? false : Director::redirectBack();
	}
}