<?php

/**
 * {@link TreeDropdownField} subclass for handling loading folders through the
 * nested {@link FormField} instances of the {@link FieldEditor}
 *
 * @package userforms
 */
class UserformsTreeDropdownField extends TreeDropdownField {

	public function Link($action = null) {
		$form = Controller::curr()->EditForm;

		return Controller::join_links(
			$form->FormAction(), 'field/Fields/handleField/' . $this->name,
			$action .
			'?SecurityID='. $form->getSecurityToken()->getValue()
		);
	}
}