<?php

namespace SilverStripe\UserForms\Model\Submission;

use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig;
use SilverStripe\Forms\GridField\GridFieldDataColumns;
use SilverStripe\Forms\GridField\GridFieldExportButton;
use SilverStripe\Forms\GridField\GridFieldPrintButton;
use SilverStripe\Forms\ReadonlyField;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\DB;
use SilverStripe\Security\Member;
use SilverStripe\UserForms\Model\UserDefinedForm;
use SilverStripe\UserForms\Model\Submission\SubmittedFormField;

class SubmittedForm extends DataObject
{
    private static $has_one = [
        'SubmittedBy' => Member::class,
        'Parent' => DataObject::class,
    ];

    private static $has_many = [
        'Values' => SubmittedFormField::class
    ];

    private static $cascade_deletes = [
        'Values',
    ];

    private static $summary_fields = [
        'ID',
        'Created'
    ];

    private static $table_name = 'SubmittedForm';

    public function requireDefaultRecords()
    {
        parent::requireDefaultRecords();

        // make sure to migrate the class across (prior to v5.x)
        DB::query("UPDATE SubmittedForm SET ParentClass = 'Page' WHERE ParentClass IS NULL");
    }

    /**
     * Returns the value of a relation or, in the case of this form, the value
     * of a given child {@link SubmittedFormField}
     *
     * @param string
     *
     * @return mixed
     */
    public function relField($fieldName)
    {
        // default case
        if ($value = parent::relField($fieldName)) {
            return $value;
        }

        // check values for a form field with the matching name.
        $formField = SubmittedFormField::get()->filter(array(
            'ParentID' => $this->ID,
            'Name' => $fieldName
        ))->first();

        if ($formField) {
            return $formField->getFormattedValue();
        }
    }

    /**
     * @return FieldList
     */
    public function getCMSFields()
    {
        $this->beforeUpdateCMSFields(function ($fields) {
            $fields->removeByName('Values');

            //check to ensure there is a Member to extract an Email from else null value
            if ($this->SubmittedBy() && $this->SubmittedBy()->exists()) {
                $submitter = $this->SubmittedBy()->Email;
            } else {
                $submitter = null;
            }

            //replace scaffolded field with readonly submitter
            $fields->replaceField(
                'SubmittedByID',
                ReadonlyField::create(
                    'Submitter',
                    'Submitter',
                    $submitter
                )
            );

            $values = GridField::create(
                'Values',
                SubmittedFormField::class,
                $this->Values()->sort('Created', 'ASC')
            );

            $exportColumns = array(
                'Title' => 'Title',
                'ExportValue' => 'Value'
            );

            $config = new GridFieldConfig();
            $config->addComponent(new GridFieldDataColumns());
            $config->addComponent(new GridFieldExportButton('after', $exportColumns));
            $config->addComponent(new GridFieldPrintButton());
            $values->setConfig($config);

            $fields->addFieldToTab('Root.Main', $values);
        });

        $fields = parent::getCMSFields();

        return $fields;
    }

    /**
     * @param Member
     *
     * @return boolean
     */
    public function canCreate($member = null, $context = [])
    {
        $extended = $this->extendedCan(__FUNCTION__, $member);
        if ($extended !== null) {
            return $extended;
        }
        return $this->Parent()->canCreate();
    }

    /**
     * @param Member
     *
     * @return boolean
     */
    public function canView($member = null)
    {
        $extended = $this->extendedCan(__FUNCTION__, $member);

        if ($extended !== null) {
            return $extended;
        }

        if ($this->Parent()) {
            return $this->Parent()->canView($member);
        }

        return parent::canView($member);
    }

    /**
     * @param Member
     *
     * @return boolean
     */
    public function canEdit($member = null)
    {
        $extended = $this->extendedCan(__FUNCTION__, $member);

        if ($extended !== null) {
            return $extended;
        }

        if ($this->Parent()) {
            return $this->Parent()->canEdit($member);
        }

        return parent::canEdit($member);
    }

    /**
     * @param Member
     *
     * @return boolean
     */
    public function canDelete($member = null)
    {
        $extended = $this->extendedCan(__FUNCTION__, $member);

        if ($extended !== null) {
            return $extended;
        }

        if ($this->Parent()) {
            return $this->Parent()->canDelete($member);
        }

        return parent::canDelete($member);
    }

    /**
     * Before we delete this form make sure we delete all the field values so
     * that we don't leave old data round.
     *
     * @return void
     */
    protected function onBeforeDelete()
    {
        if ($this->Values()) {
            foreach ($this->Values() as $value) {
                $value->delete();
            }
        }

        parent::onBeforeDelete();
    }
}
