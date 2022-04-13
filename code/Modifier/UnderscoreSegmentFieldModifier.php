<?php

namespace SilverStripe\UserForms\Modifier;

use SilverStripe\Forms\SegmentFieldModifier\SlugSegmentFieldModifier;

class UnderscoreSegmentFieldModifier extends SlugSegmentFieldModifier
{
    public function getPreview($value)
    {
        return str_replace('-', '_', parent::getPreview($value) ?? '');
    }

    public function getSuggestion($value)
    {
        return str_replace('-', '_', parent::getSuggestion($value) ?? '');
    }
}
