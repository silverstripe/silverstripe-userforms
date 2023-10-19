<?php

namespace SilverStripe\UserForms\Form;

use ResetFormAction;
use SilverStripe\Control\Controller;
use SilverStripe\Control\Session;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\FormAction;
use SilverStripe\UserForms\FormField\UserFormsStepField;
use SilverStripe\UserForms\FormField\UserFormsFieldList;

/**
 * @package userforms
 */
class UserForm extends Form
{
    /**
     * @config
     * @var string
     */
    private static $button_text = '';

    /**
     * @param Controller $controller
     * @param string $name
     */
    public function __construct(Controller $controller, $name = Form::class)
    {
        $this->controller = $controller;
        $this->setRedirectToFormOnValidationError(true);

        parent::__construct(
            $controller,
            $name,
            new FieldList(),
            new FieldList()
        );

        $this->setFields($fields = $this->getFormFields());

        $fields->setForm($this);
        $this->setActions($actions = $this->getFormActions());
        $actions->setForm($this);
        $this->setValidator($this->getRequiredFields());

        // This needs to be re-evaluated since fields have been assigned
        $this->restoreFormState();

        // Number each page
        $stepNumber = 1;
        foreach ($this->getSteps() as $step) {
            $step->setStepNumber($stepNumber++);
        }

        if ($controller->DisableCsrfSecurityToken) {
            $this->disableSecurityToken();
        }

        $data = $this->getRequest()->getSession()->get("FormInfo.{$this->FormName()}.data");

        if (is_array($data)) {
            $this->loadDataFrom($data);
        }

        $this->extend('updateForm');
    }

    public function restoreFormState()
    {
        // Suppress restoreFormState if fields haven't been bootstrapped
        if ($this->fields && $this->fields->exists()) {
            return parent::restoreFormState();
        }

        return $this;
    }

    /**
     * Used for partial caching in the template.
     *
     * @return string
     */
    public function getLastEdited()
    {
        return $this->controller->LastEdited;
    }

    /**
     * @return bool
     */
    public function getDisplayErrorMessagesAtTop()
    {
        return (bool)$this->controller->DisplayErrorMessagesAtTop;
    }

    /**
     * Return the fieldlist, filtered to only contain steps
     *
     * @return \SilverStripe\ORM\ArrayList
     */
    public function getSteps()
    {
        return $this->Fields()->filterByCallback(function ($field) {
            return $field instanceof UserFormsStepField;
        });
    }

    /**
     * Get the form fields for the form on this page. Can modify this FieldSet
     * by using {@link updateFormFields()} on an {@link Extension} subclass which
     * is applied to this controller.
     *
     * This will be a list of top level composite steps
     *
     * @return FieldList
     */
    public function getFormFields()
    {
        $fields = UserFormsFieldList::create();
        $target = $fields;

        foreach ($this->controller->data()->Fields() as $field) {
            $target = $target->processNext($field);
        }
        $fields->clearEmptySteps();
        $this->extend('updateFormFields', $fields);
        $fields->setForm($this);
        return $fields;
    }

    /**
     * Generate the form actions for the UserDefinedForm. You
     * can manipulate these by using {@link updateFormActions()} on
     * a decorator.
     *
     * @return FieldList
     */
    public function getFormActions()
    {
        $submitText = ($this->controller->SubmitButtonText)
            ? $this->controller->SubmitButtonText
            : _t('SilverStripe\\UserForms\\Model\\UserDefinedForm.SUBMITBUTTON', 'Submit');
        $clearText = ($this->controller->ClearButtonText)
            ? $this->controller->ClearButtonText
            : _t('SilverStripe\\UserForms\\Model\\UserDefinedForm.CLEARBUTTON', 'Clear');

        $actions = FieldList::create(FormAction::create('process', $submitText));

        if ($this->controller->ShowClearButton) {
            $actions->push(FormAction::create('clearForm', $clearText)->setAttribute('type', 'reset'));
        }

        $this->extend('updateFormActions', $actions);
        $actions->setForm($this);
        return $actions;
    }

    /**
     * Get the required form fields for this form.
     *
     * @return RequiredFields
     */
    public function getRequiredFields()
    {
        // Generate required field validator
        $requiredNames = $this
            ->getController()
            ->data()
            ->Fields()
            ->filter('Required', true)
            ->column('Name');
        $requiredNames = array_merge($requiredNames, $this->getEmailRecipientRequiredFields());
        $required = UserFormsRequiredFields::create($requiredNames);
        $this->extend('updateRequiredFields', $required);
        $required->setForm($this);
        return $required;
    }

    /**
     * Override some we can add UserForm specific attributes to the form.
     *
     * @return array
     */
    public function getAttributes()
    {
        $attrs = parent::getAttributes();

        $attrs['class'] = $attrs['class'] . ' userform';
        $attrs['data-livevalidation'] = (bool)$this->controller->EnableLiveValidation;
        $attrs['data-toperrors'] = (bool)$this->controller->DisplayErrorMessagesAtTop;

        return $attrs;
    }

    /**
     * @return string
     */
    public function getButtonText()
    {
        return $this->config()->get('button_text');
    }

    /**
     * Push fields into the RequiredFields array if they are used by any Email recipients.
     * Ignore if there is a backup i.e. the plain string field is set
     *
     * @return array required fields names
     */
    protected function getEmailRecipientRequiredFields()
    {
        $requiredFields = [];
        $recipientFieldsMap = [
            'EmailAddress' => 'SendEmailToField',
            'EmailSubject' => 'SendEmailSubjectField',
            'EmailReplyTo' => 'SendEmailFromField'
        ];

        foreach ($this->getController()->data()->EmailRecipients() as $recipient) {
            foreach ($recipientFieldsMap as $textField => $dynamicFormField) {
                if (empty($recipient->$textField) && $recipient->getComponent($dynamicFormField)->exists()) {
                    $requiredFields[] = $recipient->getComponent($dynamicFormField)->Name;
                }
            }
        }

        return $requiredFields;
    }
}
