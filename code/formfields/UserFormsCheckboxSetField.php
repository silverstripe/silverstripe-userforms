<?php

/**
 * @package userforms
 */
class UserFormsCheckboxSetField extends CheckboxSetField
{

    /**
     * jQuery validate requires that the value of the option does not contain
     * the actual value of the input.
     *
     * @return ArrayList
     */
    public function getOptions()
    {
        $options = parent::getOptions();

        foreach ($options as $option) {
            $option->Name = "{$this->name}[]";
        }

        return $options;
    }

    /**
     * @inheritdoc
     *
     * @return array
     */
    public function getSourceAsArray()
    {
        $array = parent::getSourceAsArray();
        return array_values($array);
    }

    /**
     * @inheritdoc
     *
     * @param Validator $validator
     *
     * @return bool
     */
    public function validate($validator)
    {
        // get the previous values (could contain comma-delimited list)

        $previous = $value = $this->Value();

        if (is_string($value) && strstr($value, ",")) {
            $value = explode(",", $value);
        }

        // set the value as an array for parent validation

        $this->setValue($value);

        $validated = parent::validate($validator);

        // restore previous value after validation

        $this->setValue($previous);

        return $validated;
    }
}
