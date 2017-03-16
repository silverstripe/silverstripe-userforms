<?php


class UserFormsVersionedTest extends SapphireTest
{
    protected static $fixture_file = 'UserDefinedFormTest.yml';

    public function setUp()
    {
        parent::setUp();
        Versioned::reading_stage('Stage');
    }

    public function testPublishing()
    {
        /** @var UserDefinedForm $form */
        $form = $this->objFromFixture('UserDefinedForm', 'filtered-form-page');

        // Get id of options
        $optionID = $this->idFromFixture('EditableOption', 'option-3');
        $this->assertEmpty(Versioned::get_one_by_stage('EditableOption', 'Live', array('"ID" = ?' => $optionID)));

        // Publishing writes this to live
        $form->doPublish();
        $liveVersion = Versioned::get_versionnumber_by_stage('EditableOption', 'Live', $optionID, false);
        $this->assertNotEmpty($liveVersion);

        // Add new option, and repeat publish process
        /** @var EditableCheckboxGroupField $list */
        $list = $this->objFromFixture('EditableCheckboxGroupField', 'checkbox-group');
        $newOption = new EditableOption();
        $newOption->Title = 'New option';
        $newOption->Value = 'ok';
        $newOption->write();
        $newOptionID = $newOption->ID;
        $list->Options()->add($newOption);

        $form->doPublish();

        // Un-modified option should not create a new version
        $newLiveVersion = Versioned::get_versionnumber_by_stage('EditableOption', 'Live', $optionID, false);
        $this->assertNotEmpty($newLiveVersion);
        $this->assertEquals($liveVersion, $newLiveVersion);

        // New option is successfully published
        $newOptionLiveVersion = Versioned::get_versionnumber_by_stage('EditableOption', 'Live', $newOptionID, false);
        $this->assertNotEmpty($newOptionLiveVersion);
    }
}
