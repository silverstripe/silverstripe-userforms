<?php

namespace SilverStripe\UserForms\Tests\Control;

use SilverStripe\Core\Extension;
use SilverStripe\Dev\TestOnly;
use SilverStripe\View\ArrayData;

class CustomMergeFieldTestExtension extends Extension implements TestOnly
{
    public static string $value = '';

    public function updateMergeFields(ArrayData $data, $recipient, $submittedForm): void
    {
        $data->setField('DevVar', static::$value);
        $data->setField('Site.Name', static::$value);
    }
}
