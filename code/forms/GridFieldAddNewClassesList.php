<?php

/**
 * A dropdown and button which allows objects to be created that was selected from the dropdown
 */
class UserFormAddNewClassesList extends Object implements GridField_HTMLProvider, GridField_ActionProvider {

	/**
	 * Name of fragment to insert into
	 *
	 * @var string
	 */
	protected $targetFragment;

	/**
	 * Button title
	 *
	 * @var string
	 */
	protected $buttonName;

	/**
	 * Additonal CSS classes for the button
	 *
	 * @var string
	 */
	protected $buttonClass = null;

	/**
	 * default value for the dropdown
	 */
	protected $defaultClass;

	/**
	 * @param array $default Class to be selected by default.
	 * @param string $targetFragment The fragment to render the button into
	 */
	public function __construct($default = null, $targetFragment = 'buttons-before-left') {
		$this->setFragment($targetFragment);
		$this->setDefaultClass($default);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getHTMLFragments($grid) {
		$classes = $this->getFieldClasses();

		if(!count($classes)) {
			return array();
		}

		$field = new DropdownField(sprintf('%s[ClassName]', __CLASS__), '', $classes, $this->defaultClass);
		$field->addExtraClass('no-change-track');

		$formAction = new GridField_FormAction(
			$grid,
			$this->getAction(),
			$this->getButtonName(),
			$this->getAction(),
			array()
		);
		$formAction->setAttribute('data-icon', 'add');

		if($buttonClass = $this->getButtonClass()) {
			$formAction->addExtraClass($buttonClass);
		}

		$data = new ArrayData(array(
			'FormAction' => $formAction,
			'ClassField' => $field
		));

		return array(
			$this->getFragment() => $data->renderWith('UserFormAddNewClassesList')
		);
	}

	/**
	 * Get extra button class
	 *
	 * @return string
	 */
	public function getButtonClass() {
		return $this->buttonClass;
	}

	/**
	 * Sets extra CSS classes for this button
	 *
	 * @param string $buttonClass
	 * @return $this
	 */
	public function setButtonClass($buttonClass) {
		$this->buttonClass = $buttonClass;
		return $this;
	}

	/**
	 * Change the button name
	 *
	 * @param string $name
	 * @return $this
	 */
	public function setButtonName($name) {
		$this->buttonName = $name;
		return $this;
	}

	/**
	 * Get the button name
	 *
	 * @return string
	 */
	public function getButtonName() {
		return $this->buttonName;
	}

	/**
	 * Gets the fragment name this button is rendered into.
	 *
	 * @return string
	 */
	public function getFragment() {
		return $this->targetFragment;
	}

	/**
	 * Sets the fragment name this button is rendered into.
	 *
	 * @param string $fragment
	 * @return GridFieldAddNewInlineButton $this
	 */
	public function setFragment($fragment) {
		$this->targetFragment = $fragment;
		return $this;
	}

	/**
	 * Handles adding a new instance of a selected class.
	 *
	 * @param GridField $grid
	 * @param Array $data from request
	 * @return null
	 */
	public function handleAdd($grid, $data) {
		$class = $this->getSelectedClass($data);

		if(!$class) {
			throw new SS_HTTPResponse_Exception(400);
		}

		// Add item to gridfield
		$list = $grid->getList();
		$item = $class::create();
		$item->write();
		$list->add($item);

		// Should trigger a simple reload
		return null;
	}

	/**
	 * Gets the default class that is selected automatically.
	 *
	 * @return string
	 */
	public function getDefaultClass() {
		return $this->defaultClass;
	}

	/**
	 * Sets the default class that is selected automatically.
	 *
	 * @param string $default the class name to use as default
	 * @return UserFormAddNewClassesList $this
	 */
	public function setDefaultClass($default) {
		$this->defaultClass = $default;
		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getActions($gridField) {
		return array(
			$this->getAction()
		);
	}

	/**
	 * Get the action suburl for this component
	 *
	 * @return string
	 */
	protected function getAction() {
		return 'add-classes-list';
	}

	/**
	 * {@inheritDoc}
	 */
	public function handleAction(GridField $gridField, $actionName, $arguments, $data) {
		switch(strtolower($actionName)) {
			case $this->getAction():
				return $this->handleAdd($gridField, $data);
			default:
				return null;
		}
	}

	/**
	 * Get the list of classes that can be selected and created
	 *
	 * @return array
	 */
	public function getFieldClasses() {
		return singleton('EditableFormField')->getEditableFieldClasses();
	}

	/**
	 * Gets the selected value from the request data array
	 *
	 * @param array $data from request
	 * @return string|null;
	 */
	public function getSelectedClass($data = null) {
		$classes = $this->getFieldClasses();

		$class = null;
		if(is_array($data) && isset($data[__CLASS__]['ClassName'])) {
			$class = $data[__CLASS__]['ClassName'];
		}

		if($class && !array_key_exists($class, $classes)) {
			throw new SS_HTTPResponse_Exception(400);
		}

		return $class;
	}
}