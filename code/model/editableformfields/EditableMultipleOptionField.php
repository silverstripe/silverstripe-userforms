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

class EditableMultipleOptionField extends EditableFormField {
	
	private static $has_many = array(
		"Options" => "EditableOption"
	);

	/**
	 * @return FieldList
	 */
	public function getCMSFields() {
		$fields = parent::getCMSFields();

		$optionsGrid = GridField::create(
			'Options',
			_t('EditableFormField.CUSTOMOPTIONS', 'Options'),
			$this->Options()
		);

		$optionsConfig = GridFieldConfig::create()
			->addComponents(
				(new GridFieldEditableColumns())
				->setDisplayFields(array(
					'Title' => function($record, $column, $grid) {
						return TextField::create($column);
					},
					'Default' => function($record, $column, $grid) {
						return CheckboxField::create($column);
					},
					'ParentID' => function($record, $column, $grid) {
						return HiddenField::create($column, '', $this->ID);
					}
				)),
				new GridFieldButtonRow(),
				new GridFieldToolbarHeader(),
				new GridFieldAddNewInlineButton(),
				new GridFieldDeleteAction(),
				new GridState_Component()
			);

		$optionsGrid->setConfig($optionsConfig);

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
	public function doPublish($fromStage, $toStage, $createNewVersion = false) {
		$live = Versioned::get_by_stage("EditableOption", "Live", "\"EditableOption\".\"ParentID\" = $this->ID");

		if($live) {
			foreach($live as $option) {
				$option->delete();
			}
		}
		
		if($this->Options()) {
			foreach($this->Options() as $option) {
				$option->publish($fromStage, $toStage, $createNewVersion);
			}
		}
		
		$this->publish($fromStage, $toStage, $createNewVersion);
	}
	
	/**
	 * Unpublishing Versioning support
	 * 
	 * When unpublishing the field it has to remove all options attached
	 *
	 * @return void
	 */
	public function doDeleteFromStage($stage) {
		if($this->Options()) {
			foreach($this->Options() as $option) {
				$option->deleteFromStage($stage);
			}
		}
		
		$this->deleteFromStage($stage);
	}
	
	/**
	 * Deletes all the options attached to this field before deleting the 
	 * field. Keeps stray options from floating around
	 *
	 * @return void
	 */
	public function delete() {
		$options = $this->Options();

		if($options) {
			foreach($options as $option) {
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
	public function duplicate($doWrite = true) {
		$clonedNode = parent::duplicate();
		
		if($this->Options()) {
			foreach($this->Options() as $field) {
				$newField = $field->duplicate();
				$newField->ParentID = $clonedNode->ID;
				$newField->write();
			}
		}
		
		return $clonedNode;
	}
	
	/**
	 * Return whether or not this field has addable options such as a 
	 * {@link EditableDropdownField} or {@link EditableRadioField}
	 *
	 * @return bool
	 */
	public function getHasAddableOptions() {
		return true;
	}

	/**
	 * Return the form field for this object in the front end form view
	 *
	 * @return FormField
	 */
	public function getFormField() {
		return user_error('Please implement getFormField() on '. $this->class, E_USER_ERROR);
	}
}
