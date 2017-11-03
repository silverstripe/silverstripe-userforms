<?php

namespace SilverStripe\UserForms\Tests\Model\EditableFormField;

use SilverStripe\Dev\SapphireTest;
use SilverStripe\UserForms\Model\EditableFormField\EditableCountryDropdownField;

class EditableCountryDropdownFieldTest extends SapphireTest
{
    public function testGetIcon()
    {
        $field = new EditableCountryDropdownField;

        $this->assertContains('/images/editabledropdown.png', $field->getIcon());
    }
}
