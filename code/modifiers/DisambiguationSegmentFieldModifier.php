<?php

use SilverStripe\Forms\SegmentFieldModifier\AbstractSegmentFieldModifier;

class DisambiguationSegmentFieldModifier extends AbstractSegmentFieldModifier
{
    /**
     * @inheritdoc
     *
     * @param string $value
     *
     * @return string
     */
    public function getPreview($value)
    {
        if ($this->form instanceof Form && $record = $this->form->getRecord()) {
            $parent = $record->Parent();

            $try = $value;

            $sibling = EditableformField::get()
                ->filter('ParentID', $parent->ID)
                ->filter('Name', $try)
                ->where('"ID" != ' . $record->ID)
                ->first();

            $counter = 1;

            while ($sibling !== null) {
                $try = $value . '_' . $counter++;

                $sibling = EditableformField::get()
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

    /**
     * @inheritdoc
     *
     * @param string $value
     *
     * @return string
     */
    public function getSuggestion($value)
    {
        return $this->getPreview($value);
    }
}
