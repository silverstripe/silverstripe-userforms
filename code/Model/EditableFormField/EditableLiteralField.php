<?php

namespace SilverStripe\UserForms\Model\EditableFormField;

use SilverStripe\Core\Injector\Injector;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\HTMLEditor\HTMLEditorConfig;
use SilverStripe\Forms\HTMLEditor\HTMLEditorField;
use SilverStripe\Forms\HTMLEditor\HTMLEditorSanitiser;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\CompositeField;
use SilverStripe\Forms\LiteralField;
use SilverStripe\UserForms\Model\EditableFormField;
use SilverStripe\View\Parsers\HTMLValue;

/**
 * Editable Literal Field. A literal field is just a blank slate where
 * you can add your own HTML / Images / Flash
 *
 * @package userforms
 * @property string $Content
 * @property int $HideFromReports
 * @property int $HideLabel
 */
class EditableLiteralField extends EditableFormField
{
    private static $singular_name = 'HTML Block';

    private static $plural_name = 'HTML Blocks';

    private static $table_name = 'EditableLiteralField';

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

    private static $db = [
        'Content' => 'HTMLText', // From CustomSettings
        'HideFromReports' => 'Boolean(0)', // from CustomSettings
        'HideLabel' => 'Boolean(0)'
    ];

    private static $defaults = [
        'HideFromReports' => false
    ];

    /**
     * Returns the {@see HTMLEditorConfig} instance to use for sanitisation
     *
     * @return HTMLEditorConfig
     */
    protected function getEditorConfig()
    {
        $editorConfig = $this->config()->get('editor_config');
        if ($editorConfig) {
            return HTMLEditorConfig::get($editorConfig);
        }
        return HTMLEditorConfig::get_active();
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
        if (!HTMLEditorField::config()->get('sanitise_server_side')) {
            return $content;
        }

        // Perform sanitisation
        $htmlValue = Injector::inst()->create(HTMLValue::class, $content);
        $santiser = Injector::inst()->create(HTMLEditorSanitiser::class, $this->getEditorConfig());
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
        $this->beforeUpdateCMSFields(function (FieldList $fields) {
            $fields->removeByName(['Default', 'Validation', 'RightTitle']);

            $fields->addFieldsToTab('Root.Main', [
                HTMLEditorField::create('Content', _t(__CLASS__ . '.CONTENT', 'HTML'))
                    ->setRows(4)
                    ->setColumns(20),
                CheckboxField::create(
                    'HideFromReports',
                    _t(__CLASS__ . '.HIDEFROMREPORT', 'Hide from reports?')
                ),
                CheckboxField::create(
                    'HideLabel',
                    _t(__CLASS__ . '.HIDELABEL', "Hide 'Title' label on frontend?")
                )
            ]);
        });

        return parent::getCMSFields();
    }

    public function getFormField()
    {
        $content = LiteralField::create(
            "LiteralFieldContent-{$this->ID}]",
            $this->dbObject('Content')->forTemplate()
        );

        $field = CompositeField::create($content)
            ->setName($this->Name)
            ->setFieldHolderTemplate(__CLASS__ . '_holder');

        $this->doUpdateFormField($field);

        return $field;
    }

    protected function updateFormField($field)
    {
        parent::updateFormField($field);

        if ($this->HideLabel) {
            $field->addExtraClass('nolabel');
        } else {
            $field->setTitle($this->Title);
        }
    }

    public function showInReports()
    {
        return !$this->HideFromReports;
    }
}
