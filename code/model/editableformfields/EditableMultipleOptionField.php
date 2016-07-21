<?php

/**
 * Base class for multiple option fields such as {@link EditableDropdownField}
 * and radio sets.
 *
 * Implemented as a class but should be viewed as abstract, you should
 * instantiate a subclass such as {@link EditableDropdownField}
 *
 * @see EditableCheckboxGroupField
 * @see EditableDropdownField
 *
 * @package userforms
 */

class EditableMultipleOptionField extends EditableFormField
{

    /**
     * Define this field as abstract (not inherited)
     *
     * @config
     * @var bool
     */
    private static $abstract = true;

    private static $has_many = array(
        "Options" => "EditableOption"
    );

    /**
     * @return FieldList
     */
    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $editableColumns = new GridFieldEditableColumns();
        $editableColumns->setDisplayFields(array(
            'Title' => array(
                'title' => _t('EditableMultipleOptionField.TITLE', 'Title'),
                'callback' => function ($record, $column, $grid) {
                    return TextField::create($column);
                }
            ),
            'Value' => array(
                'title' => _t('EditableMultipleOptionField.VALUE', 'Value'),
                'callback' => function ($record, $column, $grid) {
                    return TextField::create($column);
                }
            ),
            'Default' => array(
                'title' => _t('EditableMultipleOptionField.DEFAULT', 'Selected by default?'),
                'callback' => function ($record, $column, $grid) {
                    return CheckboxField::create($column);
                }
            )
        ));

        $optionsConfig = GridFieldConfig::create()
            ->addComponents(
                new GridFieldToolbarHeader(),
                new GridFieldTitleHeader(),
                new GridFieldOrderableRows('Sort'),
                $editableColumns,
                new GridFieldButtonRow(),
                new GridFieldAddNewInlineButton(),
                new GridFieldDeleteAction()
            );

        $optionsGrid = GridField::create(
            'Options',
            _t('EditableFormField.CUSTOMOPTIONS', 'Options'),
            $this->Options(),
            $optionsConfig
        );

        $fields->insertAfter(new Tab('Options', _t('EditableMultipleOptionField.OPTIONSTAB', 'Options')), 'Main');
        $fields->addFieldToTab('Root.Options', $optionsGrid);

        return $fields;
    }

    /**
     * Publishing Versioning support.
     *
     * When publishing it needs to handle copying across / publishing
     * each of the individual field options
     *
     * @return void
     */
    public function doPublish($fromStage, $toStage, $createNewVersion = false)
    {
        $live = Versioned::get_by_stage("EditableOption", "Live", "\"EditableOption\".\"ParentID\" = $this->ID");

        if ($live) {
            foreach ($live as $option) {
                $option->delete();
            }
        }

        if ($this->Options()) {
            foreach ($this->Options() as $option) {
                $option->publish($fromStage, $toStage, $createNewVersion);
            }
        }

        parent::doPublish($fromStage, $toStage, $createNewVersion);
    }

    /**
     * Unpublishing Versioning support
     *
     * When unpublishing the field it has to remove all options attached
     *
     * @return void
     */
    public function doDeleteFromStage($stage)
    {
        // Remove options
        $options = Versioned::get_by_stage('EditableOption', $stage)
            ->filter('ParentID', $this->ID);
        foreach ($options as $option) {
            $option->deleteFromStage($stage);
        }

        parent::doDeleteFromStage($stage);
    }

    /**
     * Deletes all the options attached to this field before deleting the
     * field. Keeps stray options from floating around
     *
     * @return void
     */
    public function delete()
    {
        $options = $this->Options();

        if ($options) {
            foreach ($options as $option) {
                $option->delete();
            }
        }

        parent::delete();
    }

    /**
     * Duplicate a pages content. We need to make sure all the fields attached
     * to that page go with it
     *
     * @return DataObject
     */
    public function duplicate($doWrite = true)
    {
        $clonedNode = parent::duplicate();

        foreach ($this->Options() as $field) {
            $newField = $field->duplicate(false);
            $newField->ParentID = $clonedNode->ID;
            $newField->Version = 0;
            $newField->write();
        }

        return $clonedNode;
    }

    /**
     * Return whether or not this field has addable options such as a
     * {@link EditableDropdownField} or {@link EditableRadioField}
     *
     * @return bool
     */
    public function getHasAddableOptions()
    {
        return true;
    }

    /**
     * Gets map of field options suitable for use in a form
     *
     * @return array
     */
    protected function getOptionsMap()
    {
        $optionSet = $this->Options();
        $optionMap = $optionSet->map('Value', 'Title');
        if ($optionMap instanceof SS_Map) {
            return $optionMap->toArray();
        }
        return $optionMap;
    }

    /**
     * Returns all default options
     *
     * @return SS_List
     */
    protected function getDefaultOptions()
    {
        return $this->Options()->filter('Default', 1);
    }
}
