<?php

/**
 * Allows inline adding of classes with a default type
 *
 * Provides an alternative to GridFieldAddNewInlineButton, but allows you to set a classname
 */
class GridFieldAddItemInlineButton implements GridField_HTMLProvider, GridField_SaveHandler {

	/**
	 * Fragment id
	 *
	 * @var string
	 */
	protected $fragment;

	/**
	 * Button title
	 *
	 * @var string
	 */
	protected $title;

	/**
	 * Class name
	 *
	 * @var string
	 */
	protected $modelClass = null;

	/**
	 * Extra CSS classes for this row
	 *
	 * @var string
	 */
	protected $extraClass = null;



	/**
	 * @param string $class Name of class to default to
	 * @param string $fragment The fragment to render the button into
	 */
	public function __construct($class, $fragment = 'buttons-before-left') {
		$this->setClass($class);
		$this->setFragment($fragment);
		$this->setTitle(_t('GridFieldExtensions.ADD', 'Add'));
	}

	/**
	 * Gets the fragment name this button is rendered into.
	 *
	 * @return string
	 */
	public function getFragment() {
		return $this->fragment;
	}

	/**
	 * Sets the fragment name this button is rendered into.
	 *
	 * @param string $fragment
	 * @return GridFieldAddNewInlineButton $this
	 */
	public function setFragment($fragment) {
		$this->fragment = $fragment;
		return $this;
	}

	/**
	 * Gets the button title text.
	 *
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * Sets the button title text.
	 *
	 * @param string $title
	 * @return GridFieldAddNewInlineButton $this
	 */
	public function setTitle($title) {
		$this->title = $title;
		return $this;
	}

	public function getHTMLFragments($grid) {
		if($grid->getList() && !singleton($grid->getModelClass())->canCreate()) {
			return array();
		}

		$fragment = $this->getFragment();

		if(!$editable = $grid->getConfig()->getComponentByType('GridFieldEditableColumns')) {
			throw new Exception('Inline adding requires the editable columns component');
		}

		Requirements::javascript(THIRDPARTY_DIR . '/javascript-templates/tmpl.js');
		GridFieldExtensions::include_requirements();
		Requirements::javascript(USERFORMS_DIR . '/javascript/GridFieldAddItemInlineButton.js');

		$data = new ArrayData(array(
			'Title'  => $this->getTitle(),
			'TemplateName' => $this->getRowTemplateName()
		));

		return array(
			$fragment => $data->renderWith(__CLASS__),
			'after'   => $this->getItemRowTemplate($grid, $editable)
		);
	}

	/**
	 * Because getRowTemplate is private
	 *
	 * @param GridField $grid
	 * @param GridFieldEditableColumns $editable
	 * @return type
	 */
	protected function getItemRowTemplate(GridField $grid, GridFieldEditableColumns $editable) {
		$columns = new ArrayList();
		$handled = array_keys($editable->getDisplayFields($grid));

		$record = Object::create($this->getClass());

		$fields = $editable->getFields($grid, $record);

		foreach($grid->getColumns() as $column) {
			$content = null;
			if(in_array($column, $handled)) {
				$field = $fields->dataFieldByName($column);
				if($field) {
					$field->setName(sprintf(
						'%s[%s][%s][{%%=o.num%%}][%s]', $grid->getName(), __CLASS__, $this->getClass(), $field->getName()
					));
				} else {
					$field = $fields->fieldByName($column);
				}
				if($field) {
					$content = $field->Field();
				}
			}

			$attrs = '';

			foreach($grid->getColumnAttributes($record, $column) as $attr => $val) {
				$attrs .= sprintf(' %s="%s"', $attr, Convert::raw2att($val));
			}

			$columns->push(new ArrayData(array(
				'Content'    => $content,
				'Attributes' => $attrs,
				'IsActions'  => $column == 'Actions'
			)));
		}

		$data = new ArrayData(array(
			'Columns' => $columns,
			'ExtraClass' => $this->getExtraClass(),
			'RecordClass' => $this->getClass(),
			'TemplateName' => $this->getRowTemplateName()
		));
		return $data->renderWith(__CLASS__ . '_Row');
	}

	public function handleSave(GridField $grid, DataObjectInterface $record) {
		$list  = $grid->getList();
		$value = $grid->Value();
		$class = $this->getClass();

		if(!isset($value[__CLASS__][$class]) || !is_array($value[__CLASS__][$class])) {
			return;
		}

		$editable = $grid->getConfig()->getComponentByType('GridFieldEditableColumns');
		$form     = $editable->getForm($grid, $record);

		if(!singleton($class)->canCreate()) {
			return;
		}

		// Process records matching the specified class
		foreach($value[__CLASS__][$class] as $fields) {
			$item  = $class::create();
			$extra = array();

			$form->loadDataFrom($fields, Form::MERGE_CLEAR_MISSING);
			$form->saveInto($item);

			if($list instanceof ManyManyList) {
				$extra = array_intersect_key($form->getData(), (array) $list->getExtraFields());
			}

			$item->write();
			$list->add($item, $extra);
		}
	}

	/**
	 * Get the class of the object to create
	 *
	 * @return string
	 */
	public function getClass() {
		return $this->modelClass;
	}

	/**
	 * Specify the class to create
	 *
	 * @param string $class
	 */
	public function setClass($class) {
		$this->modelClass = $class;
	}

	/**
	 * Get extra CSS classes for this row
	 *
	 * @return type
	 */
	public function getExtraClass() {
		return $this->extraClass;
	}

	/**
	 * Sets extra CSS classes for this row
	 *
	 * @param string $extraClass
	 */
	public function setExtraClass($extraClass) {
		$this->extraClass = $extraClass;
	}

	/**
	 * Get name of item template
	 *
	 * @return string
	 */
	public function getRowTemplateName() {
		return 'ss-gridfield-add-inline-template-' . $this->getClass();
	}
}
