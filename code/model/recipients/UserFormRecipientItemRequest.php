<?php

/**
 * Controller that handles requests to EmailRecipient's
 *
 * @package userforms
 */
class UserFormRecipientItemRequest extends GridFieldDetailForm_ItemRequest
{

    private static $allowed_actions = array(
        'edit',
        'view',
        'ItemEditForm',
        'preview'
    );

    /**
     * Renders a preview of the recipient email.
     */
    public function preview()
    {
        // Enable theme for preview (may be needed for Shortcodes)
        $previous_theme_enabled = Config::inst()->get('SSViewer', 'theme_enabled');
        Config::inst()->update('SSViewer', 'theme_enabled', true);
        
        $ret = $this->customise(new ArrayData(array(
            'Body' => $this->record->getEmailBodyContent(),
            'HideFormData' => $this->record->HideFormData,
            'Fields' => $this->getPreviewFieldData()
        )))->renderWith($this->record->EmailTemplate);
        
        // reset theme preview value
        Config::inst()->update('SSViewer', 'theme_enabled', $previous_theme_enabled);
        
        return $ret;
    }

    /**
     * Get some placeholder field values to display in the preview
     * @return ArrayList
     */
    private function getPreviewFieldData()
    {
        $data = new ArrayList();

        $fields = $this->record->Form()->Fields()->filter(array(
            'ClassName:not' => 'EditableLiteralField',
            'ClassName:not' => 'EditableFormHeading'
        ));

        foreach ($fields as $field) {
            $data->push(new ArrayData(array(
                'Name' => $field->Name,
                'Title' => $field->Title,
                'Value' => '$' . $field->Name,
                'FormattedValue' => '$' . $field->Name
            )));
        }

        return $data;
    }
}
