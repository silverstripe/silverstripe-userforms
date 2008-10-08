<?php

/**
 * Bulgarian (Bulgaria) language pack
 * @package modules: userforms
 * @subpackage i18n
 */

i18n::include_locale_file('modules: userforms', 'en_US');

global $lang;

if(array_key_exists('bg_BG', $lang) && is_array($lang['bg_BG'])) {
	$lang['bg_BG'] = array_merge($lang['en_US'], $lang['bg_BG']);
} else {
	$lang['bg_BG'] = $lang['en_US'];
}

$lang['bg_BG']['EditableCheckbox']['ANY'] = 'Всеки';
$lang['bg_BG']['EditableCheckbox']['NOTSELECTED'] = 'Не избрано';
$lang['bg_BG']['EditableCheckbox']['SELECTED'] = 'Избрано';
$lang['bg_BG']['EditableCheckbox.ss']['CHECKBOX'] = 'Поле за отметка';
$lang['bg_BG']['EditableCheckbox.ss']['DELETE'] = 'Изтрий това поле';
$lang['bg_BG']['EditableCheckbox.ss']['DRAG'] = 'Плъзнете, за да подредите реда на полетата';
$lang['bg_BG']['EditableCheckbox.ss']['LOCKED'] = 'Това поле неможе да бъде променено';
$lang['bg_BG']['EditableCheckbox.ss']['MORE'] = 'Повече Опций';
$lang['bg_BG']['EditableCheckboxGroupField.ss']['ADD'] = 'Добави нова опция';
$lang['bg_BG']['EditableCheckboxGroupField.ss']['CHECKBOXGROUP'] = 'Група отметки';
$lang['bg_BG']['EditableCheckboxGroupField.ss']['DELETE'] = 'Изтрий това поле';
$lang['bg_BG']['EditableCheckboxGroupField.ss']['DRAG'] = 'Плъзнете, за да подредите реда на полетата';
$lang['bg_BG']['EditableCheckboxGroupField.ss']['LOCKED'] = 'Това поле неможе да бъде променено';
$lang['bg_BG']['EditableCheckboxGroupField.ss']['MORE'] = 'Повече опций ';
$lang['bg_BG']['EditableCheckboxGroupField.ss']['REQUIRED'] = 'Това поле е задължително за този формуляр и неможе да бъде изтрито';
$lang['bg_BG']['EditableCheckboxOption.ss']['DELETE'] = 'Премахни тази опция';
$lang['bg_BG']['EditableCheckboxOption.ss']['DRAG'] = 'Плъзнете, за да подредите реда на опцийте';
$lang['bg_BG']['EditableCheckboxOption.ss']['LOCKED'] = 'Тези полета немогат да бъдат променени';
$lang['bg_BG']['EditableDateField.ss']['DATE'] = 'Дата поле';
$lang['bg_BG']['EditableDateField.ss']['DELETE'] = 'Изтрий това поле';
$lang['bg_BG']['EditableDateField.ss']['DRAG'] = 'Плъзнете, за да подредите реда на полетата';
$lang['bg_BG']['EditableDateField.ss']['MORE'] = 'Повече опций';
$lang['bg_BG']['EditableDropdown.ss']['ADD'] = 'Добави нова опция';
$lang['bg_BG']['EditableDropdown.ss']['DELETE'] = 'Изтрий това поле';
$lang['bg_BG']['EditableDropdown.ss']['DRAG'] = 'Плъзнете, за да подредите реда на полетата';
$lang['bg_BG']['EditableDropdown.ss']['DROPDOWN'] = 'Падащо поле';
$lang['bg_BG']['EditableDropdown.ss']['LOCKED'] = 'Тези полета неможе да бъдат променени';
$lang['bg_BG']['EditableDropdown.ss']['MORE'] = 'Повече опций';
$lang['bg_BG']['EditableDropdown.ss']['REQUIRED'] = 'Това поле е задължително за този формуляр и неможе да бъде изтрито';
$lang['bg_BG']['EditableDropdownOption.ss']['DELETE'] = 'Премахни тази опция';
$lang['bg_BG']['EditableDropdownOption.ss']['DRAG'] = 'Плъзнете, за да подредите реда на опцийте';
$lang['bg_BG']['EditableDropdownOption.ss']['LOCKED'] = 'Тези полета неможе да бъдат променени';
$lang['bg_BG']['EditableEmailField']['SENDCOPY'] = 'Изпрати копие на подаването на този адрес';
$lang['bg_BG']['EditableEmailField.ss']['DELETE'] = 'Изтрий това поле';
$lang['bg_BG']['EditableEmailField.ss']['DRAG'] = 'Плъзнете, за да подредите реда на полетата';
$lang['bg_BG']['EditableEmailField.ss']['EMAIL'] = 'Мейл адрес поле';
$lang['bg_BG']['EditableEmailField.ss']['MORE'] = 'Повече опций';
$lang['bg_BG']['EditableEmailField.ss']['REQUIRED'] = 'Това поле е задължително за този формуляр и неможе да бъде изтрито';
$lang['bg_BG']['EditableFileField.ss']['DELETE'] = 'Изтрий това поле';
$lang['bg_BG']['EditableFileField.ss']['DRAG'] = 'Плъзнете, за да подредите реда на полетата';
$lang['bg_BG']['EditableFileField.ss']['FILE'] = 'Поле за качване на файлове';
$lang['bg_BG']['EditableFileField.ss']['MORE'] = 'Повече опций';
$lang['bg_BG']['EditableFormField']['ENTERQUESTION'] = 'Въведи Въпрос';
$lang['bg_BG']['EditableFormField']['REQUIRED'] = 'Задължително?';
$lang['bg_BG']['EditableFormField.ss']['DELETE'] = 'Изтрий това поле';
$lang['bg_BG']['EditableFormField.ss']['DRAG'] = 'Плъзнете, за да подредите реда на полетата';
$lang['bg_BG']['EditableFormField.ss']['LOCKED'] = 'Тези полета неможе да бъдат променени';
$lang['bg_BG']['EditableFormField.ss']['MORE'] = 'Повече опций';
$lang['bg_BG']['EditableFormField.ss']['REQUIRED'] = 'Това поле е задължително за този формуляр и неможе да бъде изтрито';
$lang['bg_BG']['EditableFormFieldOption.ss']['DELETE'] = 'Премахни тази опция';
$lang['bg_BG']['EditableFormFieldOption.ss']['DRAG'] = 'Плъзнете, за да подредите реда на полетата';
$lang['bg_BG']['EditableFormFieldOption.ss']['LOCKED'] = 'Тези полета неможе да бъдат променени';
$lang['bg_BG']['EditableFormHeading.ss']['DELETE'] = 'Изтрий това поле';
$lang['bg_BG']['EditableFormHeading.ss']['DRAG'] = 'Плъзнете, за да подредите реда на полетата';
$lang['bg_BG']['EditableFormHeading.ss']['HEADING'] = 'Рубрика поле';
$lang['bg_BG']['EditableFormHeading.ss']['MORE'] = 'Повече опций';
$lang['bg_BG']['EditableRadioField.ss']['ADD'] = 'Добави нова опция';
$lang['bg_BG']['EditableRadioField.ss']['DELETE'] = 'Изтрий това поле';
$lang['bg_BG']['EditableRadioField.ss']['DRAG'] = 'Плъзнете, за да подредите реда на полетата';
$lang['bg_BG']['EditableRadioField.ss']['LOCKED'] = 'Тези полета неможе да бъдат промени';
$lang['bg_BG']['EditableRadioField.ss']['MORE'] = 'Повече опций';
$lang['bg_BG']['EditableRadioField.ss']['REQUIRED'] = 'Това поле е задължително за този формуляр и неможе да бъде изтрито';
$lang['bg_BG']['EditableRadioField.ss']['SET'] = 'Нагласа за Радио бутон';
$lang['bg_BG']['EditableRadioOption.ss']['DELETE'] = 'Премахни тази опция';
$lang['bg_BG']['EditableRadioOption.ss']['DRAG'] = 'Пъзнете, за да подредите реда на опцийте';
$lang['bg_BG']['EditableRadioOption.ss']['LOCKED'] = 'Тези полета неможе да бъдат променени';
$lang['bg_BG']['EditableTextField']['DEFAULTTEXT'] = 'Текст по подразбиране';
$lang['bg_BG']['EditableTextField']['NUMBERROWS'] = 'Номер на редове';
$lang['bg_BG']['EditableTextField.ss']['DELETE'] = 'Изтрий това поле';
$lang['bg_BG']['EditableTextField.ss']['DRAG'] = 'Плъзнете, за да подредите реда на полетата';
$lang['bg_BG']['EditableTextField.ss']['MORE'] = 'Повече опций';
$lang['bg_BG']['EditableTextField.ss']['TEXTFIELD'] = 'Текст поле';
$lang['bg_BG']['EditableTextField']['TEXTBOXLENGTH'] = 'Дължина на текстовото поле';
$lang['bg_BG']['EditableTextField']['TEXTLENGTH'] = 'Дължина на Текст';
$lang['bg_BG']['FieldEditor']['EMAILONSUBMIT'] = 'Мейл формуляр за пращане:';
$lang['bg_BG']['FieldEditor']['EMAILSUBMISSION'] = 'Мейл изпращане към:';
$lang['bg_BG']['FieldEditor.ss']['ADD'] = 'Добави';
$lang['bg_BG']['FieldEditor.ss']['CHECKBOX'] = 'Отметка';
$lang['bg_BG']['FieldEditor.ss']['CHECKBOXGROUP'] = 'Отметки';
$lang['bg_BG']['FieldEditor.ss']['CHECKBOXGROUPTITLE'] = 'Добави поле за група отметки';
$lang['bg_BG']['FieldEditor.ss']['CHECKBOXTITLE'] = 'Добави отметка';
$lang['bg_BG']['FieldEditor.ss']['DATE'] = 'Дата';
$lang['bg_BG']['FieldEditor.ss']['DATETITLE'] = 'Добави заглавие за дата';
$lang['bg_BG']['FieldEditor.ss']['DROPDOWN'] = 'Падащо';
$lang['bg_BG']['FieldEditor.ss']['DROPDOWNTITLE'] = 'Добави падащо';
$lang['bg_BG']['FieldEditor.ss']['EMAIL'] = 'Мейл';
$lang['bg_BG']['FieldEditor.ss']['EMAILTITLE'] = 'Добави мейл поле';
$lang['bg_BG']['FieldEditor.ss']['FILE'] = 'Файл';
$lang['bg_BG']['FieldEditor.ss']['FILETITLE'] = 'Добави поле за качване на файл';
$lang['bg_BG']['FieldEditor.ss']['FORMHEADING'] = 'Заглавие';
$lang['bg_BG']['FieldEditor.ss']['FORMHEADINGTITLE'] = 'Добави заглавие за формуляр';
$lang['bg_BG']['FieldEditor.ss']['MEMBER'] = 'Списък с потребители';
$lang['bg_BG']['FieldEditor.ss']['MEMBERTITLE'] = 'Добави поле за потребителски списък';
$lang['bg_BG']['FieldEditor.ss']['RADIOSET'] = 'Радио';
$lang['bg_BG']['FieldEditor.ss']['RADIOSETTITLE'] = 'Добави нагласа за радио бутон';
$lang['bg_BG']['FieldEditor.ss']['TEXT'] = 'Текст';
$lang['bg_BG']['FieldEditor.ss']['TEXTTITLE'] = 'Добави текст поле';
$lang['bg_BG']['SubmittedFormEmail.ss']['SUBMITTED'] = 'Следващите данни бяха пратени на вашият сайт:';
$lang['bg_BG']['SubmittedFormReportField.ss']['SUBMITTED'] = 'Изпратено на';
$lang['bg_BG']['UserDefinedForm']['FORM'] = 'Формуляр';
$lang['bg_BG']['UserDefinedForm']['NORESULTS'] = 'Не са открити никакви резултати';
$lang['bg_BG']['UserDefinedForm']['ONCOMPLETE'] = 'При завършване';
$lang['bg_BG']['UserDefinedForm']['ONCOMPLETELABEL'] = 'Покажи при завършване';
$lang['bg_BG']['UserDefinedForm']['RECEIVED'] = 'Получени Заявления';
$lang['bg_BG']['UserDefinedForm']['SUBMISSIONS'] = 'Заявления';
$lang['bg_BG']['UserDefinedForm']['SUBMIT'] = 'Изпрати';
$lang['bg_BG']['UserDefinedForm']['TEXTONSUBMIT'] = 'Текст за бутона на изпращане:';
$lang['bg_BG']['UserDefinedForm_SubmittedFormEmail']['EMAILSUBJECT'] = 'Подаване на формуляра';

?>