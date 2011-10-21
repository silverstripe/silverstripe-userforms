<?php

/**
 * Polish (Poland) language pack
 * @package modules: userforms
 * @subpackage i18n
 */

i18n::include_locale_file('modules: userforms', 'en_US');

global $lang;

if(array_key_exists('pl_PL', $lang) && is_array($lang['pl_PL'])) {
	$lang['pl_PL'] = array_merge($lang['en_US'], $lang['pl_PL']);
} else {
	$lang['pl_PL'] = $lang['en_US'];
}

$lang['pl_PL']['EditableCheckbox']['ANY'] = 'Dowolny';
$lang['pl_PL']['EditableCheckbox']['NOTSELECTED'] = 'Nie zaznaczony';
$lang['pl_PL']['EditableCheckbox']['SELECTED'] = 'Zaznaczony';
$lang['pl_PL']['EditableCheckbox.ss']['CHECKBOX'] = 'Pole wielokrotnego wyboru';
$lang['pl_PL']['EditableCheckbox.ss']['DELETE'] = 'Usuń to pole';
$lang['pl_PL']['EditableCheckbox.ss']['DRAG'] = 'Przeciągnij aby zmienić kolejność pól';
$lang['pl_PL']['EditableCheckbox.ss']['LOCKED'] = 'To pole nie może być zmodyfikowane';
$lang['pl_PL']['EditableCheckbox.ss']['MORE'] = 'Więcej opcji';
$lang['pl_PL']['EditableCheckboxGroupField.ss']['ADD'] = 'Dodaj nową opcję';
$lang['pl_PL']['EditableCheckboxGroupField.ss']['CHECKBOXGROUP'] = 'Grupa pól wielokrotnego wyboru';
$lang['pl_PL']['EditableCheckboxGroupField.ss']['DELETE'] = 'Usuń to pole';
$lang['pl_PL']['EditableCheckboxGroupField.ss']['DRAG'] = 'Przeciągnij aby zmienić kolejność pól';
$lang['pl_PL']['EditableCheckboxGroupField.ss']['LOCKED'] = 'Te pola nie mogą być modyfikowane';
$lang['pl_PL']['EditableCheckboxGroupField.ss']['MORE'] = 'Więcej opcji';
$lang['pl_PL']['EditableCheckboxGroupField.ss']['REQUIRED'] = 'To pole jest wymagane w tym formularzu i nie może być usunięte';
$lang['pl_PL']['EditableCheckboxOption.ss']['DELETE'] = 'Usuń tę opcję';
$lang['pl_PL']['EditableCheckboxOption.ss']['DRAG'] = 'Przeciągnij aby zmienić kolejność pól';
$lang['pl_PL']['EditableCheckboxOption.ss']['LOCKED'] = 'Te pola nie mogą być modyfikowane';
$lang['pl_PL']['EditableDateField.ss']['DATE'] = 'Pole daty';
$lang['pl_PL']['EditableDateField.ss']['DELETE'] = 'Usuń to pole';
$lang['pl_PL']['EditableDateField.ss']['DRAG'] = 'Przeciągnij aby zmienić kolejność pól';
$lang['pl_PL']['EditableDateField.ss']['MORE'] = 'Więcej opcji';
$lang['pl_PL']['EditableDropdown.ss']['ADD'] = 'Dodaj nową opcję';
$lang['pl_PL']['EditableDropdown.ss']['DELETE'] = 'Usuń to pole';
$lang['pl_PL']['EditableDropdown.ss']['DRAG'] = 'Przeciągnij aby zmienić kolejność pól';
$lang['pl_PL']['EditableDropdown.ss']['DROPDOWN'] = 'Pole rozwijane';
$lang['pl_PL']['EditableDropdown.ss']['LOCKED'] = 'Te pola nie mogą być modyfikowane';
$lang['pl_PL']['EditableDropdown.ss']['MORE'] = 'Więcej opcji';
$lang['pl_PL']['EditableDropdown.ss']['REQUIRED'] = 'To pole jest wymagane w tym formularzu i nie może zostać usunięte';
$lang['pl_PL']['EditableDropdownOption.ss']['DELETE'] = 'Usuń tą opcję';
$lang['pl_PL']['EditableDropdownOption.ss']['DRAG'] = 'Przeciągnij aby zmienić kolejność pól';
$lang['pl_PL']['EditableDropdownOption.ss']['LOCKED'] = 'Te pola nie mogą być modyfikowane';
$lang['pl_PL']['EditableEmailField']['SENDCOPY'] = 'Wyślij kopię na ten adres';
$lang['pl_PL']['EditableEmailField.ss']['DELETE'] = 'Usuń to pole';
$lang['pl_PL']['EditableEmailField.ss']['DRAG'] = 'Przeciągnij aby zmienić kolejność pól';
$lang['pl_PL']['EditableEmailField.ss']['EMAIL'] = 'Pole adresu e-mail';
$lang['pl_PL']['EditableEmailField.ss']['MORE'] = 'Więcej opcji';
$lang['pl_PL']['EditableEmailField.ss']['REQUIRED'] = 'To pole jest wymagane w tym formularzu i nie może być usunięte';
$lang['pl_PL']['EditableFileField.ss']['DELETE'] = 'Usuń to pole';
$lang['pl_PL']['EditableFileField.ss']['DRAG'] = 'Przeciągnij aby zmienić kolejność pól';
$lang['pl_PL']['EditableFileField.ss']['FILE'] = 'Pole wysyłania plików';
$lang['pl_PL']['EditableFileField.ss']['MORE'] = 'Więcej opcji';
$lang['pl_PL']['EditableFormField']['ENTERQUESTION'] = 'Wpisz pytanie';
$lang['pl_PL']['EditableFormField']['REQUIRED'] = 'Wymagane?';
$lang['pl_PL']['EditableFormField.ss']['DELETE'] = 'Usuń to pole';
$lang['pl_PL']['EditableFormField.ss']['DRAG'] = 'Przeciągnij aby zmienić kolejność pól';
$lang['pl_PL']['EditableFormField.ss']['LOCKED'] = 'Te pola nie mogą być modyfikowane';
$lang['pl_PL']['EditableFormField.ss']['MORE'] = 'Więcej opcji';
$lang['pl_PL']['EditableFormField.ss']['REQUIRED'] = 'To pole jest wymagane w tym formularzu i nie może zostać usunięte';
$lang['pl_PL']['EditableFormFieldOption.ss']['DELETE'] = 'Usuń tą opcję';
$lang['pl_PL']['EditableFormFieldOption.ss']['DRAG'] = 'Przeciągnij aby zmienić kolejność pól';
$lang['pl_PL']['EditableFormFieldOption.ss']['LOCKED'] = 'Te pola nie mogą zostać zmodyfikowane';
$lang['pl_PL']['EditableFormHeading.ss']['DELETE'] = 'Usuń to pole';
$lang['pl_PL']['EditableFormHeading.ss']['DRAG'] = 'Przeciągnij aby zmienić kolejność pól';
$lang['pl_PL']['EditableFormHeading.ss']['HEADING'] = 'Pole nagłówka';
$lang['pl_PL']['EditableFormHeading.ss']['MORE'] = 'Więcej opcji';
$lang['pl_PL']['EditableRadioField.ss']['ADD'] = 'Dodaj nową opcję';
$lang['pl_PL']['EditableRadioField.ss']['DELETE'] = 'Usuń to pole';
$lang['pl_PL']['EditableRadioField.ss']['DRAG'] = 'Przeciągnij aby zmienić kolejność pól';
$lang['pl_PL']['EditableRadioField.ss']['LOCKED'] = 'Te pola nie mogą być modyfikowane';
$lang['pl_PL']['EditableRadioField.ss']['MORE'] = 'Więcej opcji';
$lang['pl_PL']['EditableRadioField.ss']['REQUIRED'] = 'To pole jest wymagane w tym formularzu i nie może zostać usunięte';
$lang['pl_PL']['EditableRadioField.ss']['SET'] = 'Zestaw pól jednokrotnego wyboru.';
$lang['pl_PL']['EditableRadioOption.ss']['DELETE'] = 'Usuń tę opcję';
$lang['pl_PL']['EditableRadioOption.ss']['DRAG'] = 'Przeciągnij aby zmienić kolejność pól';
$lang['pl_PL']['EditableRadioOption.ss']['LOCKED'] = 'Te pola nie mogą być modyfikowane';
$lang['pl_PL']['EditableTextField']['DEFAULTTEXT'] = 'Domyślny tekst';
$lang['pl_PL']['EditableTextField']['NUMBERROWS'] = 'Ilość wierszy';
$lang['pl_PL']['EditableTextField.ss']['DELETE'] = 'Usuń to pole';
$lang['pl_PL']['EditableTextField.ss']['DRAG'] = 'Przeciągnij aby zmienić kolejność pól';
$lang['pl_PL']['EditableTextField.ss']['MORE'] = 'Więcej opcji';
$lang['pl_PL']['EditableTextField.ss']['TEXTFIELD'] = 'Pole tekstowe';
$lang['pl_PL']['EditableTextField']['TEXTBOXLENGTH'] = 'Długość pola tekstowego';
$lang['pl_PL']['EditableTextField']['TEXTLENGTH'] = 'Długość tekstu';
$lang['pl_PL']['FieldEditor']['EMAILONSUBMIT'] = 'Po wysłaniu formularza email:';
$lang['pl_PL']['FieldEditor']['EMAILSUBMISSION'] = 'Wyślij kopię na adres e-mail:';
$lang['pl_PL']['FieldEditor.ss']['ADD'] = 'Dodaj';
$lang['pl_PL']['FieldEditor.ss']['CHECKBOX'] = 'Pole wielokrotnego wyboru';
$lang['pl_PL']['FieldEditor.ss']['CHECKBOXGROUP'] = 'Pola wielokrotnego wyboru';
$lang['pl_PL']['FieldEditor.ss']['CHECKBOXGROUPTITLE'] = 'Dodaj zestaw pól wielokrotnego wyboru';
$lang['pl_PL']['FieldEditor.ss']['CHECKBOXTITLE'] = 'Dodaj pole wielokrotnego wyboru';
$lang['pl_PL']['FieldEditor.ss']['DATE'] = 'Data';
$lang['pl_PL']['FieldEditor.ss']['DATETITLE'] = 'Dodaj nagłówek daty';
$lang['pl_PL']['FieldEditor.ss']['DROPDOWN'] = 'Rozwijane pole';
$lang['pl_PL']['FieldEditor.ss']['DROPDOWNTITLE'] = 'Dodaj rozwijane pole';
$lang['pl_PL']['FieldEditor.ss']['EMAIL'] = 'E-mail';
$lang['pl_PL']['FieldEditor.ss']['EMAILTITLE'] = 'Dodaj pole e-mail';
$lang['pl_PL']['FieldEditor.ss']['FILE'] = 'Plik';
$lang['pl_PL']['FieldEditor.ss']['FILETITLE'] = 'Dodaj pole typu: plik';
$lang['pl_PL']['FieldEditor.ss']['FORMHEADING'] = 'Nagłówek';
$lang['pl_PL']['FieldEditor.ss']['FORMHEADINGTITLE'] = 'Dodaj nagłówek formularza';
$lang['pl_PL']['FieldEditor.ss']['MEMBER'] = 'Lista Członków';
$lang['pl_PL']['FieldEditor.ss']['MEMBERTITLE'] = 'Dodaj pole listy członków';
$lang['pl_PL']['FieldEditor.ss']['RADIOSET'] = 'Pole jednokrotnego wyboru';
$lang['pl_PL']['FieldEditor.ss']['RADIOSETTITLE'] = 'Dodaj pola jednokrotnego wyboru';
$lang['pl_PL']['FieldEditor.ss']['TEXT'] = 'Tekst';
$lang['pl_PL']['FieldEditor.ss']['TEXTTITLE'] = 'Dodaj pole tekstowe';
$lang['pl_PL']['SubmittedFormEmail.ss']['SUBMITTED'] = 'Następujące dane zostały przesłane do Twojej strony:';
$lang['pl_PL']['SubmittedFormReportField.ss']['SUBMITTED'] = 'Wysłano ';
$lang['pl_PL']['UserDefinedForm']['FORM'] = 'Formularz';
$lang['pl_PL']['UserDefinedForm']['NORESULTS'] = 'Nie znaleziono pasujących wyników';
$lang['pl_PL']['UserDefinedForm']['ONCOMPLETE'] = 'Po uzupełnieniu';
$lang['pl_PL']['UserDefinedForm']['ONCOMPLETELABEL'] = 'Pokaż po zakończeniu';
$lang['pl_PL']['UserDefinedForm']['RECEIVED'] = 'Otrzymane Zgłoszenia';
$lang['pl_PL']['UserDefinedForm']['SUBMISSIONS'] = 'Zgłoszenia';
$lang['pl_PL']['UserDefinedForm']['SUBMIT'] = 'Wyślij';
$lang['pl_PL']['UserDefinedForm']['TEXTONSUBMIT'] = 'Napis na przycisku \'submit\':';
$lang['pl_PL']['UserDefinedForm_SubmittedFormEmail']['EMAILSUBJECT'] = 'Wysyłanie formularza';
$lang['pl_PL']['EditableFormField.ss']['SHOWOPTIONS'] = 'Pokaż opcje';
$lang['pl_PL']['EditableFormField.ss']['OPTIONS'] = 'Opcje';
$lang['pl_PL']['UserDefinedForm']['EMAILRECIPIENTS'] = 'Odbiorcy Email';
$lang['pl_PL']['EditableFormField.ss']['FIELDCONFIGURATION'] = 'Konfiguracja pola';
$lang['pl_PL']['EditableFormField']['DEFAULTTOTODAY'] = 'Ustawić na dzisiaj?';
$lang['pl_PL']['EditableFormField.ss']['VALIDATION'] = 'Sprawdzanie poprawności';
$lang['pl_PL']['EditableFormField']['CUSTOMERROR'] = 'Własna wiadomość błędu';
$lang['pl_PL']['EditableFormField.ss']['CUSTOMRULES'] = 'Własne warunki';
$lang['pl_PL']['EditableFormField.ss']['ADDRULE'] = 'Dodaj warunek';
$lang['pl_PL']['EditableFormField.ss']['FIELDONDEFAULT'] = 'pole domyślnie';
$lang['pl_PL']['EditableFormField.ss']['HIDE'] = 'Ukryj';
$lang['pl_PL']['EditableOption']['Title'] = 'Tytuł';
$lang['pl_PL']['EditableOption']['Value'] = 'Wartość';
$lang['pl_PL']['CustomRule.ss']['SHOWTHISFIELD'] = 'Pokaż to pole';
$lang['pl_PL']['CustomRule.ss']['WHEN'] = 'kiedy';
$lang['pl_PL']['CustomRule.ss']['IS'] = 'jest';
$lang['pl_PL']['CustomRule.ss']['BLANK'] = 'Blank';
$lang['pl_PL']['CustomRule.ss']['NOTBLANK'] = 'Not Blank';
$lang['pl_PL']['CustomRule.ss']['GREATERTHAN'] = 'wartość większa niż';
$lang['pl_PL']['CustomRule.ss']['GREATERTHANEQUAL'] = 'wartość większa lub równa';
$lang['pl_PL']['CustomRule.ss']['LESSTHAN'] = 'wartość mniejsza niż';
$lang['pl_PL']['CustomRule.ss']['LESSTHANEQUAL'] = 'wartość mniejsza lub równa';
$lang['pl_PL']['CustomRule.ss']['NOTVALUE'] = 'nie wartością';
$lang['pl_PL']['CustomRule.ss']['VALUE'] = 'wartością';
$lang['pl_PL']['FieldEditor.ss']['SELECTAFIELD'] = 'Wybierz pole';
$lang['pl_PL']['EditableCheckbox']['PLURALNAME'] = array(
	'Pola wyboru',
	50,
	'Pural name of the object, used in dropdowns and to generally identify a collection of this object in the interface'
);
$lang['pl_PL']['EditableCheckbox']['SINGULARNAME'] = array(
	'Pole wyboru',
	50,
	'Singular name of the object, used in dropdowns and to generally identify a single object in the interface'
);
$lang['pl_PL']['EditableCheckboxGroupField']['PLURALNAME'] = array(
	'Grupa pól wyboru',
	50,
	'Pural name of the object, used in dropdowns and to generally identify a collection of this object in the interface'
);
$lang['pl_PL']['EditableCheckboxGroupField']['SINGULARNAME'] = array(
	'Grupa pola wyboru',
	50,
	'Singular name of the object, used in dropdowns and to generally identify a single object in the interface'
);
$lang['pl_PL']['EditableDateField']['PLURALNAME'] = array(
	'Pola daty',
	50,
	'Pural name of the object, used in dropdowns and to generally identify a collection of this object in the interface'
);
$lang['pl_PL']['EditableDateField']['SINGULARNAME'] = array(
	'Pole daty',
	50,
	'Singular name of the object, used in dropdowns and to generally identify a single object in the interface'
);
$lang['pl_PL']['EditableDropdown']['PLURALNAME'] = array(
	'Listy rozwijane',
	50,
	'Pural name of the object, used in dropdowns and to generally identify a collection of this object in the interface'
);
$lang['pl_PL']['EditableDropdown']['SINGULARNAME'] = array(
	'Lista rozwijana',
	50,
	'Singular name of the object, used in dropdowns and to generally identify a single object in the interface'
);
$lang['pl_PL']['EditableEmailField']['PLURALNAME'] = array(
	'Pola Email',
	50,
	'Pural name of the object, used in dropdowns and to generally identify a collection of this object in the interface'
);
$lang['pl_PL']['EditableEmailField']['SINGULARNAME'] = array(
	'Pole Email',
	50,
	'Singular name of the object, used in dropdowns and to generally identify a single object in the interface'
);
$lang['pl_PL']['EditableFileField']['PLURALNAME'] = array(
	'Pola uploadu pliku',
	50,
	'Pural name of the object, used in dropdowns and to generally identify a collection of this object in the interface'
);
$lang['pl_PL']['EditableFileField']['SINGULARNAME'] = array(
	'Pole uploadu pliku',
	50,
	'Singular name of the object, used in dropdowns and to generally identify a single object in the interface'
);
$lang['pl_PL']['EditableRadioField']['PLURALNAME'] = array(
	'Pola przełączania opcji',
	50,
	'Pural name of the object, used in dropdowns and to generally identify a collection of this object in the interface'
);
$lang['pl_PL']['EditableRadioField']['SINGULARNAME'] = array(
	'Pole przełączania opcji',
	50,
	'Singular name of the object, used in dropdowns and to generally identify a single object in the interface'
);
$lang['pl_PL']['EditableTextField']['NUMBERROWS'] = 'Number of rows';
$lang['pl_PL']['EditableTextField']['PLURALNAME'] = array(
	'Pola tekstu',
	50,
	'Pural name of the object, used in dropdowns and to generally identify a collection of this object in the interface'
);
$lang['pl_PL']['EditableTextField']['SINGULARNAME'] = array(
	'Pole tekstu',
	50,
	'Singular name of the object, used in dropdowns and to generally identify a single object in the interface'
);
?>