<?php

/**
 * English (United Kingdom) language pack
 * @package modules: userforms
 * @subpackage i18n
 */

i18n::include_locale_file('modules: userforms', 'en_US');

global $lang;

if(array_key_exists('en_GB', $lang) && is_array($lang['en_GB'])) {
	$lang['en_GB'] = array_merge($lang['en_US'], $lang['en_GB']);
} else {
	$lang['en_GB'] = $lang['en_US'];
}

$lang['en_GB']['EditableCheckbox']['ANY'] = 'Any';
$lang['en_GB']['EditableCheckbox']['NOTSELECTED'] = 'Not selected';
$lang['en_GB']['EditableCheckbox']['SELECTED'] = 'Selected';
$lang['en_GB']['EditableCheckbox.ss']['CHECKBOX'] = 'Checkbox field';
$lang['en_GB']['EditableCheckbox.ss']['DELETE'] = 'Delete this field';
$lang['en_GB']['EditableCheckbox.ss']['DRAG'] = 'Drag to rearrange order of fields';
$lang['en_GB']['EditableCheckbox.ss']['LOCKED'] = 'This field cannot be modified';
$lang['en_GB']['EditableCheckbox.ss']['MORE'] = 'More options';
$lang['en_GB']['EditableCheckboxGroupField.ss']['ADD'] = 'Add new option';
$lang['en_GB']['EditableCheckboxGroupField.ss']['CHECKBOXGROUP'] = 'Checkbox group';
$lang['en_GB']['EditableCheckboxGroupField.ss']['DELETE'] = 'Delete this field';
$lang['en_GB']['EditableCheckboxGroupField.ss']['DRAG'] = 'Drag to rearrange order of fields';
$lang['en_GB']['EditableCheckboxGroupField.ss']['LOCKED'] = 'These fields cannot be modified';
$lang['en_GB']['EditableCheckboxGroupField.ss']['MORE'] = 'More options';
$lang['en_GB']['EditableCheckboxGroupField.ss']['REQUIRED'] = 'This field is required for this form and cannot be deleted';
$lang['en_GB']['EditableCheckboxOption.ss']['DELETE'] = 'Remove this option';
$lang['en_GB']['EditableCheckboxOption.ss']['DRAG'] = 'Drag to rearrange order of options';
$lang['en_GB']['EditableCheckboxOption.ss']['LOCKED'] = 'These fields cannot be modified';
$lang['en_GB']['EditableDateField.ss']['DATE'] = 'Date Field';
$lang['en_GB']['EditableDateField.ss']['DELETE'] = 'Delete this field';
$lang['en_GB']['EditableDateField.ss']['DRAG'] = 'Drag to rearrange order of fields';
$lang['en_GB']['EditableDateField.ss']['MORE'] = 'More options';
$lang['en_GB']['EditableDropdown.ss']['ADD'] = 'Add new option';
$lang['en_GB']['EditableDropdown.ss']['DELETE'] = 'Delete this field';
$lang['en_GB']['EditableDropdown.ss']['DRAG'] = 'Drag to rearrange order of fields';
$lang['en_GB']['EditableDropdown.ss']['DROPDOWN'] = 'Dropdown box';
$lang['en_GB']['EditableDropdown.ss']['LOCKED'] = 'These fields cannot be modified';
$lang['en_GB']['EditableDropdown.ss']['MORE'] = 'More options';
$lang['en_GB']['EditableDropdown.ss']['REQUIRED'] = 'This field is required for this form and cannot be deleted';
$lang['en_GB']['EditableDropdownOption.ss']['DELETE'] = 'Remove this option';
$lang['en_GB']['EditableDropdownOption.ss']['DRAG'] = 'Drag to rearrange order of options';
$lang['en_GB']['EditableDropdownOption.ss']['LOCKED'] = 'These fields cannot be modified';
$lang['en_GB']['EditableEmailField']['SENDCOPY'] = 'Send copy of submission to this address';
$lang['en_GB']['EditableEmailField.ss']['DELETE'] = 'Delete this field';
$lang['en_GB']['EditableEmailField.ss']['DRAG'] = 'Drag to rearrange order of fields';
$lang['en_GB']['EditableEmailField.ss']['EMAIL'] = 'Email address field';
$lang['en_GB']['EditableEmailField.ss']['MORE'] = 'More options';
$lang['en_GB']['EditableEmailField.ss']['REQUIRED'] = 'This field is required for this form and cannot be deleted';
$lang['en_GB']['EditableFileField.ss']['DELETE'] = 'Delete this field';
$lang['en_GB']['EditableFileField.ss']['DRAG'] = 'Drag to rearrange order of fields';
$lang['en_GB']['EditableFileField.ss']['FILE'] = 'File upload field';
$lang['en_GB']['EditableFileField.ss']['MORE'] = 'More options';
$lang['en_GB']['EditableFormField']['ENTERQUESTION'] = 'Enter Question';
$lang['en_GB']['EditableFormField']['REQUIRED'] = 'Required?';
$lang['en_GB']['EditableFormField.ss']['DELETE'] = 'Delete this field';
$lang['en_GB']['EditableFormField.ss']['DRAG'] = 'Drag to rearrange order of fields';
$lang['en_GB']['EditableFormField.ss']['LOCKED'] = 'These fields cannot be modified';
$lang['en_GB']['EditableFormField.ss']['MORE'] = 'More options';
$lang['en_GB']['EditableFormField.ss']['REQUIRED'] = 'This field is required for this form and cannot be deleted';
$lang['en_GB']['EditableFormFieldOption.ss']['DELETE'] = 'Remove this option';
$lang['en_GB']['EditableFormFieldOption.ss']['DRAG'] = 'Drag to rearrange order of fields';
$lang['en_GB']['EditableFormFieldOption.ss']['LOCKED'] = 'These fields cannot be modified';
$lang['en_GB']['EditableFormHeading.ss']['DELETE'] = 'Delete this field';
$lang['en_GB']['EditableFormHeading.ss']['DRAG'] = 'Drag to rearrange order of fields';
$lang['en_GB']['EditableFormHeading.ss']['HEADING'] = 'Heading field';
$lang['en_GB']['EditableFormHeading.ss']['MORE'] = 'More options';
$lang['en_GB']['EditableRadioField.ss']['ADD'] = 'Add new option';
$lang['en_GB']['EditableRadioField.ss']['DELETE'] = 'Delete this field';
$lang['en_GB']['EditableRadioField.ss']['DRAG'] = 'Drag to rearrange order of fields';
$lang['en_GB']['EditableRadioField.ss']['LOCKED'] = 'These fields cannot be modified';
$lang['en_GB']['EditableRadioField.ss']['MORE'] = 'More options';
$lang['en_GB']['EditableRadioField.ss']['REQUIRED'] = 'This field is required for this form and cannot be deleted';
$lang['en_GB']['EditableRadioField.ss']['SET'] = 'Radio button set';
$lang['en_GB']['EditableRadioOption.ss']['DELETE'] = 'Remove this option';
$lang['en_GB']['EditableRadioOption.ss']['DRAG'] = 'Drag to rearrange order of options';
$lang['en_GB']['EditableRadioOption.ss']['LOCKED'] = 'These fields cannot be modified';
$lang['en_GB']['EditableTextField']['DEFAULTTEXT'] = 'Default Text';
$lang['en_GB']['EditableTextField']['NUMBERROWS'] = 'Number of rows';
$lang['en_GB']['EditableTextField.ss']['DELETE'] = 'Delete this field';
$lang['en_GB']['EditableTextField.ss']['DRAG'] = 'Drag to rearrange order of fields';
$lang['en_GB']['EditableTextField.ss']['MORE'] = 'More options';
$lang['en_GB']['EditableTextField.ss']['TEXTFIELD'] = 'Text Field';
$lang['en_GB']['EditableTextField']['TEXTBOXLENGTH'] = 'Length of text box';
$lang['en_GB']['EditableTextField']['TEXTLENGTH'] = 'Text length';
$lang['en_GB']['FieldEditor']['EMAILONSUBMIT'] = 'Email form on submit:';
$lang['en_GB']['FieldEditor']['EMAILSUBMISSION'] = 'Email submission to:';
$lang['en_GB']['FieldEditor.ss']['ADD'] = 'Add';
$lang['en_GB']['FieldEditor.ss']['CHECKBOX'] = 'Checkbox';
$lang['en_GB']['FieldEditor.ss']['CHECKBOXGROUP'] = 'Checkboxes';
$lang['en_GB']['FieldEditor.ss']['CHECKBOXGROUPTITLE'] = 'Add checkbox group field';
$lang['en_GB']['FieldEditor.ss']['CHECKBOXTITLE'] = 'Add checkbox';
$lang['en_GB']['FieldEditor.ss']['DATE'] = 'Date';
$lang['en_GB']['FieldEditor.ss']['DATETITLE'] = 'Add date heading';
$lang['en_GB']['FieldEditor.ss']['DROPDOWN'] = 'Dropdown';
$lang['en_GB']['FieldEditor.ss']['DROPDOWNTITLE'] = 'Add dropdown';
$lang['en_GB']['FieldEditor.ss']['EMAIL'] = 'Email';
$lang['en_GB']['FieldEditor.ss']['EMAILTITLE'] = 'Add email field';
$lang['en_GB']['FieldEditor.ss']['FILE'] = 'File';
$lang['en_GB']['FieldEditor.ss']['FILETITLE'] = 'Add file upload field';
$lang['en_GB']['FieldEditor.ss']['FORMHEADING'] = 'Heading';
$lang['en_GB']['FieldEditor.ss']['FORMHEADINGTITLE'] = 'Add form heading';
$lang['en_GB']['FieldEditor.ss']['MEMBER'] = 'Member List';
$lang['en_GB']['FieldEditor.ss']['MEMBERTITLE'] = 'Add member list field';
$lang['en_GB']['FieldEditor.ss']['RADIOSET'] = 'Radio';
$lang['en_GB']['FieldEditor.ss']['RADIOSETTITLE'] = 'Add radio button set';
$lang['en_GB']['FieldEditor.ss']['TEXT'] = 'Text';
$lang['en_GB']['FieldEditor.ss']['TEXTTITLE'] = 'Add text field';
$lang['en_GB']['SubmittedFormEmail.ss']['SUBMITTED'] = 'The following data was submitted to your website:';
$lang['en_GB']['SubmittedFormReportField.ss']['SUBMITTED'] = 'Submitted at';
$lang['en_GB']['UserDefinedForm']['FORM'] = 'Form';
$lang['en_GB']['UserDefinedForm']['NORESULTS'] = 'No matching results found';
$lang['en_GB']['UserDefinedForm']['ONCOMPLETE'] = 'On complete';
$lang['en_GB']['UserDefinedForm']['ONCOMPLETELABEL'] = 'Show on completion';
$lang['en_GB']['UserDefinedForm']['RECEIVED'] = 'Received Submissions';
$lang['en_GB']['UserDefinedForm']['SUBMISSIONS'] = 'Submissions';
$lang['en_GB']['UserDefinedForm']['SUBMIT'] = 'Submit';
$lang['en_GB']['UserDefinedForm']['TEXTONSUBMIT'] = 'Text on submit button:';
$lang['en_GB']['UserDefinedForm_SubmittedFormEmail']['EMAILSUBJECT'] = 'Submission of form';

?>