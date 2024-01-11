<?php

namespace SilverStripe\UserForms\Extension;

use SilverStripe\Control\Director;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\ORM\DataExtension;
use SilverStripe\ORM\DataList;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\ValidationException;
use SilverStripe\UserForms\Model\EditableFormField;
use SilverStripe\UserForms\Model\Recipient\EmailRecipient;
use SilverStripe\UserForms\Model\Submission\SubmittedForm;
use SilverStripe\UserForms\Model\UserDefinedForm;
use SilverStripe\UserForms\UserForm;

/**
 * This extension provides a hook that runs during a dev/build which will check for existing data in various
 * polymorphic relationship fields for userforms models, and ensure that the data is correct.
 *
 * Various `Parent` relationships in silverstripe/userforms for SilverStripe 3 were mapped directly to UserDefinedForm
 * instances, and were made polymorphic in SilverStripe 4 (which also requires a class name). This means that a
 * certain amount of manual checking is required to ensure that upgrades are performed smoothly.
 *
 * @internal This API is likely to be removed in later major versions of silverstripe/userforms
 *
 * @extends DataExtension<UserDefinedForm>
 */
class UpgradePolymorphicExtension extends DataExtension
{
    /**
     * A list of userforms classes that have had polymorphic relationships added in SilverStripe 4, and the fields
     * on them that are polymorphic
     *
     * @var array
     */
    protected $targets = [
        EditableFormField::class => ['ParentClass'],
        EmailRecipient::class => ['FormClass'],
        SubmittedForm::class => ['ParentClass'],
    ];

    /**
     * The default class name that will be used to replace values with
     *
     * @var string
     */
    protected $defaultReplacement = UserDefinedForm::class;

    public function requireDefaultRecords()
    {
        if (!UserDefinedForm::config()->get('upgrade_on_build')) {
            return;
        }

        $updated = 0;
        foreach ($this->targets as $className => $fieldNames) {
            foreach ($fieldNames as $fieldName) {
                /** @var DataList $list */
                $list = $className::get();

                foreach ($list as $entry) {
                    /** @var DataObject $relationshipObject */
                    $relationshipObject = Injector::inst()->get($entry->$fieldName);
                    if (!$relationshipObject) {
                        continue;
                    }

                    // If the defined data class doesn't have the UserForm trait applied, it's probably wrong. Re-map
                    // it to a default value that does
                    $classTraits = class_uses($relationshipObject);
                    if (in_array(UserForm::class, $classTraits ?? [])) {
                        continue;
                    }

                    // Don't rewrite class values when an existing value is set and is an instance of UserDefinedForm
                    if ($relationshipObject instanceof UserDefinedForm) {
                        continue;
                    }

                    $entry->$fieldName = $this->defaultReplacement;
                    try {
                        $entry->write();
                        $updated++;
                    } catch (ValidationException $ex) {
                        // no-op, allow the rest of dev/build to continue. There may be an error indicating that the
                        // object's class doesn't exist, which can be fixed by {@link DatabaseAdmin::doBuild} and this
                        // logic will work the next time dev/build is run.
                    }
                }
            }
        }

        if ($updated) {
            $message = "Corrected {$updated} default polymorphic class names to {$this->defaultReplacement}";
            if (Director::is_cli()) {
                echo sprintf(" * %s\n", $message);
            } else {
                echo sprintf("<li>%s</li>\n", $message);
            }
        }
    }
}
