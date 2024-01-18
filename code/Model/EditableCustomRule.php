<?php

namespace SilverStripe\UserForms\Model;

use InvalidArgumentException;
use LogicException;
use SilverStripe\CMS\Controllers\CMSMain;
use SilverStripe\Control\Controller;
use SilverStripe\Core\Convert;
use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Member;
use SilverStripe\Versioned\Versioned;

/**
 * A custom rule for showing / hiding an EditableFormField
 * based the value of another EditableFormField.
 *
 * @property string $ConditionOption
 * @property int $ConditionFieldID
 * @property string $Display
 * @property string $FieldValue
 * @property int $ParentID
 * @method EditableFormField ConditionField()
 * @method EditableFormField Parent()
 */
class EditableCustomRule extends DataObject
{
    private static $condition_options = [
        'IsBlank' => 'Is blank',
        'IsNotBlank' => 'Is not blank',
        'HasValue' => 'Equals',
        'ValueNot' => 'Doesn\'t equal',
        'ValueLessThan' => 'Less than',
        'ValueLessThanEqual' => 'Less than or equal',
        'ValueGreaterThan' => 'Greater than',
        'ValueGreaterThanEqual' => 'Greater than or equal'
    ];

    private static $db = [
        'Display' => 'Enum("Show,Hide")',
        'ConditionOption' => 'Enum("IsBlank,IsNotBlank,HasValue,ValueNot,ValueLessThan,ValueLessThanEqual,ValueGreaterThan,ValueGreaterThanEqual")',
        'FieldValue' => 'Varchar(255)'
    ];

    private static $has_one = [
        'Parent' => EditableFormField::class,
        'ConditionField' => EditableFormField::class
    ];

    /**
     * Built in extensions required
     *
     * @config
     * @var array
     */
    private static $extensions = [
        Versioned::class . "('Stage', 'Live')"
    ];

    private static $table_name = 'EditableCustomRule';

    /**
     * @param Member $member
     * @return bool
     */
    public function canDelete($member = null)
    {
        return $this->canEdit($member);
    }

    /**
     * @param Member $member
     * @return bool
     */
    public function canEdit($member = null)
    {
        return $this->Parent()->canEdit($member);
    }

    /**
     * @param Member $member
     * @return bool
     */
    public function canView($member = null)
    {
        return $this->Parent()->canView($member);
    }

    /**
     * Return whether a user can create an object of this type
     *
     * @param Member $member
     * @param array  $context Virtual parameter to allow context to be passed in to check
     * @return bool
     */
    public function canCreate($member = null, $context = [])
    {
        // Check parent page
        $parent = $this->getCanCreateContext(func_get_args());
        if ($parent) {
            return $parent->canEdit($member);
        }

        // Fall back to secure admin permissions
        return parent::canCreate($member);
    }

    /**
     * Helper method to check the parent for this object
     *
     * @param array $args List of arguments passed to canCreate
     * @return DataObject Some parent dataobject to inherit permissions from
     */
    protected function getCanCreateContext($args)
    {
        // Inspect second parameter to canCreate for a 'Parent' context
        if (isset($args[1]['Parent'])) {
            return $args[1]['Parent'];
        }
        // Hack in currently edited page if context is missing
        if (Controller::has_curr() && Controller::curr() instanceof CMSMain) {
            return Controller::curr()->currentPage();
        }

        // No page being edited
        return null;
    }

    /**
     * @param Member $member
     * @return bool
     */
    public function canPublish($member = null)
    {
        return $this->canEdit($member);
    }

    /**
     * @param Member $member
     * @return bool
     */
    public function canUnpublish($member = null)
    {
        return $this->canDelete($member);
    }

    /**
     * Substitutes configured rule logic with it's JS equivalents and returns them as array elements
     *
     * @return array
     * @throws LogicException If the provided condition option was not able to be handled
     */
    public function buildExpression()
    {
        $formFieldWatch = $this->ConditionField();
        //Encapsulated the action to the object
        $action = $formFieldWatch->getJsEventHandler();

        // is this field a special option field
        $checkboxField = $formFieldWatch->isCheckBoxField();
        $radioField = $formFieldWatch->isRadioField();
        $target = sprintf('$("%s")', $formFieldWatch->getSelectorFieldOnly());
        $fieldValue = Convert::raw2js($this->FieldValue);

        $conditionOptions = [
            'ValueLessThan'         => '<',
            'ValueLessThanEqual'    => '<=',
            'ValueGreaterThan'      => '>',
            'ValueGreaterThanEqual' => '>='
        ];

        // and what should we evaluate
        switch ($this->ConditionOption) {
            case 'IsNotBlank':
            case 'IsBlank':
                $expression = ($checkboxField || $radioField) ? "!{$target}.is(\":checked\")" : "{$target}.val() == ''";
                if ((string) $this->ConditionOption === 'IsNotBlank') {
                    //Negate
                    $expression = "!({$expression})";
                }
                break;
            case 'HasValue':
            case 'ValueNot':
                if ($checkboxField) {
                    if ($formFieldWatch->isCheckBoxGroupField()) {
                        $expression = sprintf(
                            "$.inArray('%s', %s.filter(':checked').map(function(){ return $(this).val();}).get()) > -1",
                            $fieldValue,
                            $target
                        );
                    } else {
                        $expression = "{$target}.prop('checked')";
                    }
                } elseif ($radioField) {
                    // We cannot simply get the value of the radio group, we need to find the checked option first.
                    $expression = sprintf(
                        '%s.closest(".field, .control-group").find("input:checked").val() == "%s"',
                        $target,
                        $fieldValue
                    );
                } else {
                    $expression = sprintf('%s.val() == "%s"', $target, $fieldValue);
                }

                if ((string) $this->ConditionOption === 'ValueNot') {
                    //Negate
                    $expression = "!({$expression})";
                }
                break;
            case 'ValueLessThan':
            case 'ValueLessThanEqual':
            case 'ValueGreaterThan':
            case 'ValueGreaterThanEqual':
                $expression = sprintf(
                    '%s.val() %s parseFloat("%s")',
                    $target,
                    $conditionOptions[$this->ConditionOption],
                    $fieldValue
                );
                break;
            default:
                throw new LogicException("Unhandled rule {$this->ConditionOption}");
                break;
        }

        $result = [
            'operation' => $expression,
            'event'     => $action,
        ];

        return $result;
    }


    /**
     * Determines whether the rule is satisfied, based on provided form data.
     * Used for php validation of required conditional fields
     *
     * @param array $data Submitted form data
     * @return boolean
     * @throws LogicException Invalid ConditionOption is set for this rule.
     */
    public function validateAgainstFormData(array $data)
    {

        $controllingField = $this->ConditionField();

        if (!isset($data[$controllingField->Name])) {
            return false;
        }

        $valid = false;

        $targetFieldValue = $this->FieldValue;
        $actualFieldValue = $data[$controllingField->Name];

        switch ($this->ConditionOption) {
            case 'IsNotBlank':
                $valid = ($actualFieldValue !== '');
                break;
            case 'IsBlank':
                $valid = ($actualFieldValue === '');
                break;
            case 'HasValue':
                $valid = ($actualFieldValue === $targetFieldValue);
                break;
            case 'ValueNot':
                $valid = ($actualFieldValue !== $targetFieldValue);
                break;
            case 'ValueLessThan':
                $valid = ($actualFieldValue < $targetFieldValue);
                break;
            case 'ValueLessThanEqual':
                $valid = ($actualFieldValue <= $targetFieldValue);
                break;
            case 'ValueGreaterThan':
                $valid = ($actualFieldValue > $targetFieldValue);
                break;
            case 'ValueGreaterThanEqual':
                $valid = ($actualFieldValue >= $targetFieldValue);
                break;
            default:
                throw new LogicException("Unhandled rule {$this->ConditionOption}");
                break;
        }

        return $valid;
    }


    /**
     * Returns the opposite visibility function for the value of the initial visibility field, e.g. show/hide. This
     * will toggle the "hide" class either way, which is handled by CSS.
     *
     * @param string $initialState
     * @param boolean $invert
     * @return string
     */
    public function toggleDisplayText($initialState, $invert = false)
    {
        $action = strtolower($initialState ?? '') === 'hide' ? 'removeClass' : 'addClass';
        if ($invert) {
            $action = $action === 'removeClass' ? 'addClass' : 'removeClass';
        }
        return sprintf('%s("hide")', $action);
    }

    /**
     * Returns an event name to be dispatched when the field is changed. Matches up with the visibility classes
     * added or removed in `toggleDisplayText()`.
     *
     * @param string $initialState
     * @param bool $invert
     * @return string
     */
    public function toggleDisplayEvent($initialState, $invert = false)
    {
        $action = strtolower($initialState ?? '') === 'hide' ? 'show' : 'hide';
        if ($invert) {
            $action = $action === 'hide' ? 'show' : 'hide';
        }
        return sprintf('userform.field.%s', $action);
    }
}
