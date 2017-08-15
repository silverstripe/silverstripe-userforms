<?php

namespace SilverStripe\UserForms\Tests\Model\EditableFormField;

use SilverStripe\Dev\SapphireTest;
use SilverStripe\ORM\ValidationException;
use SilverStripe\UserForms\Model\EditableFormField\EditableFileField;

/**
 * @package userforms
 */
class EditableFileFieldTest extends SapphireTest
{
    protected static $fixture_file = '../EditableFormFieldTest.yml';

    /**
     * @var
     */
    private $php_max_file_size;

    /**
     * Hold the server default max file size upload limit for later
     */
    protected function setUp()
    {
        parent::setUp();

        $editableFileField = singleton(EditableFileField::class);
        $this->php_max_file_size = $editableFileField::get_php_max_file_size();
    }

    /**
     * Test that the field validator has the server default as the max file size upload
     */
    public function testDefaultMaxFileSize()
    {
        $fileField = $this->objFromFixture(EditableFileField::class, 'file-field');
        $formField = $fileField->getFormField();

        $this->assertEquals($this->php_max_file_size, $formField->getValidator()->getAllowedMaxFileSize());
    }

    /**
     * Test that validation prevents the provided upload size limit to be less than or equal to the max php size
     */
    public function testValidateFileSizeFieldValue()
    {

        $fileField = $this->objFromFixture(EditableFileField::class, 'file-field');
        $this->setExpectedException(ValidationException::class);
        $fileField->MaxFileSizeMB = $this->php_max_file_size * 2;
        $fileField->write();
    }

    /**
     * Test the field validator has the updated allowed max file size
     */
    public function testUpdatedMaxFileSize()
    {
        $fileField = $this->objFromFixture(EditableFileField::class, 'file-field');
        $fileField->MaxFileSizeMB = .25;
        $fileField->write();

        $formField = $fileField->getFormField();
        $this->assertEquals($formField->getValidator()->getAllowedMaxFileSize(), 262144);
    }
}
