<?php

namespace SilverStripe\UserForms\Tests\Model;

use SilverStripe\Dev\SapphireTest;
use SilverStripe\UserForms\Model\EditableCustomRule;

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
        $this->assertContains('==', $result1['operation']);

        /** @var EditableCustomRule $rule2 */
        $rule2 = $this->objFromFixture(EditableCustomRule::class, 'rule2');
        $result2 = $rule2->buildExpression();

        //TextField expect change event
        $this->assertEquals('keyup', $result2['event']);
        $this->assertNotEmpty($result2['operation']);

        //Check for greater than sign
        $this->assertContains('>', $result2['operation']);
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

    public function dataProviderValidateAgainstFormData()
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
     * @dataProvider dataProviderValidateAgainstFormData
     */
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

    /**
     * @expectedException LogicException
     */
    public function testValidateAgainstFormDataWithNonSenseRule()
    {
        $rule1 = $this->objFromFixture(EditableCustomRule::class, 'rule1');
        $rule1->ConditionOption = 'NonSenseRule';
        $rule1->validateAgainstFormData(['CountrySelection' => 'booya']);
    }
}
