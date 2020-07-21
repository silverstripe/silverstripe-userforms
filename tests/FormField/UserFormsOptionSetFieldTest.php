<?php

namespace SilverStripe\UserForms\Tests\FormField;

use SilverStripe\Dev\SapphireTest;
use SilverStripe\UserForms\FormField\UserFormsOptionSetField;
use SilverStripe\UserForms\Model\EditableFormField\EditableRadioField;

class UserFormsOptionSetFieldTest extends SapphireTest
{
    protected static $fixture_file = '../UserFormsTest.yml';

    public function testCustomErrorMessageValidationAttributesHTML()
    {
        /** @var UserFormsOptionSetField $userFormsOptionSetField */
        $radio = $this->objFromFixture(EditableRadioField::class, 'radio-field');
        $radio->Required = true;
        $radio->CustomErrorMessage = 'My custom error message with \'single\' and "double" quotes';
        $userFormsOptionSetField = $radio->getFormField();
        $html = $userFormsOptionSetField->renderWith(UserFormsOptionSetField::class)->getValue();
        $attributesHTML = 'data-rule-required="true" data-msg-required="My custom error message with &amp;#039;single&amp;#039; and &amp;quot;double&amp;quot; quotes"';
        $this->assertTrue(strpos($html, $attributesHTML) > 0);
    }
}
