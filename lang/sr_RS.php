<?php

/**
 * Serbian (Serbia) language pack
 * @package modules: userforms
 * @subpackage i18n
 */

i18n::include_locale_file('modules: userforms', 'en_US');

global $lang;

if(array_key_exists('sr_RS', $lang) && is_array($lang['sr_RS'])) {
	$lang['sr_RS'] = array_merge($lang['en_US'], $lang['sr_RS']);
} else {
	$lang['sr_RS'] = $lang['en_US'];
}

$lang['sr_RS']['EditableCheckbox']['NOTSELECTED'] = 'Није изабран';
$lang['sr_RS']['EditableCheckbox']['SELECTED'] = 'Изабран';
$lang['sr_RS']['EditableCheckbox.ss']['DELETE'] = 'Обриши ово поље';
$lang['sr_RS']['EditableCheckbox.ss']['LOCKED'] = 'Ово поље не може да буде измењено';
$lang['sr_RS']['EditableCheckbox.ss']['MORE'] = 'Више опција';
$lang['sr_RS']['EditableCheckboxGroupField.ss']['ADD'] = 'Додај нову опцију';
$lang['sr_RS']['EditableCheckboxGroupField.ss']['DELETE'] = 'Обриши ово поље';
$lang['sr_RS']['EditableCheckboxGroupField.ss']['LOCKED'] = 'Ова поља не могу да буду измењена';
$lang['sr_RS']['EditableCheckboxGroupField.ss']['MORE'] = 'Више опција';
$lang['sr_RS']['EditableCheckboxOption.ss']['DELETE'] = 'Уклони ову опцију';
$lang['sr_RS']['EditableCheckboxOption.ss']['DRAG'] = 'Превуците да бисте распоредили опције';
$lang['sr_RS']['EditableCheckboxOption.ss']['LOCKED'] = 'Ова поља не могу да буду измењена';
$lang['sr_RS']['EditableDateField.ss']['DATE'] = 'Поље за датум';
$lang['sr_RS']['EditableDateField.ss']['DELETE'] = 'Обриши ово поље';
$lang['sr_RS']['EditableDateField.ss']['DRAG'] = 'Превуците да бисте распоредили поља';
$lang['sr_RS']['EditableDateField.ss']['MORE'] = 'Више опција';
$lang['sr_RS']['EditableDropdown.ss']['ADD'] = 'Додај нову опцију';
$lang['sr_RS']['EditableDropdown.ss']['DELETE'] = 'Обриши ово поље';
$lang['sr_RS']['EditableDropdown.ss']['DRAG'] = 'Превуците да бисте распоредили поља';
$lang['sr_RS']['EditableDropdown.ss']['LOCKED'] = 'Ова поља не могу да буду измењена';
$lang['sr_RS']['EditableDropdown.ss']['MORE'] = 'Више опција';
$lang['sr_RS']['EditableDropdownOption.ss']['DELETE'] = 'Уклони ову опцију';
$lang['sr_RS']['EditableDropdownOption.ss']['LOCKED'] = 'Ова поље не могу да буду измењена';
$lang['sr_RS']['EditableEmailField.ss']['DELETE'] = 'Обриши ово поље';
$lang['sr_RS']['EditableEmailField.ss']['EMAIL'] = 'Поље за имејл адресу';
$lang['sr_RS']['EditableEmailField.ss']['MORE'] = 'Више опција';
$lang['sr_RS']['EditableFileField.ss']['DELETE'] = 'Обриши ово поље';
$lang['sr_RS']['EditableFileField.ss']['FILE'] = 'Поље за достављање датотека';
$lang['sr_RS']['EditableFileField.ss']['MORE'] = 'Више опција';
$lang['sr_RS']['EditableFormField']['ENTERQUESTION'] = 'Унесите питање';
$lang['sr_RS']['EditableFormField']['REQUIRED'] = 'Захтевано?';
$lang['sr_RS']['EditableFormField.ss']['DELETE'] = 'Обриши ово поље';
$lang['sr_RS']['EditableFormField.ss']['MORE'] = 'Више опција';
$lang['sr_RS']['EditableFormFieldOption.ss']['DELETE'] = 'Уклони ову опцију';
$lang['sr_RS']['EditableFormFieldOption.ss']['LOCKED'] = 'Ова поља не могу да буду измењена';
$lang['sr_RS']['EditableFormHeading.ss']['DELETE'] = 'Обриши ово поље';
$lang['sr_RS']['EditableFormHeading.ss']['MORE'] = 'Више опција';
$lang['sr_RS']['EditableRadioField.ss']['ADD'] = 'Додај нову опцију';
$lang['sr_RS']['EditableRadioField.ss']['DELETE'] = 'Обриши ово поље';
$lang['sr_RS']['EditableRadioField.ss']['MORE'] = 'Више опција';
$lang['sr_RS']['EditableRadioOption.ss']['DELETE'] = 'Уклони ову опцију';
$lang['sr_RS']['EditableTextField']['DEFAULTTEXT'] = 'Подразумевани текст';
$lang['sr_RS']['EditableTextField']['NUMBERROWS'] = 'Број редова';
$lang['sr_RS']['EditableTextField.ss']['DELETE'] = 'Обриши ово поље';
$lang['sr_RS']['EditableTextField.ss']['MORE'] = 'Више опција';
$lang['sr_RS']['EditableTextField.ss']['TEXTFIELD'] = 'Текстуално поље';
$lang['sr_RS']['EditableTextField']['TEXTBOXLENGTH'] = 'Дужина текстуалног поља';
$lang['sr_RS']['EditableTextField']['TEXTLENGTH'] = 'Дужина текста';
$lang['sr_RS']['FieldEditor.ss']['ADD'] = 'Додај';
$lang['sr_RS']['FieldEditor.ss']['DATE'] = 'Датум';
$lang['sr_RS']['FieldEditor.ss']['EMAIL'] = 'Имејл';
$lang['sr_RS']['FieldEditor.ss']['EMAILTITLE'] = 'Додај поље за имејл';
$lang['sr_RS']['FieldEditor.ss']['FILE'] = 'Датотека';
$lang['sr_RS']['FieldEditor.ss']['FILETITLE'] = 'Додај поље за достављање датотека';
$lang['sr_RS']['FieldEditor.ss']['FORMHEADING'] = 'Наслов';
$lang['sr_RS']['FieldEditor.ss']['MEMBER'] = 'Списак чланова';
$lang['sr_RS']['FieldEditor.ss']['TEXT'] = 'Текст';
$lang['sr_RS']['FieldEditor.ss']['TEXTTITLE'] = 'Додај текстуално поље';
$lang['sr_RS']['SubmittedFormEmail.ss']['SUBMITTED'] = 'Следећи подацу су послати на ваш сајт:';
$lang['sr_RS']['UserDefinedForm']['NORESULTS'] = 'Нема пронађених резултата';
$lang['sr_RS']['UserDefinedForm']['SUBMIT'] = 'Сачувај';

?>