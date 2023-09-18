<?php

namespace SilverStripe\UserForms\Tests\Model;

use SilverStripe\Core\Config\Config;
use SilverStripe\Dev\FunctionalTest;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\OptionsetField;
use SilverStripe\UserForms\Model\EditableFormField;
use SilverStripe\UserForms\Model\EditableFormField\EditableCheckbox;
use SilverStripe\UserForms\Model\EditableFormField\EditableDropdown;
use SilverStripe\UserForms\Model\EditableFormField\EditableFileField;
use SilverStripe\UserForms\Model\EditableFormField\EditableLiteralField;
use SilverStripe\UserForms\Model\EditableFormField\EditableOption;
use SilverStripe\UserForms\Model\EditableFormField\EditableRadioField;
use SilverStripe\UserForms\Model\EditableFormField\EditableTextField;
use SilverStripe\UserForms\Model\EditableCustomRule;

/**
 * @package userforms
 */
class EditableFormFieldTest extends FunctionalTest
{
    protected static $fixture_file = 'EditableFormFieldTest.yml';

    public function testFormFieldPermissions()
    {
        $text = $this->objFromFixture(EditableTextField::class, 'basic-text');

        $this->logInWithPermission('ADMIN');

        $this->assertTrue($text->canCreate());
        $this->assertTrue($text->canView());
        $this->assertTrue($text->canEdit());
        $this->assertTrue($text->canDelete());

        $text->setReadonly(true);
        $this->assertTrue($text->canView());
        $this->assertFalse($text->canEdit());
        $this->assertFalse($text->canDelete());

        $text->setReadonly(false);
        $this->assertTrue($text->canView());
        $this->assertTrue($text->canEdit());
        $this->assertTrue($text->canDelete());

        $this->logOut();
        $this->logInWithPermission('SITETREE_VIEW_ALL');

        $this->assertFalse($text->canCreate());

        $text->setReadonly(false);
        $this->assertTrue($text->canView());
        $this->assertFalse($text->canEdit());
        $this->assertFalse($text->canDelete());

        $text->setReadonly(true);
        $this->assertTrue($text->canView());
        $this->assertFalse($text->canEdit());
        $this->assertFalse($text->canDelete());
    }

    public function testEditableOptionEmptyValue()
    {
        $option = $this->objFromFixture(EditableOption::class, 'option-1');
        $option->Value = '';

        // Disallow empty values
        EditableOption::set_allow_empty_values(false);
        $this->assertEquals($option->Title, $option->Value);

        $option->Value = 'test';
        $this->assertEquals('test', $option->Value);

        // Allow empty values
        EditableOption::set_allow_empty_values(true);
        $option->Value = '';
        $this->assertEquals('', $option->Value);
    }

    public function testEditableDropdownField()
    {
        $dropdown = $this->objFromFixture(EditableDropdown::class, 'basic-dropdown');

        $field = $dropdown->getFormField();

        $this->assertThat($field, $this->isInstanceOf(DropdownField::class));
        $values = $field->getSource();

        $this->assertEquals(['Option 1' => 'Option 1', 'Option 2' => 'Option 2'], $values);
    }

    public function testEditableRadioField()
    {
        $radio = $this->objFromFixture(EditableRadioField::class, 'radio-field');

        $field = $radio->getFormField();

        $this->assertThat($field, $this->isInstanceOf(OptionsetField::class));
        $values = $field->getSource();

        $this->assertEquals(['Option 5' => 'Option 5', 'Option 6' => 'Option 6'], $values);
    }

    public function testMultipleOptionDuplication()
    {
        $dropdown = $this->objFromFixture(EditableDropdown::class, 'basic-dropdown');

        $clone = $dropdown->duplicate();

        $this->assertEquals(
            $dropdown->Options()->Count(),
            $clone->Options()->Count(),
            "The duplicate should have contain same number of options"
        );

        foreach ($clone->Options() as $option) {
            $original = $dropdown->Options()->find('Title', $option->Title);

            $this->assertEquals($original->Sort, $option->Sort);
        }
    }

    public function testFileField()
    {
        $fileField = $this->objFromFixture(EditableFileField::class, 'file-field');
        $formField = $fileField->getFormField();

        $this->assertContains('jpg', $formField->getValidator()->getAllowedExtensions());
        $this->assertNotContains('notallowedextension', $formField->getValidator()->getAllowedExtensions());
    }

    public function testFileFieldAllowedExtensionsBlacklist()
    {
        Config::modify()->merge(EditableFileField::class, 'allowed_extensions_blacklist', ['jpg']);
        $fileField = $this->objFromFixture(EditableFileField::class, 'file-field');
        $formField = $fileField->getFormField();

        $this->assertNotContains('jpg', $formField->getValidator()->getAllowedExtensions());
    }

    /**
     * Test that if folder is not set or folder was removed,
     * then getFormField() saves file in protected folder
     */
    public function testCreateProtectedFolder()
    {
        $fileField1 = $this->objFromFixture(EditableFileField::class, 'file-field-without-folder');
        $fileField2 = $this->objFromFixture(EditableFileField::class, 'file-field-with-folder');

        $fileField1->createProtectedFolder();

        $formField1 = $fileField1->getFormField();
        $formField2 = $fileField2->getFormField();

        $canViewParent1 = $fileField1->Folder()->Parent()->Parent()->CanViewType;
        $canViewParent2 = $fileField2->Folder()->Parent()->CanViewType;

        $this->assertEquals('OnlyTheseUsers', $canViewParent1);
        $this->assertEquals('Inherit', $canViewParent2);

        $this->assertTrue(
            (bool) preg_match(
                sprintf(
                    '/^Form-submissions\/page-%d\/upload-field-%d/',
                    $fileField1->ParentID,
                    $fileField1->ID
                ),
                $formField1->folderName,
            )
        );
        $this->assertEquals('folder1/folder1-1/', $formField2->folderName);
    }

    /**
     * Verify that folder is related to a field exist
     */
    public function testGetFolderExists()
    {
        $fileField1 = $this->objFromFixture(EditableFileField::class, 'file-field-without-folder');
        $fileField2 = $this->objFromFixture(EditableFileField::class, 'file-field-with-folder');
        
        $this->assertFalse($fileField1->getFolderExists());
        $this->assertTrue($fileField2->getFolderExists());
    }

    /**
     * Verify that unique names are automatically generated for each formfield
     */
    public function testUniqueName()
    {
        $textfield1 = new EditableTextField();
        $this->assertEmpty($textfield1->Name);

        // Write values
        $textfield1->write();
        $textfield2 = new EditableTextField();
        $textfield2->write();
        $checkboxField = new EditableCheckbox();
        $checkboxField->write();

        // Test values are in the expected format
        $this->assertMatchesRegularExpression('/^EditableTextField_.+/', $textfield1->Name);
        $this->assertMatchesRegularExpression('/^EditableTextField_.+/', $textfield2->Name);
        $this->assertMatchesRegularExpression('/^EditableCheckbox_.+/', $checkboxField->Name);
        $this->assertNotEquals($textfield1->Name, $textfield2->Name);
    }

    public function testLengthRange()
    {
        /** @var EditableTextField $textField */
        $textField = $this->objFromFixture(EditableTextField::class, 'basic-text');

        // Empty range
        /** @var TextField $formField */
        $textField->MinLength = 0;
        $textField->MaxLength = 0;
        $attributes = $textField->getFormField()->getAttributes();
        $this->assertFalse(isset($attributes['maxLength']));
        $this->assertFalse(isset($attributes['data-rule-minlength']));
        $this->assertFalse(isset($attributes['data-rule-maxlength']));

        // Test valid range
        $textField->MinLength = 10;
        $textField->MaxLength = 20;
        $attributes = $textField->getFormField()->getAttributes();
        $this->assertEquals(20, $attributes['maxLength']);
        $this->assertEquals(20, $attributes['size']);
        $this->assertEquals(10, $attributes['data-rule-minlength']);
        $this->assertEquals(20, $attributes['data-rule-maxlength']);

        // textarea
        $textField->Rows = 3;
        $attributes = $textField->getFormField()->getAttributes();
        $this->assertFalse(isset($attributes['maxLength']));
        $this->assertEquals(10, $attributes['data-rule-minlength']);
        $this->assertEquals(20, $attributes['data-rule-maxlength']);
    }

    public function testFormatDisplayRules()
    {
        $field = $this->objFromFixture(EditableTextField::class, 'irdNumberField');
        $displayRules = $field->formatDisplayRules();
        $this->assertNotNull($displayRules);
        $this->assertCount(1, $displayRules['operations']);

        // Field is initially visible, so the "view" method should be to hide it
        $this->assertSame('addClass("hide")', $displayRules['view']);
        // The opposite method should be to return it to its original state, i.e. show it again
        $this->assertSame('removeClass("hide")', $displayRules['opposite']);
    }

    public function testGetIcon()
    {
        $field = new EditableTextField;

        $this->assertStringContainsString('/images/editabletextfield.png', $field->getIcon());
    }

    public function displayedProvider()
    {
        $one = ['basic_text_name' => 'foobar'];
        $two = array_merge($one, ['basic_text_name_2' => 'foobar']);

        return [
            'no display rule AND' => ['alwaysVisible', [], true],
            'no display rule OR' =>  ['alwaysVisibleOr', [], true],

            'no display rule hidden AND' => ['neverVisible', [], false],
            'no display rule hidden OR' => ['neverVisibleOr', [], false],

            '1 unmet display rule AND' => ['singleDisplayRule', [], false],
            '1 met display rule AND' => ['singleDisplayRule', $one, true],
            '1 unmet display rule OR' => ['singleDisplayRuleOr', [], false],
            '1 met display rule OR' => ['singleDisplayRuleOr', $one, true],

            '1 unmet hide rule AND' => ['singleHiddingRule', [], true],
            '1 met hide rule AND' => ['singleHiddingRule', $one, false],
            '1 unmet hide rule OR' => ['singleHiddingRuleOr', [], true],
            '1 met hide rule OR' => ['singleHiddingRuleOr', $one, false],

            'multi display rule AND none met' => ['multiDisplayRule', [], false],
            'multi display rule AND partially met' => ['multiDisplayRule', $one, false],
            'multi display rule AND all met' => ['multiDisplayRule', $two, true],

            'multi display rule OR none met' => ['multiDisplayRuleOr', [], false],
            'multi display rule OR partially met' => ['multiDisplayRuleOr', $one, true],
            'multi display rule OR all met' => ['multiDisplayRuleOr', $two, true],

            'multi hide rule AND none met' => ['multiHiddingRule', [], true],
            'multi hide rule AND partially met' => ['multiHiddingRule', $one, true],
            'multi hide rule AND all met' => ['multiHiddingRule', $two, false],

            'multi hide rule OR none met' => ['multiHiddingRuleOr', [], true],
            'multi hide rule OR partially met' => ['multiHiddingRuleOr', $one, false],
            'multi hide rule OR all met' => ['multiHiddingRuleOr', $two, false],
        ];
    }

    /**
     * @param $fieldName
     * @param $data
     * @param $expected
     * @dataProvider displayedProvider
     */
    public function testIsDisplayed($fieldName, $data, $expected)
    {
        /** @var EditableFormField $field */
        $field = $this->objFromFixture(EditableTextField::class, $fieldName);
        $this->assertEquals($expected, $field->isDisplayed($data));
    }

    public function testChangingDataFieldTypeToDatalessRemovesRequiredSetting()
    {
        $requiredTextField = $this->objFromFixture(EditableTextField::class, 'required-text');
        $fieldId = $requiredTextField->ID;
        $this->assertTrue((bool)$requiredTextField->Required);

        $literalField = $requiredTextField->newClassInstance(EditableLiteralField::class);
        $this->assertTrue((bool)$literalField->Required);

        $literalField->write();
        $this->assertFalse((bool)$literalField->Required);

        $updatedField = EditableFormField::get()->byId($fieldId);
        $this->assertFalse((bool)$updatedField->Required);
    }

    public function testRecursionProtection()
    {
        $radioOne = EditableRadioField::create();
        $radioOneID = $radioOne->write();
        $optionOneOne = EditableOption::create();
        $optionOneOne->Value = 'yes';
        $optionOneOne->ParentID = $radioOneID;
        $optionOneTwo = EditableOption::create();
        $optionOneTwo->Value = 'no';
        $optionOneTwo->ParentID = $radioOneID;

        $radioTwo = EditableRadioField::create();
        $radioTwoID = $radioTwo->write();
        $optionTwoOne = EditableOption::create();
        $optionTwoOne->Value = 'yes';
        $optionTwoOne->ParentID = $radioOneID;
        $optionTwoTwo = EditableOption::create();
        $optionTwoTwo->Value = 'no';
        $optionTwoTwo->ParentID = $radioTwoID;

        $conditionOne = EditableCustomRule::create();
        $conditionOne->ParentID = $radioOneID;
        $conditionOne->ConditionFieldID = $radioTwoID;
        $conditionOne->ConditionOption = 'HasValue';
        $conditionOne->FieldValue = 'yes';
        $conditionOne->write();
        $radioOne->DisplayRules()->add($conditionOne);

        $conditionTwo = EditableCustomRule::create();
        $conditionTwo->ParentID = $radioTwoID;
        $conditionTwo->ConditionFieldID = $radioOneID;
        $conditionTwo->ConditionOption = 'IsNotBlank';
        $conditionTwo->write();
        $radioTwo->DisplayRules()->add($conditionTwo);

        $testField = new class extends EditableFormField
        {
            public function countIsDisplayedRecursionProtection(int $fieldID)
            {
                return count(array_filter(static::$isDisplayedRecursionProtection, function ($id) use ($fieldID) {
                    return $id === $fieldID;
                }));
            }
        };

        $this->assertSame(0, $testField->countIsDisplayedRecursionProtection($radioOneID));
        $radioOne->isDisplayed([]);
        $this->assertSame(100, $testField->countIsDisplayedRecursionProtection($radioOneID));
    }
}
