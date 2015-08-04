<?php

/**
 * Editable Literal Field. A literal field is just a blank slate where
 * you can add your own HTML / Images / Flash
 * 
 * @package userforms
 */

class EditableLiteralField extends EditableFormField {
	
	private static $singular_name = 'HTML Block';
	
	private static $plural_name = 'HTML Blocks';

	/**
	 * Get the name of the editor config to use for HTML sanitisation. Defaults to the active config.
	 *
	 * @var string
	 * @config
	 */
	private static $editor_config = null;

	/**
	 * Returns the {@see HtmlEditorConfig} instance to use for sanitisation
	 *
	 * @return HtmlEditorConfig
	 */
	protected function getEditorConfig() {
		$editorConfig = $this->config()->editor_config;
		if($editorConfig) return HtmlEditorConfig::get($editorConfig);
		return HtmlEditorConfig::get_active();
	}

	/**
	 * Safely sanitise html content, if enabled
	 *
	 * @param string $content Raw html
	 * @return string Safely sanitised html
	 */
	protected function sanitiseContent($content) {
		// Check if sanitisation is enabled
		if(!HtmlEditorField::config()->sanitise_server_side) return $content;

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
	public function getContent() {
		// Apply html editor sanitisation rules
		$content = $this->getSetting('Content');
		return $this->sanitiseContent($content);
	}

	/**
	 * Set the content with the given value
	 *
	 * @param string $content
	 */
	public function setContent($content) {
		// Apply html editor sanitisation rules
		$content = $this->sanitiseContent($content);
		$this->setSetting('Content', $content);
	}

	/**
	 * @return FieldList
	 */
	public function getCMSFields() {
		$fields = parent::getCMSFields();

		$fields->removeByName('Default');
		$fields->removeByName('Validation');

		$fields->addFieldsToTab('Root.Main', array(
			HTMLEditorField::create(
				$this->getSettingName('Content'),
				'HTML',
				$this->getContent())
			->setRows(4)
			->setColumns(20),
			CheckboxField::create(
				$this->getSettingName('HideFromReports'),
				_t('EditableLiteralField.HIDEFROMREPORT', 'Hide from reports?'), 
				$this->getSetting('HideFromReports')
			)
		));

		return $fields;
	}

	public function getFormField() {
		$label = $this->Title ? "<label class='left'>$this->Title</label>":"";
		$classes = $this->Title ? "" : " nolabel";
		
		return new LiteralField("LiteralField[$this->ID]", 
			"<div id='$this->Name' class='field text$classes'>
				$label
				<div class='middleColumn literalFieldArea'>". $this->getSetting('Content') ."</div>".
			"</div>"
		);
	}
	
	public function showInReports() {
		return (!$this->getSetting('HideFromReports'));
	}
}
