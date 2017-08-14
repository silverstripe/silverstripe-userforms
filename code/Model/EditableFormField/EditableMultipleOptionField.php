<?php

namespace SilverStripe\UserForms\Model\EditableFormField;

use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldButtonRow;
use SilverStripe\Forms\GridField\GridFieldConfig;
use SilverStripe\Forms\GridField\GridFieldDeleteAction;
use SilverStripe\Forms\GridField\GridFieldToolbarHeader;
use SilverStripe\Forms\Tab;
use SilverStripe\ORM\Map;
use SilverStripe\UserForms\Model\EditableFormField;
use SilverStripe\UserForms\Model\EditableFormField\EditableOption;
use SilverStripe\Versioned\Versioned;
use Symbiote\GridFieldExtensions\GridFieldAddNewInlineButton;
use Symbiote\GridFieldExtensions\GridFieldEditableColumns;
use Symbiote\GridFieldExtensions\GridFieldOrderableRows;
use Symbiote\GridFieldExtensions\GridFieldTitleHeader;

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

    private static $has_many = [
        'Options' => EditableOption::class
    ];

    private static $table_name = 'EditableMultipleOptionField';

    /**
     * @return FieldList
     */
    public function getCMSFields()
    {
        $this->beforeUpdateCMSFields(function ($fields) {
            $editableColumns = new GridFieldEditableColumns();
            $editableColumns->setDisplayFields([
                'Title' => [
                    'title' => _t(__CLASS__.'.TITLE', 'Title'),
                    'callback' => function ($record, $column, $grid) {
                        return TextField::create($column);
                    }
                ],
                'Value' => [
                    'title' => _t(__CLASS__.'.VALUE', 'Value'),
                    'callback' => function ($record, $column, $grid) {
                        return TextField::create($column);
                    }
                ],
                'Default' => [
                    'title' => _t(__CLASS__.'.DEFAULT', 'Selected by default?'),
                    'callback' => function ($record, $column, $grid) {
                        return CheckboxField::create($column);
                    }
                ]
            ]);

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
                _t('SilverStripe\\UserForms\\Model\\EditableFormField.CUSTOMOPTIONS', 'Options'),
                $this->Options(),
                $optionsConfig
            );

            $fields->insertAfter(Tab::create('Options', _t(__CLASS__.'.OPTIONSTAB', 'Options')), 'Main');
            $fields->addFieldToTab('Root.Options', $optionsGrid);
        });

        $fields = parent::getCMSFields();

        return $fields;
    }

    /**
     * Publishing Versioning support.
     *
     * When publishing it needs to handle copying across / publishing
     * each of the individual field options
     *
     * @param string $fromStage
     * @param string $toStage
     * @param bool $createNewVersion
     */
    public function copyVersionToStage($fromStage, $toStage, $createNewVersion = false)
    {
        parent::copyVersionToStage($fromStage, $toStage, $createNewVersion);
        $this->publishOptions($fromStage, $toStage, $createNewVersion);
    }


    /**
     * Publish list options
     *
     * @param string $fromStage
     * @param string $toStage
     * @param bool $createNewVersion
     */
    protected function publishOptions($fromStage, $toStage, $createNewVersion)
    {
        $seenIDs = [];

        // Publish all options
        foreach ($this->Options() as $option) {
            $seenIDs[] = $option->ID;
            $option->copyVersionToStage($fromStage, $toStage, $createNewVersion);
        }

        // remove any orphans from the "fromStage"
        $options = Versioned::get_by_stage(EditableOption::class, $toStage)
            ->filter('ParentID', $this->ID);

        if (!empty($seenIDs)) {
            $options = $options->exclude('ID', $seenIDs);
        }

        foreach ($options as $rule) {
            $rule->deleteFromStage($toStage);
        }
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
        $options = Versioned::get_by_stage(EditableOption::class, $stage)
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
    public function duplicate($doWrite = true, $manyMany = 'many_many')
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
        if ($optionMap instanceof Map) {
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