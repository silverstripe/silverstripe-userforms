<?php

namespace SilverStripe\UserForms\Tests\Form;

use SilverStripe\CMS\Controllers\CMSMain;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\Session;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\ORM\DataList;
use SilverStripe\UserForms\Form\GridFieldAddClassesButton;
use SilverStripe\UserForms\Model\EditableFormField;
use SilverStripe\UserForms\Model\EditableFormField\EditableDateField;
use SilverStripe\UserForms\Model\EditableFormField\EditableTextField;
use SilverStripe\UserForms\Model\UserDefinedForm;
use Symbiote\GridFieldExtensions\GridFieldEditableColumns;

class GridFieldAddClassesButtonTest extends SapphireTest
{
    protected $usesDatabase = true;

    public function testHandleAddUpdatesModifiedFormData()
    {
        $this->logInWithPermission('SITETREE_EDIT_ALL');
        $udf = UserDefinedForm::create(['Title' => 'MyUDF']);
        $udfID = $udf->write();
        // Set the current controller to CMSMain to satisfy EditableFormField::getCanCreateContext()
        /** @var CMSMain $controller */
        $controller = Injector::inst()->get(CMSMain::class);
        $request = new HTTPRequest('GET', '/');
        $request->setSession(new Session([]));
        $controller->setRequest($request);
        $controller->setCurrentRecordID($udf->ID);
        $controller->pushCurrent();
        $list = new DataList(EditableFormField::class);
        $field = EditableTextField::create([
            'ParentClass' => UserDefinedForm::class,
            'ParentID' => $udfID,
            'Title' => 'MyTitle',
        ]);
        $fieldID = $field->write();
        $list->add($field);
        $gridField = new GridField('MyName', 'MyTitle', $list);
        $button = new GridFieldAddClassesButton([EditableTextField::class]);
        $request = new HTTPRequest('POST', 'url', [], [
            'Fields' => [
                GridFieldEditableColumns::POST_KEY => [
                    $fieldID => [
                        'ClassName' => EditableDateField::class,
                        'Title' => 'UpdatedTitle'
                    ]
                ]
            ]
        ]);
        $gridField->setRequest($request);
        $button->handleAdd($gridField);
        $field = EditableFormField::get()->byID($fieldID);
        $this->assertSame(EditableDateField::class, $field->ClassName);
        $this->assertSame('UpdatedTitle', $field->Title);
    }
}
