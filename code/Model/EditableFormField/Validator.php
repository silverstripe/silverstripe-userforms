<?php

namespace SilverStripe\UserForms\Model\EditableFormField;

use SilverStripe\Forms\RequiredFields;
use SilverStripe\UserForms\Model\EditableCustomRule;

class Validator extends RequiredFields
{
    /**
     *
     * @var EditableFormField
     */
    protected $record = null;

    /**
     *
     * @param EditableFormField $record
     * @return $this
     */
    public function setRecord($record)
    {
        $this->record = $record;
        return $this;
    }

    /*
     * @return EditableFormField
     */
    public function getRecord()
    {
        return $this->record;
    }


    public function php($data)
    {
        if (!parent::php($data)) {
            return false;
        }

        // Skip unsaved records
        if (!$this->record || !$this->record->exists()) {
            return true;
        }

        // Skip validation if not required
        if (empty($data['Required'])) {
            return;
        }

        // Skip validation if no rules
        $count = EditableCustomRule::get()->filter('ParentID', $this->record->ID)->count();
        if ($count == 0) {
            return true;
        }

        // Both required = true and rules > 0 should error
        $this->validationError(
            'Required_Error',
            _t(
                __CLASS__.'.REQUIRED_ERROR',
                'Form fields cannot be required and have conditional display rules.'
            ),
            'error'
        );
        return false;
    }
}
