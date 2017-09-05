<?php

namespace SilverStripe\UserForms\FormField;

/**
 * Represents a page step in a form, which may contain form fields or other groups
 */
class UserFormsStepField extends UserFormsCompositeField
{
    private static $casting = [
        'StepNumber' => 'Int'
    ];

    /**
     * Numeric index (1 based) of this step
     *
     * Null if unassigned
     *
     * @var int|null
     */
    protected $number = null;

    public function FieldHolder($properties = [])
    {
        return $this->Field($properties);
    }

    /**
     * Get the step number
     *
     * @return int|null
     */
    public function getStepNumber()
    {
        return $this->number;
    }

    /**
     * Re-assign this step to another number
     *
     * @param type $number
     * @return $this
     */
    public function setStepNumber($number)
    {
        $this->number = $number;
        return $this;
    }
}
