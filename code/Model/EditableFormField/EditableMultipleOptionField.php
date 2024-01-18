<?php

namespace SilverStripe\UserForms\Model\EditableFormField;

use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldButtonRow;
use SilverStripe\Forms\GridField\GridFieldConfig;
use SilverStripe\Forms\GridField\GridFieldDeleteAction;
use SilverStripe\Forms\GridField\GridFieldToolbarHeader;
use SilverStripe\Forms\Tab;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\HasManyList;
use SilverStripe\ORM\Map;
use SilverStripe\ORM\SS_List;
use SilverStripe\UserForms\Model\EditableFormField;
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
 * @method HasManyList<EditableOption> Options()
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
        'Options' => EditableOption::class,
    ];

    private static $owns = [
        'Options',
    ];

    private static $cascade_deletes = [
        'Options',
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

            $fields->insertAfter('Main', Tab::create('Options', _t(__CLASS__.'.OPTIONSTAB', 'Options')));
            $fields->addFieldToTab('Root.Options', $optionsGrid);
        });

        $fields = parent::getCMSFields();

        return $fields;
    }

    /**
     * Duplicate a pages content. We need to make sure all the fields attached
     * to that page go with it
     * {@inheritDoc}
     */
    public function duplicate(bool $doWrite = true, array|null $relations = null): static
    {
        $clonedNode = parent::duplicate(true);

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
     * {@link EditableDropdown} or {@link EditableRadioField}
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
