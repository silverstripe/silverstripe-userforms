<?php

namespace SilverStripe\UserForms\Tests\Form;

use SilverStripe\CMS\Controllers\ModelAsController;
use SilverStripe\Dev\Debug;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\UserForms\Form\UserFormsRequiredFields;
use SilverStripe\UserForms\Model\UserDefinedForm;
use SilverStripe\UserForms\Form\UserForm;

class UserFormsRequiredFieldsTest extends SapphireTest
{
    protected static $fixture_file = '../UserFormsTest.yml';

    private function getValidatorFromPage($page)
    {
        $controller = ModelAsController::controller_for($page);
        $form = new UserForm($controller);
        return $form->getValidator();
    }

    public function testUsesUserFormsRequiredFieldsValidator()
    {
        $page = $this->objFromFixture(UserDefinedForm::class, 'required-custom-rules-form');
        $this->assertEquals(3, $page->Fields()->count());
        $validator = $this->getValidatorFromPage($page);
        $this->assertNotNull($validator);
        $this->assertInstanceOf(UserFormsRequiredFields::class, $validator, 'Uses UserFormsRequiredFields validator');
    }

    public function testValidationOfConditionalRequiredFields()
    {
        $page = $this->objFromFixture(UserDefinedForm::class, 'required-custom-rules-form');
        $validator = $this->getValidatorFromPage($page);
        $this->assertNotNull($validator);

        $this->assertFalse(
            $validator->php([]),
            'Fails when non-conditional required field is empty'
        );

        $this->assertTrue(
            $validator->php(
                [
                    'required-text-field-2'     => 'some text',
                    'radio-option-2'            => 'N',
                    'conditional-required-text' => ''
                ]
            ),
            'Passes when non-conditional required field has a value'
        );

        $this->assertFalse(
            $validator->php(
                [
                    'required-text-field-2'     => 'some text',
                    'radio-option-2'            => 'Y',
                    'conditional-required-text' => ''
                ]
            ),
            'Fails when conditional required is displayed but not completed'
        );

        $this->assertTrue(
            $validator->php(
                [
                    'required-text-field-2'     => 'some text',
                    'radio-option-2'            => 'Y',
                    'conditional-required-text' => 'some more text'
                ]
            ),
            'Passes when conditional required field has a value'
        );
    }

    public function testValidationOfNestedConditionalRequiredFields()
    {
        $page = $this->objFromFixture(UserDefinedForm::class, 'required-nested-custom-rules-form');
        $this->assertEquals(4, $page->Fields()->count());
        $validator = $this->getValidatorFromPage($page);
        $this->assertNotNull($validator);

        $this->assertFalse(
            $validator->php([]),
            'Fails when non-conditional required field is empty'
        );

        $this->assertTrue(
            $validator->php(
                [
                    'required-text-field-3'       => 'some text',
                    'radio-option-2'              => 'N',
                    'conditional-required-text-2' => '',
                    'conditional-required-text-3' => ''
                ]
            ),
            'Passes when non-conditional required field has a value'
        );

        $this->assertFalse(
            $validator->php(
                [
                    'required-text-field-3'       => 'some text',
                    'radio-option-2'              => 'Y',
                    'conditional-required-text-2' => '',
                    'conditional-required-text-3' => ''
                ]
            ),
            'Fails when conditional required is displayed but not completed'
        );

        $this->assertTrue(
            $validator->php(
                [
                    'required-text-field-3'       => 'some text',
                    'radio-option-3'              => 'Y',
                    'conditional-required-text-2' => 'this text',
                    'conditional-required-text-3' => ''
                ]
            ),
            'Passes when non-conditional required field has a value'
        );

        $this->assertFalse(
            $validator->php(
                [
                    'required-text-field-3'       => 'some text',
                    'radio-option-3'              => 'Y',
                    'conditional-required-text-2' => 'Show more',
                    'conditional-required-text-3' => ''
                ]
            ),
            'Fails when nested conditional required is displayed but not completed'
        );

        $this->assertTrue(
            $validator->php(
                [
                    'required-text-field-3'       => 'some text',
                    'radio-option-3'              => 'Y',
                    'conditional-required-text-2' => 'Show more',
                    'conditional-required-text-3' => 'more text'
                ]
            ),
            'Passes when nested conditional required field has a value'
        );
    }
}
