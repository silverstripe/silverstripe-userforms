<?php

namespace SilverStripe\UserForms\Form;

use SilverStripe\Core\Convert;
use SilverStripe\Forms\CompositeField;
use SilverStripe\Forms\DateField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldGroup;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridField_FormAction;
use SilverStripe\Forms\GridField\GridFieldDataColumns;
use SilverStripe\Forms\GridField\GridFieldFilterHeader;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\FieldType\DBDate;
use SilverStripe\ORM\SS_List;
use SilverStripe\View\ArrayData;

/**
 * Extension to the build in SilverStripe {@link GridField} to allow for
 * filtering {@link SubmittedForm} objects in the submissions tab by
 * entering the value of a field
 *
 * @package userforms
 */
class UserFormsGridFieldFilterHeader extends GridFieldFilterHeader
{
    /**
     * A map of name => value of columns from all submissions
     * @var array
     */
    protected $columns;

    public function setColumns($columns)
    {
        $this->columns = $columns;
    }

    public function handleAction(GridField $gridField, $actionName, $arguments, $data)
    {
        if (!$this->checkDataType($gridField->getList())) {
            return;
        }

        if ($actionName === 'filter') {
            $gridField->State->UserFormsGridField = array(
                'filter' => isset($data['FieldNameFilter']) ? $data['FieldNameFilter'] : null,
                'value' => isset($data['FieldValue']) ? $data['FieldValue'] : null,
                'start' => isset($data['StartFilter']) ? $data['StartFilter'] : null,
                'end' => isset($data['EndFilter']) ? $data['EndFilter'] : null
            );
        }
    }


    public function getHTMLFragments($gridField)
    {
        $fields = ArrayList::create();
        $state = $gridField->State->UserFormsGridField;

        $selectedField = $state->filter;
        $selectedValue = $state->value;

        // show dropdown of all the fields available from the submitted form fields
        // that have been saved. Takes the titles from the currently live form.
        $columnField = DropdownField::create('FieldNameFilter', '');
        $columnField->setSource($this->columns);
        $columnField->setEmptyString(_t(__CLASS__.'.FILTERSUBMISSIONS', 'Filter Submissions..'));
        $columnField->setHasEmptyDefault(true);
        $columnField->setValue($selectedField);

        $valueField = TextField::create('FieldValue', '', $selectedValue);

        $columnField->addExtraClass('ss-gridfield-sort');
        $columnField->addExtraClass('no-change-track');

        $valueField->addExtraClass('ss-gridfield-sort');
        $valueField->addExtraClass('no-change-track');
        $valueField->setAttribute(
            'placeholder',
            _t(__CLASS__.'.WHEREVALUEIS', 'where value is..')
        );

        $fields->push(FieldGroup::create(CompositeField::create(
            $columnField,
            $valueField
        )));

        $fields->push(FieldGroup::create(CompositeField::create(
            $start = DateField::create('StartFilter', _t(__CLASS__.'.FROM', 'From')),
            $end = DateField::create('EndFilter', _t(__CLASS__.'.TILL', 'Till'))
        )));

        foreach (array($start, $end) as $date) {
            $date->setDateFormat(DBDate::ISO_DATE);
            $date->addExtraClass('no-change-track');
        }

        if ($state->end) {
            $end->setValue($state->end);
        }
        if ($state->start) {
            $start->setValue($state->start);
        }


        $fields->push($actions = FieldGroup::create(
            GridField_FormAction::create($gridField, 'filter', false, 'filter', null)
                ->addExtraClass('ss-gridfield-button-filter btn btn-primary')
                ->setTitle(_t(__CLASS__.'.FILTER', "Filter"))
                ->setAttribute('title', _t(__CLASS__.'.FILTER', "Filter"))
                ->setAttribute('id', 'action_filter_' . $gridField->getModelClass() . '_' . $columnField),
            GridField_FormAction::create($gridField, 'reset', false, 'reset', null)
                ->addExtraClass('ss-gridfield-button-close btn ')
                ->setTitle(_t(__CLASS__.'.RESET', "Reset"))
                ->setAttribute('title', _t(__CLASS__.'.RESET', "Reset"))
                ->setAttribute('id', 'action_reset_' . $gridField->getModelClass() . '_' . $columnField)
        ));

        $actions->addExtraClass('filter-buttons');
        $actions->addExtraClass('no-change-track');

        $colSpan = 2 + count($gridField->getConfig()->getComponentByType(GridFieldDataColumns::class)
            ->getDisplayFields($gridField) ?? []);

        $forTemplate = ArrayData::create(array(
            'Fields'    => $fields,
            'ColSpan'   => $colSpan
        ));


        return array(
            'header' => $forTemplate->renderWith(UserFormsGridFieldFilterHeader::class . '_Row')
        );
    }

    public function getManipulatedData(GridField $gridField, SS_List $dataList)
    {
        $state = $gridField->State;

        if ($filter = $state->UserFormsGridField->toArray()) {
            if (isset($filter['filter']) && $filter['filter'] && isset($filter['value']) && $filter['value']) {
                $dataList = $dataList->where(sprintf(
                    "
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

            if (isset($filter['start']) && $filter['start']) {
                $dataList = $dataList->filter(array(
                    'Created:GreaterThan' => $filter['start']
                ));
            }

            if (isset($filter['end']) && $filter['end']) {
                $dataList = $dataList->filter(array(
                    'Created:LessThan' => $filter['end']
                ));
            }
        }

        return $dataList;
    }
}
