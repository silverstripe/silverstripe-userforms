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
use SilverStripe\UserForms\Model\EditableFormField\EditableOption;
use SilverStripe\UserForms\Model\EditableFormField\EditableRadioField;
use SilverStripe\UserForms\Model\EditableFormField\EditableTextField;
use SilverStripe\UserForms\Model\UserDefinedForm;

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

    public function testCustomRules()
    {
        $this->logInWithPermission('ADMIN');
        $form = $this->objFromFixture(UserDefinedForm::class, 'custom-rules-form');

        $checkbox = $form->Fields()->find('ClassName', EditableCheckbox::class);
        $field = $form->Fields()->find('ClassName', EditableTextField::class);

        $rules = $checkbox->DisplayRules();

        // form has 2 fields - a checkbox and a text field
        // it has 1 rule - when ticked the checkbox hides the text field
        $this->assertEquals(1, $rules->Count());
        $this->assertEquals($rules, $checkbox->EffectiveDisplayRules());

        $checkboxRule = $rules->First();
        $checkboxRule->ConditionFieldID = $field->ID;

        $this->assertEquals($checkboxRule->Display, 'Hide');
        $this->assertEquals($checkboxRule->ConditionOption, 'HasValue');
        $this->assertEquals($checkboxRule->FieldValue, '6');

        // If field is required then all custom rules are disabled
        $checkbox->Required = true;
        $this->assertEquals(0, $checkbox->EffectiveDisplayRules()->count());
    }

    /**
     * @covers EditableOption::getValue
     */
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
        $this->assertRegExp('/^EditableTextField_.+/', $textfield1->Name);
        $this->assertRegExp('/^EditableTextField_.+/', $textfield2->Name);
        $this->assertRegExp('/^EditableCheckbox_.+/', $checkboxField->Name);
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

        $this->assertContains('/images/editabletextfield.png', $field->getIcon());
    }
}
