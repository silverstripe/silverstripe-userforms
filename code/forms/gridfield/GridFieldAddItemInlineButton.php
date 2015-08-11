<?php

/**
 * Allows inline adding of classes with a default type
 *
 * Provides an alternative to GridFieldAddNewInlineButton, but allows you to set a classname
 */
class GridFieldAddItemInlineButton extends Object implements GridField_HTMLProvider, GridField_SaveHandler {

	/**
	 * Fragment id
	 *
	 * @var string
	 */
	protected $fragment;

	/**
	 * Additonal CSS classes for the button
	 *
	 * @var string
	 */
	protected $buttonClass = null;

	/**
	 * Button title
	 *
	 * @var string
	 */
	protected $title;

	/**
	 * Class names
	 *
	 * @var array
	 */
	protected $modelClasses = null;

	/**
	 * Extra CSS classes for this row
	 *
	 * @var string
	 */
	protected $extraClass = null;

	/**
	 * @param array $classes Class or list of classes to create.
	 * If you enter more than one class, each click of the "add" button will create one of each
	 * @param string $fragment The fragment to render the button into
	 */
	public function __construct($classes, $fragment = 'buttons-before-left') {
		$this->setClasses($classes);
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
		foreach($this->getClasses() as $class) {
			if(!singleton($class)->canCreate()) {
				return array();
			}
		}

		if(!$editable = $grid->getConfig()->getComponentByType('GridFieldEditableColumns')) {
			throw new Exception('Inline adding requires the editable columns component');
		}

		Requirements::javascript(THIRDPARTY_DIR . '/javascript-templates/tmpl.js');
		GridFieldExtensions::include_requirements();
		Requirements::javascript(USERFORMS_DIR . '/javascript/GridFieldExtensions.js');

		// Build button content
		$data = new ArrayData(array(
			'Title'  => $this->getTitle(),
			'ButtonClass' => $this->getButtonClass(),
			'TemplateNames' => json_encode($this->getRowTemplateNames())
		));

		// Build template body
		$templates = '';
		foreach($this->getClasses() as $class) {
			$templates .= $this->getItemRowTemplateFor($grid, $editable, $class);
		}

		return array(
			$this->getFragment() => $data->renderWith(__CLASS__),
			'after'   => $templates
		);
	}



	/**
	 * Get the template for a given class
	 *
	 * @param GridField $grid
	 * @param GridFieldEditableColumns $editable
	 * @param string $class Name of class
	 * @return type
	 */
	protected function getItemRowTemplateFor(GridField $grid, GridFieldEditableColumns $editable, $class) {
		$columns = new ArrayList();
		$handled = array_keys($editable->getDisplayFields($grid));

		$record = Object::create($class);

		$fields = $editable->getFields($grid, $record);

		foreach($grid->getColumns() as $column) {
			$content = null;
			if(in_array($column, $handled)) {
				$field = $fields->dataFieldByName($column);
				if($field) {
					$field->setName(sprintf(
						'%s[%s][{%%=o.num%%}][%s][%s]', $grid->getName(), __CLASS__, $class, $field->getName()
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
			'RecordClass' => $class,
			'TemplateName' => $this->getRowTemplateNameFor($class)
		));
		return $data->renderWith('GridFieldAddItemTemplate');
	}

	public function handleSave(GridField $grid, DataObjectInterface $record) {
		// Check that this submission relates to this component
		$value = $grid->Value();
		if(empty($value[__CLASS__]) || !is_array($value[__CLASS__])) {
			return;
		}

		// The way that this component works is that only the first component added to a form will
		// be responsible for saving all records for each submission, in order to ensure creation
		// and sort order is maintained
		$addInlineComponents = $grid->getConfig()->getComponentsByType(__CLASS__);
		if($this !== $addInlineComponents->first()) {
			return;
		}

		// Get allowed classes
		$classes = array();
		foreach($addInlineComponents as $component) {
			$classes = array_merge($classes, $component->getClasses());
		}
		$classes = array_filter($classes, function($class) {
			return singleton($class)->canCreate();
		});

		// Get form
		$editable = $grid->getConfig()->getComponentByType('GridFieldEditableColumns');
		$form = $editable->getForm($grid, $record);


		// Process records matching the specified class
		$list  = $grid->getList();
		foreach($value[__CLASS__] as $row) {
			$fields = reset($row);
			$class = key($row);
			if(!in_array($class, $classes)) {
				continue;
			}
			
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
	 * Get the classes of the objects to create
	 *
	 * @return array
	 */
	public function getClasses() {
		return $this->modelClasses;
	}

	/**
	 * Specify the classes to create
	 *
	 * @param array $classes
	 */
	public function setClasses($classes) {
		if(!is_array($classes)) {
			$classes = array($classes);
		}
		$this->modelClasses = $classes;
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
	 * @return $this
	 */
	public function setExtraClass($extraClass) {
		$this->extraClass = $extraClass;
		return $this;
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
	 * Get names of all item templates
	 *
	 * @return array
	 */
	public function getRowTemplateNames() {
		$self = $this;
		return array_map(function($class) use ($self) {
			return $this->getRowTemplateNameFor($class);
		}, $this->getClasses());
	}

	/**
	 * Get the template name for a single class
	 *
	 * @param string $class
	 * @return string
	 */
	public function getRowTemplateNameFor($class) {
		return "ss-gridfield-add-inline-template-{$class}";
	}
}
