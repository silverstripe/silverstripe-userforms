<?php

namespace SilverStripe\UserForms\Model\Recipient;

use SilverStripe\Core\Config\Config;
use SilverStripe\Forms\GridField\GridFieldDetailForm_ItemRequest;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\FieldType\DBField;
use SilverStripe\UserForms\Model\EditableFormField\EditableFormHeading;
use SilverStripe\UserForms\Model\EditableFormField\EditableLiteralField;
use SilverStripe\View\ArrayData;
use SilverStripe\View\Requirements;
use SilverStripe\View\SSViewer;

/**
 * Controller that handles requests to EmailRecipient's
 *
 * @package userforms
 */
class UserFormRecipientItemRequest extends GridFieldDetailForm_ItemRequest
{
    private static $allowed_actions = [
        'edit',
        'view',
        'ItemEditForm',
        'preview'
    ];

    /**
     * Renders a preview of the recipient email.
     */
    public function preview()
    {
        // Enable theme for preview (may be needed for Shortcodes)
        Config::nest();
        Config::modify()->set(SSViewer::class, 'theme_enabled', true);

        Requirements::clear();

        $content = $this->customise([
            'Body' => $this->record->getEmailBodyContent(),
            'HideFormData' => (bool) $this->record->HideFormData,
            'Fields' => $this->getPreviewFieldData()
        ])->renderWith($this->record->EmailTemplate);

        Requirements::restore();
        Config::unnest();


        return $content;
    }

    /**
     * Get some placeholder field values to display in the preview
     *
     * @return ArrayList<ArrayData>
     */
    protected function getPreviewFieldData()
    {
        $data = ArrayList::create();
        $fields = $this->record->Form()->Fields();

        foreach ($fields as $field) {
            if (!$field->showInReports()) {
                continue;
            }
            $data->push(ArrayData::create([
                'Name' => $field->dbObject('Name'),
                'Title' => $field->dbObject('Title'),
                'Value' => DBField::create_field('Varchar', '$' . $field->Name),
                'FormattedValue' => DBField::create_field('Varchar', '$' . $field->Name)
            ]));
        }

        return $data;
    }
}
