<?php

namespace SilverStripe\UserForms\Tests\Model\EditableFormField;

use SilverStripe\Core\Config\Config;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Forms\CompositeField;
use SilverStripe\Forms\HTMLEditor\HTMLEditorConfig;
use SilverStripe\Forms\HTMLEditor\HTMLEditorField;
use SilverStripe\Forms\LiteralField;
use SilverStripe\UserForms\Model\EditableFormField\EditableLiteralField;

/**
 * Tests the {@see EditableLiteralField} class
 */
class EditableLiteralFieldTest extends SapphireTest
{
    protected function setUp()
    {
        parent::setUp();
        $cmsConfig = HTMLEditorConfig::get('cms');
        HTMLEditorConfig::set_active($cmsConfig);
    }

    /**
     * Tests the sanitisation of HTML content
     */
    public function testSanitisation()
    {
        $rawContent = '<h1>Welcome</h1><script>alert("Hello!");</script><p>Giant Robots!</p>';
        $safeContent = '<h1>Welcome</h1><p>Giant Robots!</p>';
        $field = new EditableLiteralField();

        // Test with sanitisation enabled
        Config::modify()->set(HTMLEditorField::class, 'sanitise_server_side', true);
        $field->setContent($rawContent);
        $this->assertEquals($safeContent, $field->getContent());

        // Test with sanitisation disabled
        Config::modify()->remove(HTMLEditorField::class, 'sanitise_server_side');
        $field->setContent($rawContent);
        $this->assertEquals($rawContent, $field->getContent());
    }

    public function testHideLabel()
    {
        $field = new EditableLiteralField([
            'Title' => 'Test label'
        ]);

        $this->assertContains('Test label', $field->getFormField()->FieldHolder());
        $this->assertEquals('Test label', $field->getFormField()->Title());

        $field->HideLabel = true;
        $this->assertNotContains('Test label', $field->getFormField()->FieldHolder());
        $this->assertEmpty($field->getFormField()->Title());
    }

    public function testLiteralFieldHasUpdateFormFieldMethodCalled()
    {
        $field = $this->getMockBuilder(EditableLiteralField::class)
            ->setMethods(array('doUpdateFormField'))
            ->getMock();

        $field->expects($this->once())->method('doUpdateFormField');

        $field->getFormField();
    }

    /**
     * LiteralFields do not allow field names, etc. Instead, the field is contained within a composite field. This
     * test ensures that this structure is correct.
     */
    public function testLiteralFieldIsContainedWithinCompositeField()
    {
        $field = new EditableLiteralField;
        $formField = $field->getFormField();

        $this->assertInstanceOf(
            CompositeField::class,
            $formField,
            'Literal field is contained within a composite field'
        );
        $this->assertInstanceOf(
            LiteralField::class,
            $formField->FieldList()->first(),
            'Actual literal field exists in composite field children'
        );
    }
}
