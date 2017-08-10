<?php

namespace SilverStripe\UserForms\Modifier;

use SilverStripe\Forms\Form;
use SilverStripe\Forms\SegmentFieldModifier\AbstractSegmentFieldModifier;
use SilverStripe\UserForms\Model\EditableFormField;

class DisambiguationSegmentFieldModifier extends AbstractSegmentFieldModifier
{
    public function getPreview($value)
    {
        if ($this->form instanceof Form && $record = $this->form->getRecord()) {
            $parent = $record->Parent();

            $try = $value;

            $sibling = EditableFormField::get()
                ->filter('ParentID', $parent->ID)
                ->filter('Name', $try)
                ->where('"ID" != ' . $record->ID)
                ->first();

            $counter = 1;

            while ($sibling !== null) {
                $try = $value . '_' . $counter++;

                $sibling = EditableFormField::get()
                    ->filter('ParentID', $parent->ID)
                    ->filter('Name', $try)
                    ->first();
            }

            if ($try !== $value) {
                return $try;
            }
        }

        return $value;
    }

    public function getSuggestion($value)
    {
        return $this->getPreview($value);
    }
}
