<?php

/**
 * A button which allows objects to be created with a specified classname(s)
 */
class GridFieldAddClassesButton extends SS_Object implements GridField_HTMLProvider, GridField_ActionProvider
{

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
     * Class names
     *
     * @var array
     */
    protected $modelClasses = null;

    /**
     * @param array $classes Class or list of classes to create.
     * If you enter more than one class, each click of the "add" button will create one of each
     * @param string $targetFragment The fragment to render the button into
     */
    public function __construct($classes, $targetFragment = 'buttons-before-left')
    {
        parent::__construct();
        $this->setClasses($classes);
        $this->setFragment($targetFragment);
    }

    /**
     * Change the button name
     *
     * @param string $name
     * @return $this
     */
    public function setButtonName($name)
    {
        $this->buttonName = $name;
        return $this;
    }

    /**
     * Get the button name
     *
     * @return string
     */
    public function getButtonName()
    {
        return $this->buttonName;
    }

    /**
     * Gets the fragment name this button is rendered into.
     *
     * @return string
     */
    public function getFragment()
    {
        return $this->targetFragment;
    }

    /**
     * Sets the fragment name this button is rendered into.
     *
     * @param string $fragment
     * @return GridFieldAddNewInlineButton $this
     */
    public function setFragment($fragment)
    {
        $this->targetFragment = $fragment;
        return $this;
    }

    /**
     * Get extra button class
     *
     * @return string
     */
    public function getButtonClass()
    {
        return $this->buttonClass;
    }

    /**
     * Sets extra CSS classes for this button
     *
     * @param string $buttonClass
     * @return $this
     */
    public function setButtonClass($buttonClass)
    {
        $this->buttonClass = $buttonClass;
        return $this;
    }


    /**
     * Get the classes of the objects to create
     *
     * @return array
     */
    public function getClasses()
    {
        return $this->modelClasses;
    }

    /**
     * Gets the list of classes which can be created, with checks for permissions.
     * Will fallback to the default model class for the given DataGrid
     *
     * @param DataGrid $grid
     * @return array
     */
    public function getClassesCreate($grid)
    {
        // Get explicit or fallback class list
        $classes = $this->getClasses();
        if (empty($classes) && $grid) {
            $classes = array($grid->getModelClass());
        }

        // Filter out classes without permission
        return array_filter($classes, function ($class) {
            return singleton($class)->canCreate();
        });
    }

    /**
     * Specify the classes to create
     *
     * @param array $classes
     */
    public function setClasses($classes)
    {
        if (!is_array($classes)) {
            $classes = $classes ? array($classes) : array();
        }
        $this->modelClasses = $classes;
    }

    public function getHTMLFragments($grid)
    {
        // Check create permission
        $singleton = singleton($grid->getModelClass());
        if (!$singleton->canCreate()) {
            return array();
        }

        // Get button name
        $buttonName = $this->getButtonName();
        if (!$buttonName) {
            // provide a default button name, can be changed by calling {@link setButtonName()} on this component
            $objectName = $singleton->i18n_singular_name();
            $buttonName = _t('GridField.Add', 'Add {name}', array('name' => $objectName));
        }

        $addAction = new GridField_FormAction(
            $grid,
            $this->getAction(),
            $buttonName,
            $this->getAction(),
            array()
        );
        $addAction->setAttribute('data-icon', 'add');

        if ($this->getButtonClass()) {
            $addAction->addExtraClass($this->getButtonClass());
        }

        return array(
            $this->targetFragment => $addAction->forTemplate()
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getActions($gridField)
    {
        return array(
            $this->getAction()
        );
    }

    /**
     * Get the action suburl for this component
     *
     * @return string
     */
    protected function getAction()
    {
        return 'add-classes-' . strtolower(implode('-', $this->getClasses()));
    }

    public function handleAction(GridField $gridField, $actionName, $arguments, $data)
    {
        switch (strtolower($actionName)) {
            case $this->getAction():
                return $this->handleAdd($gridField);
            default:
                return null;
        }
    }

    /**
     * Handles adding a new instance of a selected class.
     *
     * @param GridField $grid
     * @return null
     */
    public function handleAdd($grid)
    {
        $classes = $this->getClassesCreate($grid);
        if (empty($classes)) {
            throw new SS_HTTPResponse_Exception(400);
        }

        // Add item to gridfield
        $list = $grid->getList();
        foreach ($classes as $class) {
            $item = $class::create();
            $item->write();
            $list->add($item);
        }

        // Should trigger a simple reload
        return null;
    }
}
