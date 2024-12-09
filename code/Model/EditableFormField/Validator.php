<?php

namespace SilverStripe\UserForms\Model\EditableFormField;

use SilverStripe\Forms\Validation\RequiredFieldsValidator;
use SilverStripe\UserForms\Model\EditableFormField;

class Validator extends RequiredFieldsValidator
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

        return true;
    }
}
