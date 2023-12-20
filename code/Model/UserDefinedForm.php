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
     */
    private static $icon_class = 'font-icon-p-list';

    /**
     * @var string
     */
    private static $description = 'Adds a customizable form.';

    /**
     * @var string
     */
    private static $table_name = 'UserDefinedForm';

    /**
     * @var string
     */
    private static $controller_name =  UserDefinedFormController::class;
}
