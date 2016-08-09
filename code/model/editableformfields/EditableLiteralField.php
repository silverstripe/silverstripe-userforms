<?php

/**
 * Editable Literal Field. A literal field is just a blank slate where
 * you can add your own HTML / Images / Flash
 *
 * @package userforms
 */

class EditableLiteralField extends EditableFormField
{

    private static $singular_name = 'HTML Block';

    private static $plural_name = 'HTML Blocks';

    /**
     * Mark as literal only
     *
     * @config
     * @var bool
     */
    private static $literal = true;

    /**
     * Get the name of the editor config to use for HTML sanitisation. Defaults to the active config.
     *
     * @var string
     * @config
     */
    private static $editor_config = null;

    private static $db = array(
        'Content' => 'HTMLText', // From CustomSettings
        'HideFromReports' => 'Boolean(0)', // from CustomSettings
        'HideLabel' => 'Boolean(0)'
    );

    private static $defaults = array(
        'HideFromReports' => false
    );

    /**
     * Returns the {@see HtmlEditorConfig} instance to use for sanitisation
     *
     * @return HtmlEditorConfig
     */
    protected function getEditorConfig()
    {
        $editorConfig = $this->config()->editor_config;
        if ($editorConfig) {
            return HtmlEditorConfig::get($editorConfig);
        }
        return HtmlEditorConfig::get_active();
    }

    /**
     * Safely sanitise html content, if enabled
     *
     * @param string $content Raw html
     * @return string Safely sanitised html
     */
    protected function sanitiseContent($content)
    {
        // Check if sanitisation is enabled
        if (!HtmlEditorField::config()->sanitise_server_side) {
            return $content;
        }

        // Perform sanitisation
        $htmlValue = Injector::inst()->create('HTMLValue', $content);
        $santiser = Injector::inst()->create('HtmlEditorSanitiser', $this->getEditorConfig());
        $santiser->sanitise($htmlValue);
        return $htmlValue->getContent();
    }

    /**
     * Get HTML Content of this literal field
     *
     * @return string
     */
    public function getContent()
    {
        // Apply html editor sanitisation rules
        $content = $this->getField('Content');
        return $this->sanitiseContent($content);
    }

    /**
     * Set the content with the given value
     *
     * @param string $content
     */
    public function setContent($content)
    {
        // Apply html editor sanitisation rules
        $content = $this->sanitiseContent($content);
        $this->setField('Content', $content);
    }

    /**
     * @return FieldList
     */
    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->removeByName(array('Default', 'Validation', 'RightTitle'));

        $fields->addFieldsToTab('Root.Main', array(
            HTMLEditorField::create('Content', _t('EditableLiteralField.CONTENT', 'HTML'))
                ->setRows(4)
                ->setColumns(20),
            CheckboxField::create(
                'HideFromReports',
                _t('EditableLiteralField.HIDEFROMREPORT', 'Hide from reports?')
            ),
            CheckboxField::create(
                'HideLabel',
                _t('EditableLiteralField.HIDELABEL', "Hide 'Title' label on frontend?")
            )
        ));

        return $fields;
    }

    public function getFormField()
    {
        // Build label and css classes
        $label = '';
        $classes = $this->ExtraClass;
        if (empty($this->Title) || $this->HideLabel) {
            $classes .= " nolabel";
        } else {
            $label = "<label class='left'>{$this->EscapedTitle}</label>";
        }

        $field = new LiteralField(
            "LiteralField[{$this->ID}]",
            sprintf(
                "<div id='%s' class='field text %s'>
					%s
					<div class='middleColumn literalFieldArea'>%s</div>".
                "</div>",
                Convert::raw2htmlname($this->Name),
                Convert::raw2att($classes),
                $label,
                $this->Content
            )
        );

        // When dealing with literal fields there is no further customisation that can be added at this point
        return $field;
    }

    public function showInReports()
    {
        return ! $this->HideFromReports;
    }
}
