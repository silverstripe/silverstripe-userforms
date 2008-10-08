<?php

/**
 * Russian (Russia) language pack
 * @package modules: userforms
 * @subpackage i18n
 */

i18n::include_locale_file('modules: userforms', 'en_US');

global $lang;

if(array_key_exists('ru_RU', $lang) && is_array($lang['ru_RU'])) {
	$lang['ru_RU'] = array_merge($lang['en_US'], $lang['ru_RU']);
} else {
	$lang['ru_RU'] = $lang['en_US'];
}

$lang['ru_RU']['EditableCheckbox']['ANY'] = 'Любое';
$lang['ru_RU']['EditableCheckbox']['NOTSELECTED'] = 'Не выбрано';
$lang['ru_RU']['EditableCheckbox']['SELECTED'] = 'Выбрано';
$lang['ru_RU']['EditableCheckbox.ss']['CHECKBOX'] = 'Флаг множественного выбора';
$lang['ru_RU']['EditableCheckbox.ss']['DELETE'] = 'Удалить это поле';
$lang['ru_RU']['EditableCheckbox.ss']['DRAG'] = 'Перетащите для изменения порядка полей';
$lang['ru_RU']['EditableCheckbox.ss']['LOCKED'] = 'Это поле не может быть изменено';
$lang['ru_RU']['EditableCheckbox.ss']['MORE'] = 'Другие настройки';
$lang['ru_RU']['EditableCheckboxGroupField.ss']['ADD'] = 'Добавить новый элемент';
$lang['ru_RU']['EditableCheckboxGroupField.ss']['CHECKBOXGROUP'] = 'Группа флагов выбора (checkbox)';
$lang['ru_RU']['EditableCheckboxGroupField.ss']['DELETE'] = 'Удалить это поле';
$lang['ru_RU']['EditableCheckboxGroupField.ss']['DRAG'] = 'Перетащите для изменения порядка полей';
$lang['ru_RU']['EditableCheckboxGroupField.ss']['LOCKED'] = 'Эти поля не могут быть изменены';
$lang['ru_RU']['EditableCheckboxGroupField.ss']['MORE'] = 'Другие настройки';
$lang['ru_RU']['EditableCheckboxGroupField.ss']['REQUIRED'] = 'Это поле обязательно для этой формы и не может быть удалено';
$lang['ru_RU']['EditableCheckboxOption.ss']['DELETE'] = 'Удалить этот элемент';
$lang['ru_RU']['EditableCheckboxOption.ss']['DRAG'] = 'Перетащите для изменения порядка элементов';
$lang['ru_RU']['EditableCheckboxOption.ss']['LOCKED'] = 'Эти поля не могут быть изменены';
$lang['ru_RU']['EditableDateField.ss']['DATE'] = 'Поле даты';
$lang['ru_RU']['EditableDateField.ss']['DELETE'] = 'Удалить это поле';
$lang['ru_RU']['EditableDateField.ss']['DRAG'] = 'Перетащите для изменения порядка полей';
$lang['ru_RU']['EditableDateField.ss']['MORE'] = 'Другие настройки';
$lang['ru_RU']['EditableDropdown.ss']['ADD'] = 'Добавить элемент списка';
$lang['ru_RU']['EditableDropdown.ss']['DELETE'] = 'Удалить это поле';
$lang['ru_RU']['EditableDropdown.ss']['DRAG'] = 'Перетащите для изменения порядка полей';
$lang['ru_RU']['EditableDropdown.ss']['DROPDOWN'] = 'Выпадающий список';
$lang['ru_RU']['EditableDropdown.ss']['LOCKED'] = 'Эти поля не могут быть изменены';
$lang['ru_RU']['EditableDropdown.ss']['MORE'] = 'Другие настройки';
$lang['ru_RU']['EditableDropdown.ss']['REQUIRED'] = 'Это поле обязательно для этой формы и не может быть удалено';
$lang['ru_RU']['EditableDropdownOption.ss']['DELETE'] = 'Удалить этот элемент';
$lang['ru_RU']['EditableDropdownOption.ss']['DRAG'] = 'Перетащите для изменения порядка элементов';
$lang['ru_RU']['EditableDropdownOption.ss']['LOCKED'] = 'Эти поля не могут быть изменены';
$lang['ru_RU']['EditableEmailField']['SENDCOPY'] = 'Отправить копию полученной формы на этот адрес';
$lang['ru_RU']['EditableEmailField.ss']['DELETE'] = 'Удалить это поле';
$lang['ru_RU']['EditableEmailField.ss']['DRAG'] = 'Перетащите для изменения порядка полей';
$lang['ru_RU']['EditableEmailField.ss']['EMAIL'] = 'Поле адреса email';
$lang['ru_RU']['EditableEmailField.ss']['MORE'] = 'Другие настройки';
$lang['ru_RU']['EditableEmailField.ss']['REQUIRED'] = 'Это поле обязательно для этой формы и не может быть удалено';
$lang['ru_RU']['EditableFileField.ss']['DELETE'] = 'Удалить это поле';
$lang['ru_RU']['EditableFileField.ss']['DRAG'] = 'Перетащите для изменения порядка полей';
$lang['ru_RU']['EditableFileField.ss']['FILE'] = 'Поле загрузки файла';
$lang['ru_RU']['EditableFileField.ss']['MORE'] = 'Другие настройки';
$lang['ru_RU']['EditableFormField']['ENTERQUESTION'] = 'Введите вопрос';
$lang['ru_RU']['EditableFormField']['REQUIRED'] = 'Обязательно?';
$lang['ru_RU']['EditableFormField.ss']['DELETE'] = 'Удалить это поле';
$lang['ru_RU']['EditableFormField.ss']['DRAG'] = 'Перетащите для изменения порядка полей';
$lang['ru_RU']['EditableFormField.ss']['LOCKED'] = 'Это поле не может быть изменено';
$lang['ru_RU']['EditableFormField.ss']['MORE'] = 'Другие настройки';
$lang['ru_RU']['EditableFormField.ss']['REQUIRED'] = 'Это поле обязательно для этой формы и не может быть удалено';
$lang['ru_RU']['EditableFormFieldOption.ss']['DELETE'] = 'Удалить этот элемент';
$lang['ru_RU']['EditableFormFieldOption.ss']['DRAG'] = 'Перетащите для изменения порядка полей';
$lang['ru_RU']['EditableFormFieldOption.ss']['LOCKED'] = 'Эти поля не могут быть изменены';
$lang['ru_RU']['EditableFormHeading.ss']['DELETE'] = 'Удалить это поле';
$lang['ru_RU']['EditableFormHeading.ss']['DRAG'] = 'Перетащите для изменения порядка полей';
$lang['ru_RU']['EditableFormHeading.ss']['HEADING'] = 'Поле заголовка';
$lang['ru_RU']['EditableFormHeading.ss']['MORE'] = 'Другие настройки';
$lang['ru_RU']['EditableRadioField.ss']['ADD'] = 'Добавить элемент';
$lang['ru_RU']['EditableRadioField.ss']['DELETE'] = 'Удалить это поле';
$lang['ru_RU']['EditableRadioField.ss']['DRAG'] = 'Перетащите для изменения порядка полей';
$lang['ru_RU']['EditableRadioField.ss']['LOCKED'] = 'Эти поля не могут быть изменены';
$lang['ru_RU']['EditableRadioField.ss']['MORE'] = 'Другие настройки';
$lang['ru_RU']['EditableRadioField.ss']['REQUIRED'] = 'Это поле обязательно для этой формы и не может быть удалено';
$lang['ru_RU']['EditableRadioField.ss']['SET'] = 'Набор radio-кнопок';
$lang['ru_RU']['EditableRadioOption.ss']['DELETE'] = 'Удалить этот элемент';
$lang['ru_RU']['EditableRadioOption.ss']['DRAG'] = 'Перетащите для изменения порядка элементов';
$lang['ru_RU']['EditableRadioOption.ss']['LOCKED'] = 'Эти поля не могут быть удалены';
$lang['ru_RU']['EditableTextField']['DEFAULTTEXT'] = 'Текст по умолчанию';
$lang['ru_RU']['EditableTextField']['NUMBERROWS'] = 'Кол-во строк';
$lang['ru_RU']['EditableTextField.ss']['DELETE'] = 'Удалить это поле';
$lang['ru_RU']['EditableTextField.ss']['DRAG'] = 'Перетащите для изменения порядка полей';
$lang['ru_RU']['EditableTextField.ss']['MORE'] = 'Другие настройки';
$lang['ru_RU']['EditableTextField.ss']['TEXTFIELD'] = 'Текстовое поле';
$lang['ru_RU']['EditableTextField']['TEXTBOXLENGTH'] = 'Длина текстового поля';
$lang['ru_RU']['EditableTextField']['TEXTLENGTH'] = 'Длина текста';
$lang['ru_RU']['FieldEditor']['EMAILONSUBMIT'] = 'Отправлять заполненную форму на email:';
$lang['ru_RU']['FieldEditor']['EMAILSUBMISSION'] = 'Отправить полученную форму на email:';
$lang['ru_RU']['FieldEditor.ss']['ADD'] = 'Добавить';
$lang['ru_RU']['FieldEditor.ss']['CHECKBOX'] = 'Флаг (checkbox)';
$lang['ru_RU']['FieldEditor.ss']['CHECKBOXGROUP'] = 'Флаги (checkbox)';
$lang['ru_RU']['FieldEditor.ss']['CHECKBOXGROUPTITLE'] = 'Добавить группу флагов (checkbox)';
$lang['ru_RU']['FieldEditor.ss']['CHECKBOXTITLE'] = 'Добавить флаг (checkbox)';
$lang['ru_RU']['FieldEditor.ss']['DATE'] = 'Дата';
$lang['ru_RU']['FieldEditor.ss']['DATETITLE'] = 'Добавить заголовок даты';
$lang['ru_RU']['FieldEditor.ss']['DROPDOWN'] = 'Выпадающий список';
$lang['ru_RU']['FieldEditor.ss']['DROPDOWNTITLE'] = 'Добавить выпадающий список';
$lang['ru_RU']['FieldEditor.ss']['EMAIL'] = 'Email адрес';
$lang['ru_RU']['FieldEditor.ss']['EMAILTITLE'] = 'Добавить поле email адреса';
$lang['ru_RU']['FieldEditor.ss']['FILE'] = 'Файл';
$lang['ru_RU']['FieldEditor.ss']['FILETITLE'] = 'Добавить поле загрузки файла';
$lang['ru_RU']['FieldEditor.ss']['FORMHEADING'] = 'Заголовок';
$lang['ru_RU']['FieldEditor.ss']['FORMHEADINGTITLE'] = 'Добавить заголовок формы';
$lang['ru_RU']['FieldEditor.ss']['MEMBER'] = 'Список участников';
$lang['ru_RU']['FieldEditor.ss']['MEMBERTITLE'] = 'Добавить поле списка участников';
$lang['ru_RU']['FieldEditor.ss']['RADIOSET'] = 'Набор radio-кнопок';
$lang['ru_RU']['FieldEditor.ss']['RADIOSETTITLE'] = 'Добавить набор radio-кнопок';
$lang['ru_RU']['FieldEditor.ss']['TEXT'] = 'Текст';
$lang['ru_RU']['FieldEditor.ss']['TEXTTITLE'] = 'Добавить текстовое поле';
$lang['ru_RU']['SubmittedFormEmail.ss']['SUBMITTED'] = 'На Ваш сайт были отправлены следующие данные: ';
$lang['ru_RU']['SubmittedFormReportField.ss']['SUBMITTED'] = 'Получено ';
$lang['ru_RU']['UserDefinedForm']['FORM'] = 'Форма';
$lang['ru_RU']['UserDefinedForm']['NORESULTS'] = 'Ничего не найдено';
$lang['ru_RU']['UserDefinedForm']['ONCOMPLETE'] = 'После отправки';
$lang['ru_RU']['UserDefinedForm']['ONCOMPLETELABEL'] = 'Показать после отправки';
$lang['ru_RU']['UserDefinedForm']['RECEIVED'] = 'Получено из формы';
$lang['ru_RU']['UserDefinedForm']['SUBMISSIONS'] = 'Полученное';
$lang['ru_RU']['UserDefinedForm']['SUBMIT'] = 'Отправить';
$lang['ru_RU']['UserDefinedForm']['TEXTONSUBMIT'] = 'Текст на кнопке отправки';
$lang['ru_RU']['UserDefinedForm_SubmittedFormEmail']['EMAILSUBJECT'] = 'Получено из формы';

?>