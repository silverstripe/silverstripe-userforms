<?php

namespace SilverStripe\UserForms\Model;

use Page;

use SilverStripe\UserForms\UserForm;
use SilverStripe\UserForms\Control\UserDefinedFormController;

/**
 * @package userforms
 */
class UserDefinedForm extends Page
{
    use UserForm;

    /**
     * @var string
     */
    private static $icon = 'silverstripe/userforms:images/sitetree_icon.png';

    /**
     * @var string
     */
    private static $description = 'Adds a customizable form.';

    /**
     * @var string
     */
    private static $table_name = 'UserDefinedForm';

    public function getControllerName()
    {
        return UserDefinedFormController::class;
    }
}
