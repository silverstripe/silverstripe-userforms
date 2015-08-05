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
	 * Get the form steps.
	 *
	 * @return ArrayList
	 */
	public function getFormSteps() {
		$steps = new ArrayList();

		foreach ($this->controller->Fields()->filter('ClassName', 'EditableFormStep') as $step) {
			$steps->push(array(
				'Title' => $step->Title,
				'Fields' => $this->getFormFields($step)
			));
		}

		return $steps;
	}

	/**
	 * Get the form fields for the form on this page. Can modify this FieldSet
	 * by using {@link updateFormFields()} on an {@link Extension} subclass which
	 * is applied to this controller.
	 *
	 * @param EditableFormStep $parent
	 *
	 * @return FieldList
	 */
	public function getFormFields($parent = null) {
		if(!$parent) {
			$parent = $this->controller;
		}

		$fields = new FieldList();

		foreach($parent->Fields() as $editableField) {
			// get the raw form field from the editable version
			$field = $editableField->getFormField();

			if(!$field) continue;

			// set the error / formatting messages
			$field->setCustomValidationMessage($editableField->getErrorMessage());

			// set the right title on this field
			if($right = $editableField->RightTitle) {
				// Since this field expects raw html, safely escape the user data prior
				$field->setRightTitle(Convert::raw2xml($right));
			}

			// if this field is required add some
			if($editableField->Required) {
				$field->addExtraClass('requiredField');

				if($identifier = UserDefinedForm::config()->required_identifier) {

					$title = $field->Title() ." <span class='required-identifier'>". $identifier . "</span>";
					$field->setTitle($title);
				}
			}
			// if this field has an extra class
			if($extraClass = $editableField->ExtraClass) {
				$field->addExtraClass(Convert::raw2att($extraClass));
			}

			// set the values passed by the url to the field
			$request = $this->controller->getRequest();
			if($value = $request->getVar($field->getName())) {
				$field->setValue($value);
			}

			$fields->push($field);
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
}
