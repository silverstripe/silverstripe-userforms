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
        $this->assertCount(1, $page->Fields());
        $this->assertCount(3, $block->Fields());

        // ensure setup has affected live mode too
        $origReadingMode = Versioned::get_reading_mode();
        Versioned::set_reading_mode(Versioned::LIVE);

        $initialLivePage = UserDefinedForm::get()->First();
        $initialLiveBlock = UserFormBlockStub::get()->First();

        $this->assertSame($initialLivePage->ID, $initialLiveBlock->ID);
        $this->assertCount(1, $initialLivePage->Fields());
        $this->assertCount(3, $initialLiveBlock->Fields());

        Versioned::set_reading_mode($origReadingMode);

        // execute change
        $newField = new EditableEmailField();
        $newField->update([
            'Name' => 'basic_email_name',
            'Title' => 'Page Email Field'
        ]);
        $page->Fields()->add($newField);
        $page->publishRecursive();

        // assert effect of change
        $checkPage = UserDefinedForm::get()->First();
        $checkBlock = UserFormBlockStub::get()->First();

        $this->assertCount(2, $checkPage->Fields());
        $this->assertCount(3, $checkBlock->Fields());

        // ensure this is true for live mode too
        $origReadingMode = Versioned::get_reading_mode();
        Versioned::set_reading_mode(Versioned::LIVE);

        $checkLivePage = UserDefinedForm::get()->First();
        $checkLiveBlock = UserFormBlockStub::get()->First();
        $this->assertCount(2, $checkLivePage->Fields());
        $this->assertCount(3, $checkLiveBlock->Fields());

        Versioned::set_reading_mode($origReadingMode);
    }
}
