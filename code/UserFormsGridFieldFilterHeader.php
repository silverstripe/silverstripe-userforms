<?php

/**
 * Extension to the build in SilverStripe {@link GridField} to allow for 
 * filtering {@link SubmittedForm} objects in the submissions tab by 
 * entering the value of a field
 *
 * @package userforms
 */
class UserFormsGridFieldFilterHeader extends GridFieldFilterHeader {

	/**
	 * A map of name => value of columns from all submissions
	 * @var array
	 */
	protected $columns;

	public function setColumns($columns) {
		$this->columns = $columns;
	}

	public function handleAction(GridField $gridField, $actionName, $arguments, $data) {
		if(!$this->checkDataType($gridField->getList())) {
			return;
		}

		if($actionName === 'filter') {
			$gridField->State->UserFormsGridField = array(
				'filter' => isset($data['FieldNameFilter']) ? $data['FieldNameFilter'] : null,
				'value' => isset($data['FieldValue']) ? $data['FieldValue'] : null,
				'start' => isset($data['StartFilter']) ? $data['StartFilter'] : null,
				'end' => isset($data['EndFilter']) ? $data['EndFilter'] : null
			);
		}
	}


	public function getHTMLFragments($gridField) {
		$fields = new ArrayList();
		$state = $gridField->State->UserFormsGridField;

		$selectedField = $state->filter;
		$selectedValue = $state->value;
		
		// show dropdown of all the fields available from the submitted form fields
		// that have been saved. Takes the titles from the currently live form.
		$columnField = new DropdownField('FieldNameFilter', '');
		$columnField->setSource($this->columns);
		$columnField->setEmptyString(_t('UserFormsGridFieldFilterHeader.FILTERSUBMISSIONS', 'Filter Submissions..'));
		$columnField->setHasEmptyDefault(true);
		$columnField->setValue($selectedField);

		$valueField = new TextField('FieldValue', '', $selectedValue);

		$columnField->addExtraClass('ss-gridfield-sort');
		$columnField->addExtraClass('no-change-track');

		$valueField->addExtraClass('ss-gridfield-sort');
		$valueField->addExtraClass('no-change-track');
		$valueField->setAttribute(
			'placeholder', 
			_t('UserFormsGridFieldFilterHeader.WHEREVALUEIS', 'where value is..'
		));

		$fields->push(new FieldGroup(new CompositeField(
			$columnField,
			$valueField
		)));

		$fields->push(new FieldGroup(new CompositeField(
			$start = new DateField('StartFilter', 'From'),
			$end = new DateField('EndFilter', 'Till')
		)));

		foreach(array($start, $end) as $date) {
			$date->setConfig('showcalendar', true);
			$date->setConfig('dateformat', 'y-mm-dd');
			$date->setConfig('datavalueformat', 'y-mm-dd');
			$date->addExtraClass('no-change-track');
		}

		$end->setValue($state->end);
		$start->setValue($state->start);


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
			if(isset($filter['filter']) && $filter['filter'] && isset($filter['value']) && $filter['value']) {
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

			if(isset($filter['start']) && $filter['start']) {
				$dataList = $dataList->filter(array(
					'Created:GreaterThan' => $filter['start']
				));
			}

			if(isset($filter['end']) && $filter['end']) {
				$dataList = $dataList->filter(array(
					'Created:LessThan' => $filter['end']
				));
			}
		}

		return $dataList;
	}
}
