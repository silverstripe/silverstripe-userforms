<?php

namespace SilverStripe\UserForms\Extension;

use SilverStripe\Core\Manifest\ModuleLoader;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Tab;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldButtonRow;
use SilverStripe\Forms\GridField\GridFieldConfig;
use SilverStripe\Forms\GridField\GridFieldEditButton;
use SilverStripe\Forms\GridField\GridFieldDeleteAction;
use SilverStripe\Forms\GridField\GridFieldDetailForm;
use SilverStripe\Forms\GridField\GridFieldToolbarHeader;
use SilverStripe\ORM\DataExtension;
use SilverStripe\UserForms\Form\GridFieldAddClassesButton;
use SilverStripe\UserForms\Model\EditableFormField\EditableFieldGroup;
use SilverStripe\UserForms\Model\EditableFormField\EditableFieldGroupEnd;
use SilverStripe\UserForms\Model\EditableFormField;
use SilverStripe\UserForms\Model\EditableFormField\EditableFormStep;
use SilverStripe\UserForms\Model\EditableFormField\EditableTextField;
use SilverStripe\Versioned\Versioned;
use SilverStripe\View\Requirements;
use Symbiote\GridFieldExtensions\GridFieldEditableColumns;
use Symbiote\GridFieldExtensions\GridFieldOrderableRows;

/**
 * @package userforms
 */
class UserFormFieldEditorExtension extends DataExtension
{
    /**
     * @var array
     */
    private static $has_many = array(
        'Fields' => EditableFormField::class
    );

    /**
     * Adds the field editor to the page.
     *
     * @return FieldList
     */
    public function updateCMSFields(FieldList $fields)
    {
        $fieldEditor = $this->getFieldEditorGrid();

        $fields->insertAfter(new Tab('FormFields', _t(__CLASS__.'.FORMFIELDS', 'Form Fields')), 'Main');
        $fields->addFieldToTab('Root.FormFields', $fieldEditor);

        return $fields;
    }

    /**
     * Gets the field editor, for adding and removing EditableFormFields.
     *
     * @return GridField
     */
    public function getFieldEditorGrid()
    {
        $module = ModuleLoader::getModule('silverstripe/userforms');
        Requirements::javascript($module->getRelativeResourcePath('javascript/FieldEditor.js'));

        $fields = $this->owner->Fields();

        $this->createInitialFormStep(true);

        $editableColumns = new GridFieldEditableColumns();
        $fieldClasses = singleton(EditableFormField::class)->getEditableFieldClasses();
        $editableColumns->setDisplayFields([
            'ClassName' => function ($record, $column, $grid) use ($fieldClasses) {
                if ($record instanceof EditableFormField) {
                    return $record->getInlineClassnameField($column, $fieldClasses);
                }
            },
            'Title' => function ($record, $column, $grid) {
                if ($record instanceof EditableFormField) {
                    return $record->getInlineTitleField($column);
                }
            }
        ]);

        $config = GridFieldConfig::create()
            ->addComponents(
                $editableColumns,
                new GridFieldButtonRow(),
                (new GridFieldAddClassesButton(EditableTextField::class))
                    ->setButtonName(_t(__CLASS__.'.ADD_FIELD', 'Add Field'))
                    ->setButtonClass('ss-ui-action-constructive'),
                (new GridFieldAddClassesButton(EditableFormStep::class))
                    ->setButtonName(_t(__CLASS__.'.ADD_PAGE_BREAK', 'Add Page Break')),
                (new GridFieldAddClassesButton([EditableFieldGroup::class, EditableFieldGroupEnd::class]))
                    ->setButtonName(_t(__CLASS__.'.ADD_FIELD_GROUP', 'Add Field Group')),
                new GridFieldEditButton(),
                new GridFieldDeleteAction(),
                new GridFieldToolbarHeader(),
                new GridFieldOrderableRows('Sort'),
                new GridFieldDetailForm()
            );

        $fieldEditor = GridField::create(
            'Fields',
            _t('SilverStripe\\UserForms\\Model\\UserDefinedForm.FIELDS', 'Fields'),
            $fields,
            $config
        )->addExtraClass('uf-field-editor');

        return $fieldEditor;
    }

    /**
     * A UserForm must have at least one step.
     * If no steps exist, create an initial step, and put all fields inside it.
     *
     * @param bool $force
     * @return void
     */
    public function createInitialFormStep($force = false)
    {
        // Only invoke once saved
        if (!$this->owner->exists()) {
            return;
        }

        // Check if first field is a step
        $fields = $this->owner->Fields();
        $firstField = $fields->first();
        if ($firstField instanceof EditableFormStep) {
            return;
        }

        // Don't create steps on write if there are no formfields, as this
        // can create duplicate first steps during publish of new records
        if (!$force && !$firstField) {
            return;
        }

        // Re-apply sort to each field starting at 2
        $next = 2;
        foreach ($fields as $field) {
            $field->Sort = $next++;
            $field->write();
        }

        // Add step
        $step = EditableFormStep::create();
        $step->Title = _t('SilverStripe\\UserForms\\Model\\EditableFormField\\EditableFormStep.TITLE_FIRST', 'First Page');
        $step->Sort = 1;
        $step->write();
        $fields->add($step);
    }

    /**
     * Ensure that at least one page exists at the start
     */
    public function onAfterWrite()
    {
        $this->createInitialFormStep();
    }

    /**
     * @see SiteTree::doPublish
     * @param Page $original
     *
     * @return void
     */
    public function onAfterPublish($original)
    {
        if (!$original) {
            return;
        }

        // store IDs of fields we've published
        $seenIDs = array();

        foreach ($this->owner->Fields() as $field) {
            // store any IDs of fields we publish so we don't unpublish them
            $seenIDs[] = $field->ID;
            $field->publishRecursive();
            $field->destroy();
        }

        // fetch any orphaned live records
        $live = Versioned::get_by_stage(EditableFormField::class, "Live")
            ->filter([
                'ParentID' => $original->ID,
            ]);

        if (!empty($seenIDs)) {
            $live = $live->exclude([
                'ID' => $seenIDs,
            ]);
        }

        // delete orphaned records
        foreach ($live as $field) {
            $field->doDeleteFromStage('Live');
            $field->destroy();
        }
    }

    /**
     * @see SiteTree::doUnpublish
     * @param Page $page
     *
     * @return void
     */
    public function onAfterUnpublish($page)
    {
        foreach ($page->Fields() as $field) {
            $field->doDeleteFromStage('Live');
        }
    }

    /**
     * @see SiteTree::duplicate
     * @param DataObject $newPage
     *
     * @return DataObject
     */
    public function onAfterDuplicate($newPage)
    {
        // List of EditableFieldGroups, where the
        // key of the array is the ID of the old end group
        $fieldGroups = array();
        foreach ($this->owner->Fields() as $field) {
            $newField = $field->duplicate(false);
            $newField->ParentID = $newPage->ID;
            $newField->ParentClass = $newPage->ClassName;
            $newField->Version = 0;
            $newField->write();

            // If we encounter a group start, record it for later use
            if ($field instanceof EditableFieldGroup) {
                $fieldGroups[$field->EndID] = $newField;
            }

            // If we encounter an end group, link it back to the group start
            if ($field instanceof EditableFieldGroupEnd && isset($fieldGroups[$field->ID])) {
                $groupStart = $fieldGroups[$field->ID];
                $groupStart->EndID = $newField->ID;
                $groupStart->write();
            }

            foreach ($field->DisplayRules() as $customRule) {
                $newRule = $customRule->duplicate(false);
                $newRule->ParentID = $newField->ID;
                $newRule->Version = 0;
                $newRule->write();
            }
        }

        return $newPage;
    }

    /**
     * @see SiteTree::getIsModifiedOnStage
     * @param boolean $isModified
     *
     * @return boolean
     */
    public function getIsModifiedOnStage($isModified)
    {
        if (!$isModified) {
            foreach ($this->owner->Fields() as $field) {
                if ($field->getIsModifiedOnStage()) {
                    $isModified = true;
                    break;
                }
            }
        }

        return $isModified;
    }

    /**
     * @see SiteTree::doRevertToLive
     * @param Page $page
     *
     * @return void
     */
    public function onAfterRevertToLive($page)
    {
        foreach ($page->Fields() as $field) {
            $field->copyVersionToStage('Live', 'Stage', false);
            $field->writeWithoutVersion();
        }
    }
}
