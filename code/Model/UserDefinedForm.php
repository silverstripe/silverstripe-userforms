<?php

namespace SilverStripe\UserForms\Model;

use Page;

use SilverStripe\UserForms\UserForm;
use SilverStripe\UserForms\Control\UserDefinedFormController;

/**
 * @package userforms
 * @method SilverStripe\ORM\HasManyList<SilverStripe\UserForms\Model\Recipient\EmailRecipient> EmailRecipients()
 * @method SilverStripe\ORM\HasManyList<SilverStripe\UserForms\Model\Submission\SubmittedForm> Submissions()
 */
class UserDefinedForm extends Page
{
    use UserForm;

    /**
     * @var string
     * @deprecated 6.4.0 Will be renamed to cms_icon_class
     */
    private static $icon_class = 'font-icon-p-list';

    /**
     * @var string
     * @deprecated 6.4.0 use class_description instead.
     */
    private static $description = 'Adds a customizable form.';

    private static $class_description = 'Adds a customizable form.';

    /**
     * @var string
     */
    private static $table_name = 'UserDefinedForm';

    /**
     * @var string
     */
    private static $controller_name =  UserDefinedFormController::class;
}
