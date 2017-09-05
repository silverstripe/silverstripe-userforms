<?php

namespace SilverStripe\UserForms\Tests\Model\Recipient;

use SilverStripe\Dev\SapphireTest;
use SilverStripe\UserForms\Model\Recipient\EmailRecipientCondition;

/**
 * Class EditableCustomRulesTest
 */
class EmailRecipientConditionTest extends SapphireTest
{
    protected static $fixture_file = 'EmailRecipientConditionTest.yml';

    /**
     * Various matching tests
     */
    public function testMatches()
    {
        $fixtureClass = EmailRecipientCondition::class;

        //Test Blank
        $blankObj = $this->objFromFixture($fixtureClass, 'blankTest');
        $this->assertTrue($blankObj->matches(['Name' => null]));
        $this->assertFalse($blankObj->matches(['Name' => 'Jane']));

        //Test IsNotBlank
        $blankObj = $this->objFromFixture($fixtureClass, 'isNotBlankTest');
        $this->assertTrue($blankObj->matches(['Name' => 'Jane']));
        $this->assertFalse($blankObj->matches(['Name' => null]));

        //Test ValueLessthan
        $blankObj = $this->objFromFixture($fixtureClass, 'valueLessThanTest');
        $this->assertTrue($blankObj->matches(['Age' => 17]));
        $this->assertFalse($blankObj->matches(['Age' => 19]));

        //Test ValueLessThanEquals
        $blankObj = $this->objFromFixture($fixtureClass, 'valueLessThanEqualTest');
        $this->assertTrue($blankObj->matches(['Age' => 18]));
        $this->assertFalse($blankObj->matches(['Age' => 19]));

        //Test ValueGreaterThan
        $blankObj = $this->objFromFixture($fixtureClass, 'valueGreaterThanTest');
        $this->assertTrue($blankObj->matches(['Age' => 19]));
        $this->assertFalse($blankObj->matches(['Age' => 17]));

        //Test ValueGreaterThanEquals
        $blankObj = $this->objFromFixture($fixtureClass, 'valueGreaterThanEqualTest');
        $this->assertTrue($blankObj->matches(['Age' => 18]));
        $this->assertFalse($blankObj->matches(['Age' => 17]));

        //Test Equals
        $blankObj = $this->objFromFixture($fixtureClass, 'equalsTest');
        $this->assertTrue($blankObj->matches(['Age' => 18]));
        $this->assertFalse($blankObj->matches(['Age' => 17]));

        //Test NotEquals
        $blankObj = $this->objFromFixture($fixtureClass, 'notEqualsTest');
        $this->assertTrue($blankObj->matches(['Age' => 17]));
        $this->assertFalse($blankObj->matches(['Age' => 18]));
    }
}
