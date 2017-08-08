<?php

namespace SilverStripe\UserForms\Model\Recipient;






use SilverStripe\Core\Config\Config;
use SilverStripe\View\SSViewer;
use SilverStripe\View\ArrayData;
use SilverStripe\ORM\ArrayList;
use SilverStripe\UserForms\Model\EditableFormField\EditableLiteralField;
use SilverStripe\UserForms\Model\EditableFormField\EditableFormHeading;
use SilverStripe\ORM\FieldType\DBField;
use SilverStripe\Forms\GridField\GridFieldDetailForm_ItemRequest;



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
        Config::nest();
        Config::inst()->update(SSViewer::class, 'theme_enabled', true);

        $content = $this->customise(new ArrayData(array(
            'Body' => $this->record->getEmailBodyContent(),
            'HideFormData' => $this->record->HideFormData,
            'Fields' => $this->getPreviewFieldData()
        )))->renderWith($this->record->EmailTemplate);

        Config::unnest();

        return $content;
    }

    /**
     * Get some placeholder field values to display in the preview
     * @return ArrayList
     */
    private function getPreviewFieldData()
    {
        $data = new ArrayList();

        $fields = $this->record->Form()->Fields()->filter(array(
            'ClassName:not' => EditableLiteralField::class,
            'ClassName:not' => EditableFormHeading::class
        ));

        foreach ($fields as $field) {
            $data->push(new ArrayData(array(
                'Name' => $field->dbObject('Name'),
                'Title' => $field->dbObject('Title'),
                'Value' => DBField::create_field('Varchar', '$' . $field->Name),
                'FormattedValue' => DBField::create_field('Varchar', '$' . $field->Name)
            )));
        }

        return $data;
    }
}
