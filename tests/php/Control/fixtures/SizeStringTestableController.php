<?php

namespace SilverStripe\UserForms\Tests\Control\fixtures;

use SilverStripe\Dev\TestOnly;
use SilverStripe\UserForms\Control\UserDefinedFormController;

class SizeStringTestableController extends UserDefinedFormController implements TestOnly
{
    public function convertSizeStringToBytes($sizeString)
    {
        return $this->parseByteSizeString($sizeString);
    }
}
