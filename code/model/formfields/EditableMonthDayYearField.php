<?php
class EditableMonthDayYearField extends EditableDateField {

	private static $singular_name = 'MonthDayYear Field';

	private static $plural_name = 'MonthDayYear Field';

	public function getIcon() {
	    return  USERFORMS_DIR . '/images/editablemonthdayyearfield.png';
	}
	
	
	public function getFormField() {
	    $defaultValue = ($this->getSetting('DefaultToToday')) ? date('Y-m-d') : $this->Default;
	    $field = EditableDateField_FormField::create( $this->Name, $this->Title, $defaultValue);
	    $field->setConfig('showcalendar', false)
        		->setConfig('dmyfields', true)
        		->setConfig('dmyseparator', '/') // set the separator
        		->setConfig('dmyplaceholders', 'true'); // enable HTML 5 Placeholders
	
	    if ($this->Required) {
	        // Required validation can conflict so add the Required validation messages
	        // as input attributes
	        $errorMessage = $this->getErrorMessage()->HTML();
	        $field->setAttribute('data-rule-required', 'true');
	        $field->setAttribute('data-msg-required', $errorMessage);
	    }
	
	    return $field;
	}
	

    /**
     * Set date to string formated as Mmm d, YYYY
     * 
     * uses strtotime
     */
    
	public function getValueFromData($data) {
	    $strDate = 'Error'; //return error if can't get valid value
	    
	    if(isset($data[$this->Name])) {
    	    if (is_array($data[$this->Name])) {
    	        if ($data[$this->Name]['year']  && $data[$this->Name]['month'] != '' && $data[$this->Name]['day']) {
    	            $year = $data[$this->Name]['year'];
    	            if (strlen($year) == 1) {
    	                $year = "0$year";
    	            }
    	            if (strlen($year) == 2) {
    	                $curyear = date('Y');
    	                $year = substr(strval($curyear-$year), 0, 2) . $year;
    	            }
    	            $time = strtotime("{$year}-{$data[$this->Name]['month']}-{$data[$this->Name]['day']}");
    	            $strDate = date('M j, Y', $time);
    	        }
    	    } else {
    	        $strDate = "Error - invalid date parts";
    	    }
    	    
	    } else { //not array, return value
	        $strDate = $data[$this->Name];
	    }
    	    
        return $strDate;
	}
	
	
}