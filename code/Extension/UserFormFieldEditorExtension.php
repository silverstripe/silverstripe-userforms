<?php

namespace SilverStripe\UserForms\Extension;

use SilverStripe\Admin\AdminRootController;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\GridField\GridFieldPaginator;
use SilverStripe\Forms\Tab;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldButtonRow;
use SilverStripe\Forms\GridField\GridFieldConfig;
use SilverStripe\Forms\GridField\GridFieldEditButton;
use SilverStripe\Forms\GridField\GridFieldDeleteAction;
use SilverStripe\Forms\GridField\GridFieldDetailForm;
use SilverStripe\Forms\GridField\GridFieldToolbarHeader;
use SilverStripe\ORM\DataExtension;
use SilverStripe\ORM\DataList;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\HasManyList;
use SilverStripe\UserForms\Form\GridFieldAddClassesButton;
use SilverStripe\UserForms\Model\EditableFormField;
use SilverStripe\UserForms\Model\EditableFormField\EditableFieldGroup;
use SilverStripe\UserForms\Model\EditableFormField\EditableFieldGroupEnd;
use SilverStripe\UserForms\Model\EditableFormField\EditableFileField;
use SilverStripe\UserForms\Model\EditableFormField\EditableFormStep;
use SilverStripe\UserForms\Model\EditableFormField\EditableTextField;
use SilverStripe\Versioned\Versioned;
use SilverStripe\View\Requirements;
use Symbiote\GridFieldExtensions\GridFieldEditableColumns;
use Symbiote\GridFieldExtensions\GridFieldOrderableRows;

/**
 * @method HasManyList<EditableFormField> Fields()
 */
class UserFormFieldEditorExtension extends DataExtension
{
    /**
     * @var array
     */
    private static $has_many = array(
        'Fields' => EditableFormField::class
    );

    private static $owns = [
        'Fields'
    ];

    private static $cascade_deletes = [
        'Fields'
    ];

    /**
     * Adds the field editor to the page.
     *
     * @return FieldList
     */
    public function updateCMSFields(FieldList $fields)
    {
        $fieldEditor = $this->getFieldEditorGrid();

        $fields->insertAfter('Main', new Tab('FormFields', _t(__CLASS__.'.FORMFIELDS', 'Form Fields')));
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
        Requirements::javascript('silverstripe/admin:client/dist/js/vendor.js');
        Requirements::javascript('silverstripe/admin:client/dist/js/bundle.js');
        Requirements::javascript('silverstripe/userforms:client/dist/js/userforms-cms.js');

        $fields = $this->owner->Fields();

        $this->createInitialFormStep(true);

        $editableColumns = new GridFieldEditableColumns();
        $fieldClasses = singleton(EditableFormField::class)->getEditableFieldClasses();
        $editableColumns->setDisplayFields([
            'ClassName' => function ($record, $column, $grid) use ($fieldClasses) {
                if ($record instanceof EditableFormField) {
                    $field = $record->getInlineClassnameField($column, $fieldClasses);
                    if ($record instanceof EditableFileField) {
                        $field->setAttribute('data-folderconfirmed', $record->FolderConfirmed ? 1 : 0);
                    }
                    return $field;
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
                    ->setButtonClass('btn-primary'),
                (new GridFieldAddClassesButton(EditableFormStep::class))
                    ->setButtonName(_t(__CLASS__.'.ADD_PAGE_BREAK', 'Add Page Break'))
                    ->setButtonClass('btn-secondary'),
                (new GridFieldAddClassesButton([EditableFieldGroup::class, EditableFieldGroupEnd::class]))
                    ->setButtonName(_t(__CLASS__.'.ADD_FIELD_GROUP', 'Add Field Group'))
                    ->setButtonClass('btn-secondary'),
                $editButton = new GridFieldEditButton(),
                new GridFieldDeleteAction(),
                new GridFieldToolbarHeader(),
                new GridFieldOrderableRows('Sort'),
                new GridFieldDetailForm(),
                // Betterbuttons prev and next is enabled by adding a GridFieldPaginator component
                new GridFieldPaginator(999)
            );

        $editButton->removeExtraClass('grid-field__icon-action--hidden-on-hover');

        $fieldEditor = GridField::create(
            'Fields',
            '',
            $fields,
            $config
        )
            ->addExtraClass('uf-field-editor');

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
     * Remove any orphaned child records on publish
     */
    public function onAfterPublish()
    {
        // store IDs of fields we've published
        $seenIDs = [];
        foreach ($this->owner->Fields() as $field) {
            // store any IDs of fields we publish so we don't unpublish them
            $seenIDs[] = $field->ID;
            $field->publishRecursive();
            $field->destroy();
        }

        // fetch any orphaned live records
        $live = Versioned::get_by_stage(EditableFormField::class, Versioned::LIVE)
            ->filter([
                'ParentID' => $this->owner->ID,
                'ParentClass' => get_class($this->owner),
            ]);

        if (!empty($seenIDs)) {
            $live = $live->exclude([
                'ID' => $seenIDs,
            ]);
        }

        // delete orphaned records
        foreach ($live as $field) {
            $field->deleteFromStage(Versioned::LIVE);
            $field->destroy();
        }
    }

    /**
     * Remove all fields from the live stage when unpublishing the page
     */
    public function onAfterUnpublish()
    {
        foreach ($this->owner->Fields() as $field) {
            $field->deleteFromStage(Versioned::LIVE);
        }
    }

    /**
     * When duplicating a UserDefinedForm, duplicate all of its fields and display rules
     *
     * @see DataObject::duplicate
     * @param DataObject $oldPage
     * @param bool $doWrite
     * @param string $manyMany
     * @return DataObject
     */
    public function onAfterDuplicate($oldPage, $doWrite, $manyMany)
    {
        // List of EditableFieldGroups, where the key of the array is the ID of the old end group
        $fieldGroups = [];
        foreach ($oldPage->Fields() as $field) {
            /** @var EditableFormField $newField */
            $newField = $field->duplicate(false);
            $newField->ParentID = $this->owner->ID;
            $newField->ParentClass = $this->owner->ClassName;
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
    }

    /**
     * Checks child fields to see if any are modified in draft as well. The owner of this extension will still
     * use the Versioned method to determine its own status.
     *
     * @see Versioned::isModifiedOnDraft
     *
     * @return boolean|null
     */
    public function isModifiedOnDraft()
    {
        foreach ($this->owner->Fields() as $field) {
            if ($field->isModifiedOnDraft()) {
                return true;
            }
        }
    }

    /**
     * @see Versioned::doRevertToLive
     */
    public function onAfterRevertToLive()
    {
        foreach ($this->owner->Fields() as $field) {
            $field->copyVersionToStage(Versioned::LIVE, Versioned::DRAFT);
            $field->writeWithoutVersion();
        }
    }
}
