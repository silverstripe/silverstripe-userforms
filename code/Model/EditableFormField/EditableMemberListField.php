<?php
/**
 * Creates an editable field that displays members in a given group
 *
 * @package userforms
 */

class EditableMemberListField extends EditableFormField
{

    private static $singular_name = 'Member List Field';

    private static $plural_name = 'Member List Fields';

    private static $has_one = array(
        'Group' => 'Group'
    );

    /**
     * @return FieldList
     */
    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->removeByName('Default');
        $fields->removeByName('Validation');

        $fields->addFieldToTab(
            'Root.Main',
            DropdownField::create(
                "GroupID",
                _t('EditableFormField.GROUP', 'Group'),
                Group::get()->map()
            )->setEmptyString(' ')
        );

        return $fields;
    }

    public function getFormField()
    {
        if (empty($this->GroupID)) {
            return false;
        }

        $members = Member::map_in_groups($this->GroupID);
        $field = new DropdownField($this->Name, $this->EscapedTitle, $members);
        $this->doUpdateFormField($field);
        return $field;
    }

    public function getValueFromData($data)
    {
        if (isset($data[$this->Name])) {
            $memberID = $data[$this->Name];
            $member = Member::get()->byID($memberID);
            return $member ? $member->getName() : "";
        }

        return false;
    }
}
