<?php
/**
 * Represents the base class of a editable form field 
 * object like {@link EditableTextField}. 
 *
 * @package userforms
 */
class EditableFormField extends DataObject {
	
	static $default_sort = "Sort";
	
	static $db = array(
		"Name" => "Varchar",
		"Title" => "Varchar(255)",
		"Default" => "Varchar",
		"Sort" => "Int",
		"Required" => "Boolean",
	  	"CanDelete" => "Boolean",
    	"CustomParameter" => "Varchar",
		"OptionallyDisplay" => "Boolean"
	);
    
	static $defaults = array(
		"CanDelete" => "1"
	);
    
	static $has_one = array(
		"Parent" => "SiteTree",
	);
	
	/**
	 * @var bool Is this field readonly to the user
	 */
	protected $readonly;
	
	/**
	 * @var FieldEditor The current editor
	 */
	protected $editor;

	/**
	 * Construct a new EditableFormField Object.
	 * 
	 * @param array|null $record This will be null for a new database record. 
	 * @param boolean $isSingleton This this to true if this is a singleton() object, a stub for calling methods. 
	 */
	public function __construct($record = null, $isSingleton = false) {
		$this->setField('Default', -1);
		parent::__construct( $record, $isSingleton );
	}	
	
	/**
	 * Set the FieldEditor object for this field.
	 *
	 * @param FieldEditor The Editor window you wish to use
	 */
	protected function setEditor($editor) {
		$this->editor = $editor;
	}
	
	function EditSegment() {
		return $this->renderWith('EditableFormField');
	}
	
	function isReadonly() {
		return $this->readonly;
	}
	
	function ClassName() {
		return $this->class;
	}
	
	/**
	 * Return whether or not this field has addable options
	 * such as a dropdown field or radio set
	 *
	 * @return bool
	 */
	public function hasAddableOptions() {
		return false;
	}
	
	/**
	 * Return whether or not this field needs to show the extra
	 * options dropdown list
	 * 
	 * @return bool
	 */
	public function showExtraOptions() {
		return true;
	}
	
	function makeReadonly() {
		$this->readonly = true;
		return $this;
	}
	
	function ReadonlyEditSegment() {
		$this->readonly = true;
		return $this->EditSegment();
	}
	
	function TitleField() {
		$titleAttr = Convert::raw2att($this->Title);
		$readOnlyAttr = ($this->readonly) ? ' disabled="disabled"' : '';
		
		return "<input type=\"text\" class=\"text\" title=\"("._t('EditableFormField.ENTERQUESTION', 'Enter Question').")\" value=\"$titleAttr\" name=\"Fields[{$this->ID}][Title]\"$readOnlyAttr />";
	}
	
	function Name() {
		return "Fields[".$this->ID."]";
	}
	
	/**
	 * How to save the data submitted in this field into
	 * the database object which this field represents.
	 *
	 * Any class's which call this should also call 
	 * {@link parent::populateFromPostData()} to ensure 
	 * that this method is called
	 *
	 * @access public
	 */
	public function populateFromPostData($data) {
		$this->Title = (isset($data['Title'])) ? $data['Title']: "";
		$this->Default = (isset($data['Default'])) ? $data['Default'] : "";
		$this->Sort = isset($data['Sort']) ? $data['Sort'] : null;
  		$this->CustomParameter = isset($data['CustomParameter']) ? $data['CustomParameter'] : null;
		$this->Required = !empty($data['Required']) ? 1 : 0;
  		$this->CanDelete = (isset($data['CanDelete']) && !$data['CanDelete']) ? 0 : 1;
		$this->Name = $this->class.$this->ID;
		$this->write();
	}
	
	function ExtraOptions() {
		
		$baseName = "Fields[$this->ID]";
		$extraOptions = new FieldSet();
		
		if(!$this->Parent()->hasMethod('hideExtraOption')){
			$extraOptions->push(new CheckboxField($baseName . "[Required]", _t('EditableFormField.REQUIRED', 'Required?'), $this->Required));
		}
		elseif(!$this->Parent()->hideExtraOption('Required')){
			$extraOptions->push(new CheckboxField($baseName . "[Required]", _t('EditableFormField.REQUIRED', 'Required?'), $this->Required));
		}
		
		if($this->Parent()->hasMethod('getExtraOptionsForField')) {
			$extraFields = $this->Parent()->getExtraOptionsForField($this);
		
			foreach($extraFields as $extraField) {
				$extraOptions->push($extraField);
			}
		}
		
		if($this->readonly) {
			$extraOptions = $extraOptions->makeReadonly();		
		}
		
		// support for optionally display field
		// $extraOptions->push(new CheckboxField($baseName ."[OptionallyDisplay]", _t('EditableFormField.OPTIONALLYDISPLAY', 'Optionally Display Field'), $this->OptionallyDisplay));
		
		return $extraOptions;
	}
	
	/**
	 * Return a FormField to appear on the front end
	 */
	function getFormField() {
	}
	
	function getFilterField() {
		
	}
	
	/**
	 * Return an evaluation appropriate for a filter clause
	 * @todo: escape the string
	 */
	function filterClause( $value ) {
		// Not filtering on this field
		
		if( $value == '-1' ) 
			return "";
		else
			return "`{$this->name}` = '$value'";
	}
	
	function showInReports() {
		return true;
	}
    
    function prepopulate( $value ) {
        $this->prepopulateFromMap( $this->parsePrepopulateValue( $value ) );
    }
    
    protected function parsePrepopulateValue( $value ) {
        $paramList = explode( ',', $value );
        $paramMap = array();
        
        foreach( $paramList as $param ) {
    
            if( preg_match( '/([^=]+)=(.+)/', $param, $match ) ) {
                if( isset( $paramMap[$match[1]] ) && is_array( $paramMap[$match[1]] ) ) {
                    $paramMap[$match[1]][] = $match[2];
                } else if( isset( $paramMap[$match[1]] ) ) {
                    $paramMap[$match[1]] = array( $paramMap[$match[1]] );
                    $paramMap[$match[1]][] = $match[2];
                } else {
                    $paramMap[$match[1]] = $match[2];
                }
            }
        }
        return $paramMap;   
    }
    
    protected function prepopulateFromMap( $paramMap ) {
        foreach($paramMap as $field => $fieldValue) {
            if(!is_array($fieldValue)) {
                $this->$field = $fieldValue;
            }   
        }
    }

    function Type() {
        return $this->class;   
    }
    
    function CustomParameter() {
        return $this->CustomParameter;   
    }
}
?>