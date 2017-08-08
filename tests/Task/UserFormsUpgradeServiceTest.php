<?php

namespace SilverStripe\UserForms\Test\Task;



use SilverStripe\Core\Config\Config;
use SilverStripe\UserForms\Model\UserDefinedForm;
use SilverStripe\UserForms\Model\EditableFormField\EditableTextField;
use SilverStripe\UserForms\Model\EditableFormField\EditableNumericField;
use SilverStripe\Security\Group;
use SilverStripe\UserForms\Model\EditableFormField\EditableMemberListField;
use SilverStripe\UserForms\Model\EditableFormField\EditableLiteralField;
use SilverStripe\UserForms\Model\EditableFormField\EditableFormHeading;
use SilverStripe\Assets\Folder;
use SilverStripe\UserForms\Model\EditableFormField\EditableFileField;
use SilverStripe\UserForms\Model\EditableFormField\EditableDateField;
use SilverStripe\UserForms\Model\EditableFormField\EditableCheckbox;
use SilverStripe\UserForms\Task\UserFormsUpgradeService;
use SilverStripe\Dev\SapphireTest;



class UserFormsUpgradeServiceTest extends SapphireTest
{

    public static $fixture_file = 'UserFormsUpgradeServiceTest.yml';

    public function setUp()
    {
        Config::inst()->update(UserDefinedForm::class, 'upgrade_on_build', false);
        parent::setUp();

        // Assign rules programatically
        $field1 = $this->objFromFixture(EditableTextField::class, 'text1');
        $field2 = $this->objFromFixture(EditableTextField::class, 'text2');
        $field3 = $this->objFromFixture(EditableTextField::class, 'text3');

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
        $field4 = $this->objFromFixture(EditableTextField::class, 'text4');
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

        $numeric1 = $this->objFromFixture(EditableNumericField::class, 'numeric1');
        $numeric1->CustomSettings = serialize(array(
            'RightTitle' => 'Number of %',
            'Default' => 1,
            'MinValue' => 1,
            'MaxValue' => 100,
            'ShowOnLoad' => 'Show'
        ));
        $numeric1->write();

        $group1 = $this->objFromFixture(Group::class, 'group1');
        $members1 = $this->objFromFixture(EditableMemberListField::class, 'members1');
        $members1->CustomSettings = serialize(array(
            'RightTitle' => 'Select group',
            'GroupID' => $group1->ID,
            'ShowOnLoad' => 'Hide'
        ));
        $members1->write();

        $literal1 = $this->objFromFixture(EditableLiteralField::class, 'literal1');
        $literal1->CustomSettings = serialize(array(
            'HideFromReports' => 1,
            'RightTitle' => 'Literal',
            'Content' => '<p>Content</p>',
            'ShowOnLoad' => true
        ));
        $literal1->write();

        $heading1 = $this->objFromFixture(EditableFormHeading::class, 'heading1');
        $heading1->CustomSettings = serialize(array(
            'RightTitle' => 'Right',
            'Level' => 3,
            'HideFromReports' => true,
            'ShowOnLoad' => false
        ));
        $heading1->write();

        $folder = $this->objFromFixture(Folder::class, 'folder1');
        $file1 = $this->objFromFixture(EditableFileField::class, 'file1');
        $file1->CustomSettings = serialize(array(
            'RightTitle' => 'File field',
            'Folder' => $folder->ID
        ));
        $file1->write();

        $date1 = $this->objFromFixture(EditableDateField::class, 'date1');
        $date1->CustomSettings = serialize(array(
            'RightTitle' => 'Date field',
            'DefaultToToday' => '1'
        ));
        $date1->write();

        $checkbox1 = $this->objFromFixture(EditableCheckbox::class, 'checkbox1');
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
        return singleton(UserFormsUpgradeService::class);
    }

    /**
     * Tests migration of custom rules
     */
    public function testCustomRulesMigration()
    {
        $service = $this->getService();
        $service->setQuiet(true);
        $service->run();

        $field1 = $this->objFromFixture(EditableTextField::class, 'text1');
        $field2 = $this->objFromFixture(EditableTextField::class, 'text2');
        $field3 = $this->objFromFixture(EditableTextField::class, 'text3');

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

        $group1 = $this->objFromFixture(Group::class, 'group1');
        $form = $this->objFromFixture(UserDefinedForm::class, 'form-with-settings');
        $folder = $this->objFromFixture(Folder::class, 'folder1');

        $this->assertDOSEquals(array(
            array(
                'ClassName' => EditableTextField::class,
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
                'ClassName' => EditableNumericField::class,
                'Title' => 'Numeric 1',
                'RightTitle' => 'Number of %',
                'Default' => 1,
                'MinValue' => 1,
                'MaxValue' => 100,
                'ShowOnLoad' => true,
            ),
            array(
                'ClassName' => EditableMemberListField::class,
                'Title' => 'Members 1',
                'RightTitle' => 'Select group',
                'GroupID' => $group1->ID,
                'ShowOnLoad' => false,
            ),
            array(
                'ClassName' => EditableLiteralField::class,
                'Title' => 'Literal 1',
                'HideFromReports' => true,
                'RightTitle' => 'Literal',
                'Content' => '<p>Content</p>',
                'ShowOnLoad' => true,
            ),
            array(
                'ClassName' => EditableFormHeading::class,
                'Title' => 'Heading 1',
                'RightTitle' => 'Right',
                'Level' => 3,
                'HideFromReports' => true,
                'ShowOnLoad' => false,
            ),
            array(
                'ClassName' => EditableFileField::class,
                'Title' => 'File 1',
                'RightTitle' => 'File field',
                'FolderID' => $folder->ID,
            ),
            array(
                'ClassName' => EditableDateField::class,
                'Title' => 'Date 1',
                'RightTitle' => 'Date field',
                'DefaultToToday' => true,
            ),
            array(
                'ClassName' => EditableCheckbox::class,
                'Title' => 'Checkbox 1',
                'CheckedDefault' => true,
                'RightTitle' => 'Check this',
            ),
        ), $form->Fields());
    }
}
