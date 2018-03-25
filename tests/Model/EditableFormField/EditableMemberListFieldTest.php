<?php

namespace SilverStripe\UserForms\Tests\Model\EditableFormField;

use SilverStripe\Dev\SapphireTest;
use SilverStripe\Security\Group;
use SilverStripe\UserForms\Model\EditableFormField\EditableMemberListField;

class EditableMemberListFieldTest extends SapphireTest
{
    protected static $fixture_file = 'EditableMemberListFieldTest.yml';

    public function testAllowEmptyTitle()
    {
        /** @var EditableMemberListField $field */
        $field = EditableMemberListField::create();
        $field->GroupID = $this->idFromFixture(Group::class, 'a_group');
        $field->Name = 'EditableFormField_123456';
        $this->assertEmpty($field->getFormField()->Title());
    }
}
