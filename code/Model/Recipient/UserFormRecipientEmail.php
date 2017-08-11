<?php

namespace SilverStripe\UserForms\Model\Recipient;

use SilverStripe\Control\Email\Email;

/**
 * Email that gets sent to the people listed in the Email Recipients when a
 * submission is made.
 *
 * @package userforms
 */

class UserFormRecipientEmail extends Email
{
    protected $ss_template = 'SubmittedFormEmail';

    protected $data;

    public function __construct($submittedFields = null)
    {
        parent::__construct($submittedFields = null);
    }

    /**
     * Set the "Reply-To" header with an email address rather than append as
     * {@link Email::replyTo} does.
     *
     * @param string|array $address
     * @param string|null $name
     * @return $this
     */
    public function setReplyTo($address, $name = null)
    {
        $this->customHeaders['Reply-To'] = $email;
        return $this;
    }
}
