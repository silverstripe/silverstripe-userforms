<?php

use SilverStripe\Forms\SegmentFieldModifier\SlugSegmentFieldModifier;

class UnderscoreSegmentFieldModifier extends SlugSegmentFieldModifier
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
        return str_replace('-', '_', parent::getPreview($value));
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
        return str_replace('-', '_', parent::getSuggestion($value));
    }
}
