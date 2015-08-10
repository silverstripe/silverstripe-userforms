<?php

/**
 * @package userforms
 */
class UserForm extends Form {

	/**
	 * @param Controller $controller
	 * @param string $name
	 */
	public function __construct(Controller $controller, $name = 'Form') {

		$this->controller = $controller;
		$this->setRedirectToFormOnValidationError(true);

		parent::__construct(
			$controller,
			$name,
			$this->getFormFields(),
			$this->getFormActions(),
			$this->getRequiredFields()
		);

		if($controller->DisableCsrfSecurityToken) {
			$this->disableSecurityToken();
		}

		$data = Session::get("FormInfo.{$this->FormName()}.data");

		if(is_array($data)) {
			$this->loadDataFrom($data);
		}

		$this->extend('updateForm');
	}

	/**
	 * Used for partial caching in the template.
	 *
	 * @return string
	 */
	public function getLastEdited() {
		return $this->controller->LastEdited;
	}

	/**
	 * @return int
	 */
	public function getDisplayErrorMessagesAtTop() {
		return $this->controller->DisplayErrorMessagesAtTop;
	}

	/**
	 * @return array
	 */
	public function getNumberOfSteps() {
		$steps = new ArrayList();
		$numberOfSteps = $this->controller->Fields()->filter('ClassName', 'EditableFormStep')->Count();

		for($i = 0; $i < $numberOfSteps; $i++) {
			$steps->push($i);
		}

		return $steps;
	}

	/**
	 * Get the form fields for the form on this page. Can modify this FieldSet
	 * by using {@link updateFormFields()} on an {@link Extension} subclass which
	 * is applied to this controller.
	 *
	 * This will be a list of top level composite steps
	 *
	 * @return FieldList
	 */
	public function getFormFields() {
		$fields = new FieldList();
		$emptyStep = null; // Last empty step, which may or may not later have children

		foreach ($this->controller->Fields() as $field) {
			// When we encounter a step, save it
			if ($field instanceof EditableFormStep) {
				$emptyStep = $field->getFormField();
				continue;
			}

			// Ensure that the last field is a step
			if($emptyStep) {
				// When we reach the first non-step field, any empty step will no longer be empty
				$fields->push($emptyStep);
				$emptyStep = null;
				
			} elseif(! $fields->last()) {
				// If no steps have been saved yet, warn
				trigger_error('Missing first step in form', E_USER_WARNING);
				$fields->push(singleton('EditableFormStep')->getFormField());
			}

			$fields->last()->push($field->getFormField());
		}

		$this->extend('updateFormFields', $fields);
		return $fields;
	}

	/**
	 * Generate the form actions for the UserDefinedForm. You 
	 * can manipulate these by using {@link updateFormActions()} on
	 * a decorator.
	 *
	 * @todo Make form actions editable via their own field editor.
	 *
	 * @return FieldList
	 */
	public function getFormActions() {
		$submitText = ($this->controller->SubmitButtonText) ? $this->controller->SubmitButtonText : _t('UserDefinedForm.SUBMITBUTTON', 'Submit');
		$clearText = ($this->controller->ClearButtonText) ? $this->controller->ClearButtonText : _t('UserDefinedForm.CLEARBUTTON', 'Clear');

		$actions = new FieldList(
			new FormAction("process", $submitText)
		);

		if($this->controller->ShowClearButton) {
			$actions->push(new ResetFormAction("clearForm", $clearText));
		}

		$this->extend('updateFormActions', $actions);

		return $actions;
	}

	/**
	 * Get the required form fields for this form.
	 *
	 * @return RequiredFields
	 */
	public function getRequiredFields() {
		// Generate required field validator
		$requiredNames = $this
			->controller
			->Fields()
			->filter('Required', true)
			->column('Name');
		$required = new RequiredFields($requiredNames);
		
		$this->extend('updateRequiredFields', $required);

		return $required;
	}

	/**
	 * Override validation so conditional fields can be validated correctly.
	 *
	 * @return boolean
	 */
	public function validate() {
		$data = $this->getData();

		Session::set("FormInfo.{$this->FormName()}.data", $data);
		Session::clear("FormInfo.{$this->FormName()}.errors");

		foreach ($this->controller->Fields() as $key => $field) {
			$field->validateField($data, $this);
		}

		if(Session::get("FormInfo.{$this->FormName()}.errors")) {
			return false;
		}

		return true;
	}

	/**
	 * Override some we can add UserForm specific attributes to the form.
	 *
	 * @return array
	 */
	public function getAttributes() {
		$attrs = parent::getAttributes();

		$attrs['class'] = $attrs['class'] . ' userform';
		$attrs['data-livevalidation'] = (bool)$this->controller->EnableLiveValidation;
		$attrs['data-toperrors'] = (bool)$this->controller->DisplayErrorMessagesAtTop;
		$attrs['data-hidefieldlabels'] = (bool)$this->controller->HideFieldLabels;

		return $attrs;
	}
}
