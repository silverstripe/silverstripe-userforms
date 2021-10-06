<?php

namespace SilverStripe\UserForms\Tests\Extension;

use SilverStripe\Dev\SapphireTest;
use SilverStripe\UserForms\Model\EditableFormField\EditableEmailField;
use SilverStripe\UserForms\Model\UserDefinedForm;
use SilverStripe\Versioned\Versioned;

class UserFormFieldEditorExtensionTest extends SapphireTest
{
    protected static $fixture_file = 'UserFormFieldEditorExtensionTest.yml';

    protected static $extra_dataobjects = [
        UserFormBlockStub::class,
    ];

    protected function setUp()
    {
        parent::setUp();
        $page = $this->objFromFixture(UserDefinedForm::class, 'page');
        $block = $this->objFromFixture(UserFormBlockStub::class, 'block');
        $page->publishRecursive();
        $block->publishRecursive();
    }

    public function testOrphanRemovalDoesNotAffectOtherClassesWithTheSameID()
    {
        $page = $this->objFromFixture(UserDefinedForm::class, 'page');
        $block = $this->objFromFixture(UserFormBlockStub::class, 'block');

        // assert setup
        $this->assertSame($page->ID, $block->ID);
        $this->assertCount(2, $page->Fields(), 'Draft UserForm page starts with 2 fields');
        $this->assertCount(3, $block->Fields(), 'Draft UserForm block starts with 3 fields');

        // ensure setup has affected live mode too
        $origReadingMode = Versioned::get_reading_mode();
        Versioned::withVersionedMode(function () {
            Versioned::set_reading_mode(Versioned::LIVE);
            $initialLivePage = UserDefinedForm::get()->First();
            $initialLiveBlock = UserFormBlockStub::get()->First();

            $this->assertSame($initialLivePage->ID, $initialLiveBlock->ID);
            $this->assertCount(2, $initialLivePage->Fields(), 'Live UserForm page starst with 2 fields');
            $this->assertCount(3, $initialLiveBlock->Fields(), 'Live UserForm block starst with 3 fields');
        });

        // execute change
        $newField = new EditableEmailField();
        $newField->update([
            'Name' => 'basic_email_name',
            'Title' => 'Page Email Field'
        ]);
        $page->Fields()->add($newField);
        $page->publishRecursive();

        // assert effect of change
        /** @var UserDefinedForm $checkPage */
        $checkPage = UserDefinedForm::get()->First();
        $checkBlock = UserFormBlockStub::get()->First();

        $this->assertCount(3, $checkPage->Fields(), 'Field has been added to draft user form page');
        $this->assertCount(
            3,
            $checkBlock->Fields(),
            'Draft userform block with same ID is not affected'
        );

        // ensure this is true for live mode too
        Versioned::withVersionedMode(function () {
            Versioned::set_reading_mode(Versioned::LIVE);
            $checkLivePage = UserDefinedForm::get()->First();
            $checkLiveBlock = UserFormBlockStub::get()->First();
            $this->assertCount(
                3,
                $checkLivePage->Fields(),
                'Field has been added to live user form page'
            );
            $this->assertCount(
                3,
                $checkLiveBlock->Fields(),
                'Live userform block with same ID is not affected'
            );
        });
    }
}
