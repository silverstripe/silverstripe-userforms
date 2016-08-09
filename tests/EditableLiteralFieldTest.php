<?php

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

        $this->assertContains('Test label', $field->getFormField()->Field());

        $field->HideLabel = true;
        $this->assertNotContains('Test label', $field->getFormField()->Field());
    }
}
