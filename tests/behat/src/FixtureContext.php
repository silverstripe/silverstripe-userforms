<?php

namespace SilverStripe\UserForms\Tests\Behat\Context;

use SilverStripe\BehatExtension\Context\FixtureContext as BaseFixtureContext;
use SilverStripe\ORM\DataObject;
use SilverStripe\UserForms\Model\EditableCustomRule;
use SilverStripe\UserForms\Model\EditableFormField\EditableFormStep;
use SilverStripe\UserForms\Model\EditableFormField\EditableTextField;
use SilverStripe\UserForms\Model\UserDefinedForm;
use SilverStripe\Versioned\Versioned;

/**
 * Context used to create fixtures in the SilverStripe ORM.
 */
class FixtureContext extends BaseFixtureContext
{
    /**
     * @When /^I click the "([^"]+)" element$/
     * @param $selector
     */
    public function iClickTheElement(string $selector): void
    {
        $page = $this->getMainContext()->getSession()->getPage();
        $element = $page->find('css', $selector);
        assertNotNull($element, sprintf('Element %s not found', $selector));
        $element->click();
    }

    /**
     * Example: Given a userform with a hidden form step "My userform"
     *
     * @Given /^a userform with a hidden form step "([^"]+)"$/
     * @param string $udfTitle
     */
    public function stepCreateUserFormWithHiddenFormStep(string $udfTitle): void
    {
        /** @var UserDefinedForm|Versioned $udf */
        /** @var EditableTextField $tf01 */
        /** @var EditableFormStep $fs02 */
        $udf = $this->getFixtureFactory()->createObject(UserDefinedForm::class, $udfTitle);
        $this->createEditableFormField(EditableFormStep::class, 'EditableFormStep_01', $udf);
        $tf01 = $this->createEditableFormField(EditableTextField::class, 'EditableTextField_01', $udf);
        $fs02 = $this->createEditableFormField(EditableFormStep::class, 'EditableFormStep_02', $udf);
        $this->createEditableFormField(EditableTextField::class, 'EditableTextField_02', $udf);
        $fs02->ShowOnLoad = 0;
        $fs02->write();
        $this->createCustomRule('cr1', $fs02, $tf01);
        $this->createEditableFormField(EditableFormStep::class, 'EditableFormStep_03', $udf);
        $this->createEditableFormField(EditableTextField::class, 'EditableTextField_03', $udf);
        $udf->publishRecursive();
    }

    private function createEditableFormField(string $class, string $id, UserDefinedForm $udf): DataObject
    {
        $field = $this->getFixtureFactory()->createObject($class, $id);
        $field->Title = $id;
        $field->Parent = $udf;
        $field->write();
        return $field;
    }

    private function createCustomRule(string $id, EditableFormStep $fs, EditableTextField $tf): EditableCustomRule
    {
        /** @var EditableCustomRule $rule */
        $rule = $this->getFixtureFactory()->createObject(EditableCustomRule::class, $id);
        $rule->Parent = $fs;
        $rule->ConditionField = $tf;
        $rule->Display = 'Show';
        $rule->ConditionOption = 'IsNotBlank';
        $rule->write();
        return $rule;
    }
}
