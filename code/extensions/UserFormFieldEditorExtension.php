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

		$fields->findOrMakeTab('Root.Form', _t('UserDefinedForm.FORM', 'Form'));
		$fields->addFieldToTab('Root.Form', $fieldEditor);

		return $fields;
	}

	/**
	 * Gets the field editor, for adding and removing EditableFormFields.
	 *
	 * @return GridField
	 */
	public function getFieldEditorGrid() {
		$fieldEditor = GridField::create('Fields', _t('UserDefinedForm.FIELDS', 'Fields'), $this->owner->Fields());

		$config = GridFieldConfig::create()
			->addComponents(
				(new GridFieldEditableColumns())
					->setDisplayFields(array(
						'ClassName' => function($record, $column, $grid) {
							return DropdownField::create($column, '', $this->getEditableFieldClasses());
						},
						'Title' => function($record, $column, $grid) {
							return TextField::create($column, ' ')
								->setAttribute('placeholder', _t('UserDefinedForm.TITLE', 'Title'));
						}
				)),
				new GridFieldButtonRow(),
				new GridFieldAddNewInlineButton(),
				new GridFieldEditButton(),
				new GridFieldDeleteAction(),
				new GridFieldToolbarHeader(),
				new GridFieldOrderableRows('Sort'),
				new GridState_Component(),
				new GridFieldDetailForm()
			);

		$fieldEditor->setConfig($config);

		return $fieldEditor;
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
	 * @see SiteTree::doPublish
	 * @param Page $original
	 *
	 * @return void
	 */
	public function onAfterPublish($original) {
		// Remove fields on the live table which could have been orphaned.
		$live = Versioned::get_by_stage("EditableFormField", "Live", array(
			'ParentID' => $original->ID
		));

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
			$newField = $field->duplicate();
			$newField->ParentID = $newPage->ID;
			$newField->ParentClass = $newPage->ClassName;
			$newField->write();

			foreach ($field->CustomRules() as $customRule) {
				$newRule = $customRule->duplicate();
				$newRule->ParentID = $newField->ID;
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
