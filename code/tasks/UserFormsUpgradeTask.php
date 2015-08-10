<?php

/**
 * Assists with upgrade of userforms to 3.0
 *
 * @author dmooyman
 */
class UserFormsUpgradeTask extends BuildTask {

	protected $title = "UserForms 3.0 Migration Tool";

	protected $description = "Upgrade tool for sites upgrading to userforms 3.0";

	public function run($request) {
		$this->log("Upgrading userforms module");
		Injector::inst()
			->create('UserFormsUpgradeService')
			->setQuiet(false)
			->run();
		$this->log("Done");
	}
}
