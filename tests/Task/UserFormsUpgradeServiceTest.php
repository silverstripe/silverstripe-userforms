<?php

class UserFormsUpgradeServiceTest extends SapphireTest
{

    public static $fixture_file = 'UserFormsUpgradeServiceTest.yml';

    public function setUp()
    {
        Config::inst()->update('UserDefinedForm', 'upgrade_on_build', false);
        parent::setUp();

        // Assign rules programatically
        $field1 = $this->objFromFixture('EditableTextField', 'text1');
        $field2 = $this->objFromFixture('EditableTextField', 'text2');
        $field3 = $this->objFromFixture('EditableTextField', 'text3');

        $field3->CustomRules = serialize(array(
            array(
                'Display' => 'Show',
                'ConditionField' => $field1->Name,
                'ConditionOption' => 'IsBlank',
                'Value' => ''
            ),
            array(
                'Display' => 'Hide',
                'ConditionField' => $field2->Name,
                'ConditionOption' => 'HasValue',
                'Value' => 'bob'
            )
        ));
        $field3->write();

        // Assign settings programatically
        $field4 = $this->objFromFixture('EditableTextField', 'text4');
        $field4->CustomSettings = serialize(array(
            'MinLength' => 20,
            'MaxLength' => 100,
            'Rows' => 4,
            'ExtraClass' => 'special class',
            'RightTitle' => 'My Field',
            'ShowOnLoad' => '',
            'Default' => 'Enter your text here'
        ));
        $field4->write();

        $numeric1 = $this->objFromFixture('EditableNumericField', 'numeric1');
        $numeric1->CustomSettings = serialize(array(
            'RightTitle' => 'Number of %',
            'Default' => 1,
            'MinValue' => 1,
            'MaxValue' => 100,
            'ShowOnLoad' => 'Show'
        ));
        $numeric1->write();

        $group1 = $this->objFromFixture('Group', 'group1');
        $members1 = $this->objFromFixture('EditableMemberListField', 'members1');
        $members1->CustomSettings = serialize(array(
            'RightTitle' => 'Select group',
            'GroupID' => $group1->ID,
            'ShowOnLoad' => 'Hide'
        ));
        $members1->write();

        $literal1 = $this->objFromFixture('EditableLiteralField', 'literal1');
        $literal1->CustomSettings = serialize(array(
            'HideFromReports' => 1,
            'RightTitle' => 'Literal',
            'Content' => '<p>Content</p>',
            'ShowOnLoad' => true
        ));
        $literal1->write();

        $heading1 = $this->objFromFixture('EditableFormHeading', 'heading1');
        $heading1->CustomSettings = serialize(array(
            'RightTitle' => 'Right',
            'Level' => 3,
            'HideFromReports' => true,
            'ShowOnLoad' => false
        ));
        $heading1->write();

        $folder = $this->objFromFixture('Folder', 'folder1');
        $file1 = $this->objFromFixture('EditableFileField', 'file1');
        $file1->CustomSettings = serialize(array(
            'RightTitle' => 'File field',
            'Folder' => $folder->ID
        ));
        $file1->write();

        $date1 = $this->objFromFixture('EditableDateField', 'date1');
        $date1->CustomSettings = serialize(array(
            'RightTitle' => 'Date field',
            'DefaultToToday' => '1'
        ));
        $date1->write();

        $checkbox1 = $this->objFromFixture('EditableCheckbox', 'checkbox1');
        $checkbox1->CustomSettings = serialize(array(
            'Default' => true,
            'RightTitle' => 'Check this'
        ));
        $checkbox1->write();
    }

    /**
     * @return UserFormsUpgradeService;
     */
    protected function getService()
    {
        return singleton('UserFormsUpgradeService');
    }

    /**
     * Tests migration of custom rules
     */
    public function testCustomRulesMigration()
    {
        $service = $this->getService();
        $service->setQuiet(true);
        $service->run();

        $field1 = $this->objFromFixture('EditableTextField', 'text1');
        $field2 = $this->objFromFixture('EditableTextField', 'text2');
        $field3 = $this->objFromFixture('EditableTextField', 'text3');

        $this->assertDOSEquals(array(
            array(
                'Display' => 'Show',
                'ConditionFieldID' => $field1->ID,
                'ConditionOption' => 'IsBlank'
            ),
            array(
                'Display' => 'Hide',
                'ConditionFieldID' => $field2->ID,
                'ConditionOption' => 'HasValue',
                'FieldValue' => 'bob'
            )
        ), $field3->DisplayRules());
    }

    /**
     * Tests migration of all custom settings
     */
    public function testCustomSettingsMigration()
    {
        $service = $this->getService();
        $service->setQuiet(true);
        $service->run();

        $group1 = $this->objFromFixture('Group', 'group1');
        $form = $this->objFromFixture('UserDefinedForm', 'form-with-settings');
        $folder = $this->objFromFixture('Folder', 'folder1');

        $this->assertDOSEquals(array(
            array(
                'ClassName' => 'EditableTextField',
                'Title' => 'Text with rule',
                'MinLength' => 20,
                'MaxLength' => 100,
                'Rows' => 4,
                'ExtraClass' => 'special class',
                'RightTitle' => 'My Field',
                'ShowOnLoad' => true,
                'Default' => 'Enter your text here',
            ),
            array(
                'ClassName' => 'EditableNumericField',
                'Title' => 'Numeric 1',
                'RightTitle' => 'Number of %',
                'Default' => 1,
                'MinValue' => 1,
                'MaxValue' => 100,
                'ShowOnLoad' => true,
            ),
            array(
                'ClassName' => 'EditableMemberListField',
                'Title' => 'Members 1',
                'RightTitle' => 'Select group',
                'GroupID' => $group1->ID,
                'ShowOnLoad' => false,
            ),
            array(
                'ClassName' => 'EditableLiteralField',
                'Title' => 'Literal 1',
                'HideFromReports' => true,
                'RightTitle' => 'Literal',
                'Content' => '<p>Content</p>',
                'ShowOnLoad' => true,
            ),
            array(
                'ClassName' => 'EditableFormHeading',
                'Title' => 'Heading 1',
                'RightTitle' => 'Right',
                'Level' => 3,
                'HideFromReports' => true,
                'ShowOnLoad' => false,
            ),
            array(
                'ClassName' => 'EditableFileField',
                'Title' => 'File 1',
                'RightTitle' => 'File field',
                'FolderID' => $folder->ID,
            ),
            array(
                'ClassName' => 'EditableDateField',
                'Title' => 'Date 1',
                'RightTitle' => 'Date field',
                'DefaultToToday' => true,
            ),
            array(
                'ClassName' => 'EditableCheckbox',
                'Title' => 'Checkbox 1',
                'CheckedDefault' => true,
                'RightTitle' => 'Check this',
            ),
        ), $form->Fields());
    }
}
