<?php

namespace SilverStripe\UserForms\Model\EditableFormField;

use SilverStripe\Core\Manifest\ModuleLoader;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextField;
use SilverStripe\i18n\i18n;
use SilverStripe\UserForms\Model\EditableCustomRule;
use SilverStripe\UserForms\Model\EditableFormField;

/**
 * A dropdown field which allows the user to select a country
 *
 * @package userforms
 * @property bool $UseEmptyString
 * @property string $EmptyString
 */
class EditableCountryDropdownField extends EditableFormField
{
    private static $singular_name = 'Country Dropdown';

    private static $plural_name = 'Country Dropdowns';

    private static $db = array(
        'UseEmptyString' => 'Boolean',
        'EmptyString' => 'Varchar(255)',
    );

    private static $table_name = 'EditableCountryDropdownField';

    /**
     * @return \SilverStripe\Forms\FieldList
     */
    public function getCMSFields()
    {
        $this->beforeUpdateCMSFields(function (FieldList $fields) {
            $fields->removeByName('Default');
            $fields->addFieldToTab(
                'Root.Main',
                DropdownField::create('Default', _t(__CLASS__ . '.DEFAULT', 'Default value'))
                             ->setSource(i18n::getData()->getCountries())
                             ->setHasEmptyDefault(true)
                             ->setEmptyString('---')
            );

            $fields->addFieldToTab(
                'Root.Main',
                CheckboxField::create('UseEmptyString', _t(__CLASS__ . '.USE_EMPTY_STRING', 'Set default empty string'))
            );

            $fields->addFieldToTab(
                'Root.Main',
                TextField::create('EmptyString', _t(__CLASS__ . '.EMPTY_STRING', 'Empty String'))
            );
        });

        return parent::getCMSFields();
    }

    public function getFormField()
    {
        $field = DropdownField::create($this->Name, $this->Title ?: false)
            ->setSource(i18n::getData()->getCountries())
            ->setFieldHolderTemplate(EditableFormField::class . '_holder')
            ->setTemplate(EditableDropdown::class);

        // Empty string
        if ($this->UseEmptyString) {
            $field->setEmptyString($this->EmptyString ?: '');
        }

        // Set default
        if ($this->Default) {
            $field->setValue($this->Default);
        }

        $this->doUpdateFormField($field);

        return $field;
    }

    public function getValueFromData($data)
    {
        if (!empty($data[$this->Name])) {
            $source = $this->getFormField()->getSource();
            return $source[$data[$this->Name]];
        }
    }

    public function getIcon()
    {
        $resource = ModuleLoader::getModule('silverstripe/userforms')->getResource('images/editabledropdown.png');

        if (!$resource->exists()) {
            return '';
        }

        return $resource->getURL();
    }

    public function getSelectorField(EditableCustomRule $rule, $forOnLoad = false)
    {
        return "$(\"select[name='{$this->Name}']\")";
    }
}
