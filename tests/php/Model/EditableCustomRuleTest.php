<?php

namespace SilverStripe\UserForms\Tests\Model;

use SilverStripe\Dev\SapphireTest;
use SilverStripe\UserForms\Model\EditableCustomRule;
use SilverStripe\UserForms\Model\EditableFormField\EditableTextField;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Class EditableCustomRulesTest
 */
class EditableCustomRuleTest extends SapphireTest
{
    protected static $fixture_file = 'EditableCustomRuleTest.yml';

    public function testBuildExpression()
    {
        /** @var EditableCustomRule $rule1 */
        $rule1 = $this->objFromFixture(EditableCustomRule::class, 'rule1');
        $result1 = $rule1->buildExpression();

        //Dropdowns expect change event
        $this->assertEquals('change', $result1['event']);
        $this->assertNotEmpty($result1['operation']);

        //Check for equals sign
        $this->assertStringContainsString('==', $result1['operation']);

        /** @var EditableCustomRule $rule2 */
        $rule2 = $this->objFromFixture(EditableCustomRule::class, 'rule2');
        $result2 = $rule2->buildExpression();

        //TextField expect change event
        $this->assertEquals('keyup', $result2['event']);
        $this->assertNotEmpty($result2['operation']);

        //Check for greater than sign
        $this->assertStringContainsString('>', $result2['operation']);
    }

    /**
     * Test that methods are returned for manipulating the presence of the "hide" CSS class depending
     * on whether the field should be hidden or shown
     */
    public function testToggleDisplayText()
    {
        /** @var EditableCustomRule $rule1 */
        $rule1 = $this->objFromFixture(EditableCustomRule::class, 'rule1');
        $this->assertSame('addClass("hide")', $rule1->toggleDisplayText('show'));
        $this->assertSame('removeClass("hide")', $rule1->toggleDisplayText('hide'));
        $this->assertSame('removeClass("hide")', $rule1->toggleDisplayText('show', true));
        $this->assertSame('addClass("hide")', $rule1->toggleDisplayText('hide', true));
    }

    public function testToggleDisplayEvent()
    {
        /** @var EditableCustomRule $rule1 */
        $rule1 = $this->objFromFixture(EditableCustomRule::class, 'rule1');
        $this->assertSame('userform.field.hide', $rule1->toggleDisplayEvent('show'));
        $this->assertSame('userform.field.show', $rule1->toggleDisplayEvent('hide'));
        $this->assertSame('userform.field.show', $rule1->toggleDisplayEvent('show', true));
        $this->assertSame('userform.field.hide', $rule1->toggleDisplayEvent('hide', true));
    }

    public static function dataProviderValidateAgainstFormData()
    {
        return [
            'IsNotBlank with blank value' =>
                ['IsNotBlank', '', '', false],
            'IsNotBlank with nopn-blank value' =>
                ['IsNotBlank', '', 'something', true],
            'IsBlank with blank value' =>
                ['IsBlank', '', '', true],
            'IsBlank with nopn-blank value' =>
                ['IsBlank', '', 'something', false],
            'HasValue with blank value' =>
                ['HasValue', 'NZ', '', false],
            'HasValue with correct value' =>
                ['HasValue', 'NZ', 'NZ', true],
            'HasValue with incorrect value' =>
                ['HasValue', 'NZ', 'UK', false],
            'ValueNot with blank value' =>
                ['ValueNot', 'NZ', '', true],
            'ValueNot with targeted value' =>
                ['ValueNot', 'NZ', 'NZ', false],
            'ValueNot with non-targeted value' =>
                ['ValueNot', 'NZ', 'UK', true],
            'ValueLessThan with value below target' =>
                ['ValueLessThan', '0', '-0.00001', true],
            'ValueLessThan with value equal to target' =>
                ['ValueLessThan', '0', '0', false],
            'ValueLessThan with value greater to target' =>
                ['ValueLessThan', '0', '0.0001', false],
            'ValueLessThanEqual with value below target' =>
                ['ValueLessThanEqual', '0', '-0.00001', true],
            'ValueLessThanEqual with value equal to target' =>
                ['ValueLessThanEqual', '0', '0', true],
            'ValueLessThanEqual with value greater to target' =>
                ['ValueLessThanEqual', '0', '0.0001', false],
            'ValueGreaterThan with value below target' =>
                ['ValueGreaterThan', '0', '-0.00001', false],
            'ValueGreaterThan with value equal to target' =>
                ['ValueGreaterThan', '0', '0', false],
            'ValueGreaterThan with value greater to target' =>
                ['ValueGreaterThan', '0', '0.0001', true],
            'ValueGreaterThanEqual with value below target' =>
                ['ValueGreaterThanEqual', '0', '-0.00001', false],
            'ValueGreaterThanEqual with value equal to target' =>
                ['ValueGreaterThanEqual', '0', '0', true],
            'ValueGreaterThanEqual with value greater to target' =>
                ['ValueGreaterThanEqual', '0', '0.0001', true],
        ];
    }

    /**
     * Test that methods are returned for manipulating the presence of the "hide" CSS class depending
     * on whether the field should be hidden or shown
     */
    #[DataProvider('dataProviderValidateAgainstFormData')]
    public function testValidateAgainstFormData($condition, $targetValue, $value, $expected)
    {
        $rule1 = $this->objFromFixture(EditableCustomRule::class, 'rule1');
        $rule1->ConditionOption = $condition;
        $rule1->FieldValue = $targetValue;

        $this->assertFalse(
            $rule1->validateAgainstFormData([]),
            'Unset value always returns false no matter the rule'
        );

        $this->assertEquals(
            $expected,
            $rule1->validateAgainstFormData(['CountrySelection' => $value])
        );
    }

    public static function dataProviderCheckboxGroup()
    {
        return [
            'HasValue, option ticked' => ['HasValue', ['Phone' => 'Phone', 'Email' => 'Email'], true],
            'HasValue, other option ticked' => ['HasValue', ['Email' => 'Email'], false],
            'HasValue, nothing ticked' => ['HasValue', null, false],
            'ValueNot, option ticked' => ['ValueNot', ['Phone' => 'Phone'], false],
            'ValueNot, other option ticked' => ['ValueNot', ['Email' => 'Email'], true],
            'ValueNot, nothing ticked' => ['ValueNot', null, true],
            'IsNotBlank, an option ticked' => ['IsNotBlank', ['Email' => 'Email'], true],
            'IsNotBlank, nothing ticked' => ['IsNotBlank', null, false],
            'IsBlank, nothing ticked' => ['IsBlank', null, true],
            'IsBlank, an option ticked' => ['IsBlank', ['Email' => 'Email'], false],
        ];
    }

    /**
     * A checkbox group posts an array of ticked values, or nothing; the rule must be judged as the
     * browser judges it, by the ticked options
     */
    #[DataProvider('dataProviderCheckboxGroup')]
    public function testValidateAgainstCheckboxGroup($condition, $ticked, $expected)
    {
        $rule = $this->objFromFixture(EditableCustomRule::class, 'groupRule');
        $rule->ConditionOption = $condition;
        $data = $ticked === null ? [] : ['ContactBy' => $ticked];

        $this->assertSame($expected, $rule->validateAgainstFormData($data));
    }

    public static function dataProviderCheckbox()
    {
        return [
            'HasValue, ticked' => ['HasValue', '1', true],
            'HasValue, unticked' => ['HasValue', null, false],
            'ValueNot, ticked' => ['ValueNot', '1', false],
            'ValueNot, unticked' => ['ValueNot', null, true],
            'IsNotBlank, ticked' => ['IsNotBlank', '1', true],
            'IsBlank, unticked' => ['IsBlank', null, true],
            'IsBlank, ticked' => ['IsBlank', '1', false],
        ];
    }

    /**
     * A single checkbox posts nothing when unticked, and the browser's "has value" means ticked
     * whatever the rule's value
     */
    #[DataProvider('dataProviderCheckbox')]
    public function testValidateAgainstCheckbox($condition, $ticked, $expected)
    {
        $rule = $this->objFromFixture(EditableCustomRule::class, 'checkboxRule');
        $rule->ConditionOption = $condition;
        $data = $ticked === null ? [] : ['Agree' => $ticked];

        $this->assertSame($expected, $rule->validateAgainstFormData($data));
    }

    public function testFieldShownByACheckboxGroupIsDisplayed()
    {
        $phone = $this->objFromFixture(EditableTextField::class, 'phoneField');

        $this->assertTrue($phone->isDisplayed(['ContactBy' => ['Phone' => 'Phone']]));
        $this->assertFalse($phone->isDisplayed(['ContactBy' => ['Email' => 'Email']]));
        $this->assertFalse($phone->isDisplayed([]));
    }

    public function testValidateAgainstFormDataWithNonSenseRule()
    {
        $this->expectException(\LogicException::class);
        $rule1 = $this->objFromFixture(EditableCustomRule::class, 'rule1');
        $rule1->ConditionOption = 'NonSenseRule';
        $rule1->validateAgainstFormData(['CountrySelection' => 'booya']);
    }
}
