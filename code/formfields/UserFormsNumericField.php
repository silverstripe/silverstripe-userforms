<?php


class UserFormsNumericField extends NumericField
{

    /**
     * @var int
     */
    private $min;

    /**
     * @var int
     */
    private $max;

    /**
     * @return int|null
     */
    public function getMin()
    {
        return $this->min;
    }

    /**
     * @param int $min
     * @return $this
     * @throws Exception If the provided argument is not numeric
     */
    public function setMin($min)
    {
        if (!is_numeric($min)) {
            throw new Exception('$min must be a number.');
        }

        $this->min = $min;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getMax()
    {
        return $this->max;
    }

    /**
     * @param int $max
     * @return $this
     * @throws Exception If the provided argument is not numeric
     */
    public function setMax($max)
    {
        if (!is_numeric($max)) {
            throw new Exception('$max must be a number.');
        }
        $this->max = $max;
        return $this;
    }

    public function validate($validator)
    {
        $isValid = parent::validate($validator);
        if (!$isValid) {
            return false;
        }

        if ((!empty($this->min) && $this->value < $this->min) || (!empty($this->max) && $this->value > $this->max)) {
            $msg = (!empty($this->min) && $this->value < $this->min) ?
                _t(self::class . 'RANGE_ERROR_MIN', 'Please enter a value that is no less than {min}.', ['min' => $this->getMin()]) :
                _t(self::class . 'RANGE_ERROR_MAX', 'Please enter a value that is no more than {max}.', ['max' => $this->getMax()]);
            $validator->validationError($this->name, $msg, "validation", false);

            return false;
        }

        return true;
    }
}
