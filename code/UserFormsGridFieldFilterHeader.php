<?php

/**
 * Extension to the build in SilverStripe {@link GridField} to allow for 
 * filtering {@link SubmittedForm} objects in the submissions tab by 
 * entering the value of a field
 *
 * @package userforms
 */
class UserFormsGridFieldFilterHeader extends GridFieldFilterHeader {
	
	public function handleAction(GridField $gridField, $actionName, $arguments, $data) {
		if(!$this->checkDataType($gridField->getList())) {
			return;
		}

		if($actionName === 'filter') {
			$gridField->State->UserFormsGridField = array(
				'filter' => isset($data['FieldNameFilter']) ? $data['FieldNameFilter'] : null,
				'value' => isset($data['FieldValue']) ? $data['FieldValue'] : null
			);
		}
	}


	public function getHTMLFragments($gridField) {
		$fields = new ArrayList();
		$state = $gridField->State->UserFormsGridField;

		$selectedField = $state->filter;
		$selectedValue = $state->value;

		// get the state of the grid field and populate the default values.

		// retrieve a list of all the available form fields that have been 
		// submitted in this form.
		$formFields = SubmittedFormField::get()
			->filter(array(
				"SubmittedForm.ParentID" => $gridField->getList()->column()
			))
			->leftJoin('SubmittedForm', 'SubmittedFormField.ParentID = SubmittedForm.ID')
			->sort('Title', 'ASC')
			->map('Name', 'Title');
		
		// show dropdown of all the fields available from the submitted form fields
		// that have been saved. Takes the titles from the currently live form.
		$columnField = new DropdownField(
			'FieldNameFilter', 
			'', 
			$formFields->toArray(), 
			$selectedField, 
			null, 
			_t('UserFormsGridFieldFilterHeader.FILTERSUBMISSIONS', 'Filter Submissions..')
		);

		$valueField = new TextField('FieldValue', '', $selectedValue);

		$columnField->addExtraClass('ss-gridfield-sort');
		$columnField->addExtraClass('no-change-track');

		$valueField->addExtraClass('ss-gridfield-sort');
		$valueField->addExtraClass('no-change-track');
		$valueField->setAttribute(
			'placeholder', 
			_t('UserFormsGridFieldFilterHeader.WHEREVALUEIS', 'where value is..'
		));

		$fields->push($columnField);
		$fields->push($valueField);

		$fields->push($actions = new FieldGroup(
			GridField_FormAction::create($gridField, 'filter', false, 'filter', null)
				->addExtraClass('ss-gridfield-button-filter')
				->setAttribute('title', _t('GridField.Filter', "Filter"))
				->setAttribute('id', 'action_filter_' . $gridField->getModelClass() . '_' . $columnField),
			GridField_FormAction::create($gridField, 'reset', false, 'reset', null)
				->addExtraClass('ss-gridfield-button-close')
				->setAttribute('title', _t('GridField.ResetFilter', "Reset"))
				->setAttribute('id', 'action_reset_' . $gridField->getModelClass() . '_' . $columnField)
			)
		);

		$actions->addExtraClass('filter-buttons');
		$actions->addExtraClass('no-change-track');

		$forTemplate = new ArrayData(array(
			'Fields' => $fields
		));


		return array(
			'header' => $forTemplate->renderWith('GridFieldFilterHeader_Row')
		);
	}

	public function getManipulatedData(GridField $gridField, SS_List $dataList) {
		$state = $gridField->State;

		if($filter = $state->UserFormsGridField->toArray()) {
			if(isset($filter['filter']) && isset($filter['value'])) {
				$dataList = $dataList->where(sprintf("
					SELECT COUNT(*) FROM SubmittedFormField 
					WHERE ( 
						ParentID = SubmittedForm.ID AND
						Name = '%s' AND 
						Value LIKE '%s'
					) > 0",

					Convert::raw2sql($filter['filter']),
					Convert::raw2sql($filter['value'])
				));
			}
		}

		return $dataList;
	}
}