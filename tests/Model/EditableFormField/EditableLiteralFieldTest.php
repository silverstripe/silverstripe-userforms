<?php

namespace SilverStripe\UserForms\Test\Model\EditableFormField;


use HtmlEditorConfig;


use SilverStripe\UserForms\Model\EditableFormField\EditableLiteralField;
use SilverStripe\Core\Config\Config;
use SilverStripe\Forms\CompositeField;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Dev\SapphireTest;



/**
 * Tests the {@see EditableLiteralField} class
 */
class EditableLiteralFieldTest extends SapphireTest
{

    public function setUp()
    {
        parent::setUp();
        HtmlEditorConfig::set_active('cms');
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
        Config::inst()->update('HtmlEditorField', 'sanitise_server_side', true);
        $field->setContent($rawContent);
        $this->assertEquals($safeContent, $field->getContent());

        // Test with sanitisation disabled
        Config::inst()->remove('HtmlEditorField', 'sanitise_server_side');
        $field->setContent($rawContent);
        $this->assertEquals($rawContent, $field->getContent());
    }

    public function testHideLabel()
    {
        $field = new EditableLiteralField(array(
            'Title' => 'Test label'
        ));

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

        $this->assertInstanceOf(CompositeField::class, $formField, 'Literal field is contained within a composite field');
        $this->assertInstanceOf(
            LiteralField::class,
            $formField->FieldList()->first(),
            'Actual literal field exists in composite field children'
        );
    }
}
