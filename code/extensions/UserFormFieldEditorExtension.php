<?php

/**
 * @package userforms
 */
class UserFormFieldEditorExtension extends DataExtension {

	/**
	 * @var array
	 */
	private static $has_many = array(
		'Fields' => 'EditableFormField'
	);

	/**
	 * Adds the field editor to the page.
	 *
	 * @return FieldList
	 */
	public function updateCMSFields(FieldList $fields) {
		$fieldEditor = $this->getFieldEditorGrid();

		$fields->insertAfter(new Tab('FormFields', _t('UserFormFieldEditorExtension.FORMFIELDS', 'Form Fields')), 'Main');
		$fields->addFieldToTab('Root.FormFields', $fieldEditor);

		return $fields;
	}

	/**
	 * Gets the field editor, for adding and removing EditableFormFields.
	 *
	 * @return GridField
	 */
	public function getFieldEditorGrid() {
		$fields = $this->owner->Fields();

		$this->createInitialFormStep(true);

		$editableColumns = new GridFieldEditableColumns();
		$editableColumns->setDisplayFields(array(
			'ClassName' => function($record, $column, $grid) {
				if($record instanceof EditableFormStep) {
					return new LabelField($column, "Page Break");
				} else {
					return DropdownField::create($column, '', $this->getEditableFieldClasses());
				}
			},
			'Title' => function($record, $column, $grid) {
				return TextField::create($column, ' ')
					->setAttribute('placeholder', _t('UserDefinedForm.TITLE', 'Title'));
			}
		));

		$config = GridFieldConfig::create()
			->addComponents(
				$editableColumns,
				new GridFieldButtonRow(),
				$addField = new GridFieldAddNewInlineButton(),
				$addStep = new GridFieldAddItemInlineButton('EditableFormStep'),
				new GridFieldEditButton(),
				new GridFieldDeleteAction(),
				new GridFieldToolbarHeader(),
				new GridFieldOrderableRows('Sort'),
				new GridState_Component(),
				new GridFieldDetailForm()
			);
		$addField->setTitle('Add Field');
		$addStep->setTitle('Add Page Break');
		$addStep->setExtraClass('uf-gridfield-steprow');

		$fieldEditor = GridField::create(
			'Fields',
			_t('UserDefinedForm.FIELDS', 'Fields'),
			$fields,
			$config
		);

		return $fieldEditor;
	}

	/**
	 * A UserForm must have at least one step.
	 * If no steps exist, create an initial step, and put all fields inside it.
	 *
	 * @param bool $force
	 * @return void
	 */
	public function createInitialFormStep($force = false) {
		// Only invoke once saved
		if(!$this->owner->exists()) {
			return;
		}

		// Check if first field is a step
		$fields = $this->owner->Fields();
		$firstField = $fields->first();
		if($firstField instanceof EditableFormStep) {
			return;
		}

		// Don't create steps on write if there are no formfields, as this
		// can create duplicate first steps during publish of new records
		if(!$force && !$firstField) {
			return;
		}

		// Re-apply sort to each field starting at 2
		$next = 2;
		foreach($fields as $field) {
			$field->Sort = $next++;
			$field->write();
		}

		// Add step
		$step = EditableFormStep::create();
		$step->Title = _t('EditableFormStep.TITLE_FIRST', 'First Step');
		$step->Sort = 1;
		$step->write();
		$fields->add($step);
	}

	/**
	 * @return array
	 */
	public function getEditableFieldClasses() {
		$classes = ClassInfo::getValidSubClasses('EditableFormField');

		// Remove classes we don't want to display in the dropdown.
		$classes = array_diff($classes, array(
			'EditableFormField',
			'EditableMultipleOptionField'
		));

		$editableFieldClasses = array();

		foreach ($classes as $key => $className) {
			$singleton = singleton($className);

			if(!$singleton->canCreate()) {
				continue;
			}

			$editableFieldClasses[$className] = $singleton->i18n_singular_name();
		}

		return $editableFieldClasses;
	}

	/**
	 * Ensure that at least one page exists at the start
	 */
	public function onAfterWrite() {
		$this->createInitialFormStep();
	}

	/**
	 * @see SiteTree::doPublish
	 * @param Page $original
	 *
	 * @return void
	 */
	public function onAfterPublish($original) {
		// Remove fields on the live table which could have been orphaned.
		$live = Versioned::get_by_stage("EditableFormField", "Live")
			->filter('ParentID', $original->ID);

		if($live) {
			foreach($live as $field) {
				$field->doDeleteFromStage('Live');
			}
		}

		foreach($this->owner->Fields() as $field) {
			$field->doPublish('Stage', 'Live');
		}
	}

	/**
	 * @see SiteTree::doUnpublish
	 * @param Page $page
	 *
	 * @return void
	 */
	public function onAfterUnpublish($page) {
		foreach($page->Fields() as $field) {
			$field->doDeleteFromStage('Live');
		}
	}

	/**
	 * @see SiteTree::duplicate
	 * @param DataObject $newPage
	 *
	 * @return DataObject
	 */
	public function onAfterDuplicate($newPage) {
		foreach($this->owner->Fields() as $field) {
			$newField = $field->duplicate(false);
			$newField->ParentID = $newPage->ID;
			$newField->ParentClass = $newPage->ClassName;
			$newField->Version = 0;
			$newField->write();

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
	public function getIsModifiedOnStage($isModified) {
		if(!$isModified) {
			foreach($this->owner->Fields() as $field) {
				if($field->getIsModifiedOnStage()) {
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
	public function onAfterRevertToLive($page) {
		foreach($page->Fields() as $field) {
			$field->publish('Live', 'Stage', false);
			$field->writeWithoutVersion();
		}
	}
}
