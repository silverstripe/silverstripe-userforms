<?php

/**
 * UserForms Versioned Task
 *
 * Initial migration script for forms that do not exist on the live site.
 * In previous versions of UserForms it did not provide staging / live functionality
 * When upgrading to the new version we need to publish the existing pages.
 *
 * @package userforms
 */

class UserFormsVersionedTask extends MigrationTask {

	protected $title = "UserForms Versioned Initial Migration";

	protected $description = "Publishes the existing forms";
	
	/**
	 * Publish the existing forms.
	 *
	 */
	public function run($request) {
		$forms = Versioned::get_by_stage('UserDefinedForm', 'Live');
		
		if($forms) {
			foreach($forms as $form) {
				echo "Publishing $form->Title <br />";
				$form->doPublish();
			}
			echo "Complete";
		}
		else {
			echo "No Forms Found";
		}
	}
}


