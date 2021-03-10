<?php

namespace SilverStripe\UserForms\Tests\Form;

use SilverStripe\CMS\Controllers\ModelAsController;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\UserForms\Model\UserDefinedForm;
use SilverStripe\UserForms\Form\UserForm;

class UserFormTest extends SapphireTest
{
    protected static $fixture_file = '../UserFormsTest.yml';

    /**
     * Tests that a form will not generate empty pages
     */
    public function testEmptyPages()
    {
        $page = $this->objFromFixture(UserDefinedForm::class, 'empty-page');
        $this->assertEquals(5, $page->Fields()->count());
        $controller = ModelAsController::controller_for($page);
        $form = new UserForm($controller);
        $this->assertEquals(2, $form->getSteps()->count());
    }
}
