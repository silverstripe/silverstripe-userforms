<?php

namespace SilverStripe\UserForms\Tests\Model\SubClassFormTest;

use SilverStripe\Dev\TestOnly;
use SilverStripe\UserForms\Model\UserDefinedForm;

class SubClassForm extends UserDefinedForm implements TestOnly
{
    private static $table_name = 'SubClassForm';
}
