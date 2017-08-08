<?php

namespace SilverStripe\UserForms\Test\Model\Recipient;


use SilverStripe\UserForms\Model\Recipient\UserDefinedForm_EmailRecipientCondition;
use SilverStripe\Dev\SapphireTest;



/**
 * Class EditableCustomRulesTest
 */
class UserDefinedForm_EmailRecipientConditionTest extends SapphireTest
{
    protected static $fixture_file = 'userforms/tests/UserDefinedForm_EmailRecipientConditionTest.yml';

    /**
     * Various matching tests
     */
    public function testMatches()
    {
        $fixtureClass = UserDefinedForm_EmailRecipientCondition::class;
        //Test Blank
        /** @var UserDefinedForm_EmailRecipientCondition $blankObj */
        $blankObj = $this->objFromFixture($fixtureClass, 'blankTest');
        $this->assertTrue($blankObj->matches(array('Name' => null)));
        $this->assertFalse($blankObj->matches(array('Name' => 'Jane')));

        //Test IsNotBlank
        /** @var UserDefinedForm_EmailRecipientCondition $blankObj */
        $blankObj = $this->objFromFixture($fixtureClass, 'isNotBlankTest');
        $this->assertTrue($blankObj->matches(array('Name' => 'Jane')));
        $this->assertFalse($blankObj->matches(array('Name' => null)));

        //Test ValueLessthan
        /** @var UserDefinedForm_EmailRecipientCondition $blankObj */
        $blankObj = $this->objFromFixture($fixtureClass, 'valueLessThanTest');
        $this->assertTrue($blankObj->matches(array('Age' => 17)));
        $this->assertFalse($blankObj->matches(array('Age' => 19)));

        //Test ValueLessThanEquals
        /** @var UserDefinedForm_EmailRecipientCondition $blankObj */
        $blankObj = $this->objFromFixture($fixtureClass, 'valueLessThanEqualTest');
        $this->assertTrue($blankObj->matches(array('Age' => 18)));
        $this->assertFalse($blankObj->matches(array('Age' => 19)));

        //Test ValueGreaterThan
        /** @var UserDefinedForm_EmailRecipientCondition $blankObj */
        $blankObj = $this->objFromFixture($fixtureClass, 'valueGreaterThanTest');
        $this->assertTrue($blankObj->matches(array('Age' => 19)));
        $this->assertFalse($blankObj->matches(array('Age' => 17)));

        //Test ValueGreaterThanEquals
        /** @var UserDefinedForm_EmailRecipientCondition $blankObj */
        $blankObj = $this->objFromFixture($fixtureClass, 'valueGreaterThanEqualTest');
        $this->assertTrue($blankObj->matches(array('Age' => 18)));
        $this->assertFalse($blankObj->matches(array('Age' => 17)));

        //Test Equals
        /** @var UserDefinedForm_EmailRecipientCondition $blankObj */
        $blankObj = $this->objFromFixture($fixtureClass, 'equalsTest');
        $this->assertTrue($blankObj->matches(array('Age' => 18)));
        $this->assertFalse($blankObj->matches(array('Age' => 17)));

        //Test NotEquals
        /** @var UserDefinedForm_EmailRecipientCondition $blankObj */
        $blankObj = $this->objFromFixture($fixtureClass, 'notEqualsTest');
        $this->assertTrue($blankObj->matches(array('Age' => 17)));
        $this->assertFalse($blankObj->matches(array('Age' => 18)));
    }
}
