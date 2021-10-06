<?php

namespace SilverStripe\UserForms\Tests\Extension;

use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;
use SilverStripe\UserForms\UserForm;

/**
 * A stand in for e.g. dnadesigned/silverstripe-elemental-userforms
 */
class UserFormBlockStub extends DataObject implements TestOnly
{
    use UserForm;
}
