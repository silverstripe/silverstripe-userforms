<?php

/**
 * Croatian (Croatia) language pack
 * @package modules: userforms
 * @subpackage i18n
 */

i18n::include_locale_file('modules: userforms', 'en_US');

global $lang;

if(array_key_exists('hr_HR', $lang) && is_array($lang['hr_HR'])) {
	$lang['hr_HR'] = array_merge($lang['en_US'], $lang['hr_HR']);
} else {
	$lang['hr_HR'] = $lang['en_US'];
}

$lang['hr_HR']['EditableEmailField.ss']['DELETE'] = 'Obriši ovo polje';
$lang['hr_HR']['EditableEmailField.ss']['EMAIL'] = 'Polje za e-mail adresu';
$lang['hr_HR']['EditableEmailField.ss']['MORE'] = 'Više opcija';
$lang['hr_HR']['EditableEmailField.ss']['REQUIRED'] = 'Ovo je polje obavezno u ovoj formi i ne može biti obrisano.';
$lang['hr_HR']['EditableFileField.ss']['DELETE'] = 'Obriši ovo polje';
$lang['hr_HR']['EditableFileField.ss']['DRAG'] = 'Povuci za promjenu redoslijeda polja';
$lang['hr_HR']['EditableFileField.ss']['FILE'] = 'Polje za upload datoteka';
$lang['hr_HR']['EditableFileField.ss']['MORE'] = 'Više opcija';
$lang['hr_HR']['EditableFormField.ss']['DELETE'] = 'Obriši ovo polje';
$lang['hr_HR']['EditableFormField.ss']['DRAG'] = 'Povuci za promjenu redoslijeda polja';
$lang['hr_HR']['EditableFormField.ss']['LOCKED'] = 'Ovo polje ne može biti promijenjeno';
$lang['hr_HR']['EditableFormField.ss']['MORE'] = 'Više opcija';
$lang['hr_HR']['EditableFormField.ss']['REQUIRED'] = 'Ovo je polje obavezno u ovoj formi i ne može biti obrisano.';
$lang['hr_HR']['EditableFormFieldOption.ss']['DELETE'] = 'Odstrani ovu opciju';
$lang['hr_HR']['EditableFormFieldOption.ss']['DRAG'] = 'Povuci za promjenu redoslijeda polja';
$lang['hr_HR']['EditableFormFieldOption.ss']['LOCKED'] = 'Ova polja ne mogu biti promijenjena';
$lang['hr_HR']['EditableFormHeading.ss']['DELETE'] = 'Obriši ovo polje';
$lang['hr_HR']['EditableFormHeading.ss']['DRAG'] = 'Povuci za promjenu redoslijeda polja';
$lang['hr_HR']['EditableFormHeading.ss']['HEADING'] = 'Polje za naslov';
$lang['hr_HR']['EditableFormHeading.ss']['MORE'] = 'Više opcija';
$lang['hr_HR']['EditableRadioField.ss']['ADD'] = 'Nova opcija';
$lang['hr_HR']['EditableRadioField.ss']['DELETE'] = 'Obriši ovo polje';
$lang['hr_HR']['EditableRadioField.ss']['DRAG'] = 'Povuci za promjenu redoslijeda polja';
$lang['hr_HR']['EditableRadioField.ss']['LOCKED'] = 'Ova polja ne mogu biti promijenjena';
$lang['hr_HR']['EditableRadioField.ss']['MORE'] = 'Više opcija';
$lang['hr_HR']['EditableRadioField.ss']['REQUIRED'] = 'Ovo polje je obavezno u ovoj formi i ne može biti obrisano.';
$lang['hr_HR']['EditableRadioField.ss']['SET'] = 'Set radio kontrola';
$lang['hr_HR']['EditableRadioOption.ss']['DELETE'] = 'Odstrani ovu opciju';
$lang['hr_HR']['EditableRadioOption.ss']['DRAG'] = 'Povuci za promjenu redoslijeda opcija';
$lang['hr_HR']['EditableRadioOption.ss']['LOCKED'] = 'Ova polja ne mogu biti promijenjena';
$lang['hr_HR']['EditableTextField.ss']['DELETE'] = 'Obriši ovo polje';
$lang['hr_HR']['EditableTextField.ss']['DRAG'] = 'Povuci za promjenu redoslijeda polja';
$lang['hr_HR']['EditableTextField.ss']['MORE'] = 'Više opcija';
$lang['hr_HR']['EditableTextField.ss']['TEXTFIELD'] = 'Polje za unos teksta';
$lang['hr_HR']['FieldEditor.ss']['ADD'] = 'Dodaj';
$lang['hr_HR']['FieldEditor.ss']['CHECKBOX'] = 'Checkbox';
$lang['hr_HR']['FieldEditor.ss']['CHECKBOXTITLE'] = 'Dodaj checkbox';
$lang['hr_HR']['FieldEditor.ss']['DATE'] = 'Datum';
$lang['hr_HR']['FieldEditor.ss']['DATETITLE'] = 'Dodaj naslov za datum';
$lang['hr_HR']['FieldEditor.ss']['DROPDOWN'] = 'Padjući izbornik';
$lang['hr_HR']['FieldEditor.ss']['DROPDOWNTITLE'] = 'Dodaj opciju';
$lang['hr_HR']['FieldEditor.ss']['EMAIL'] = 'Email';
$lang['hr_HR']['FieldEditor.ss']['EMAILTITLE'] = 'Dodaj polje za unos e-maila';
$lang['hr_HR']['FieldEditor.ss']['FILE'] = 'Datoteka';
$lang['hr_HR']['FieldEditor.ss']['FILETITLE'] = 'Dodaj polje za upload datoteka';
$lang['hr_HR']['FieldEditor.ss']['FORMHEADING'] = 'Naslov';
$lang['hr_HR']['FieldEditor.ss']['FORMHEADINGTITLE'] = 'Dodaj naslov forme';
$lang['hr_HR']['FieldEditor.ss']['MEMBER'] = 'Lista članova';
$lang['hr_HR']['FieldEditor.ss']['RADIOSET'] = 'Radio kontrole';
$lang['hr_HR']['FieldEditor.ss']['RADIOSETTITLE'] = 'Dodaj set radio kontrola';
$lang['hr_HR']['FieldEditor.ss']['TEXT'] = 'Tekst';
$lang['hr_HR']['FieldEditor.ss']['TEXTTITLE'] = 'Dodaj polje za unos teksta';
$lang['hr_HR']['SubmittedFormEmail.ss']['SUBMITTED'] = 'Slijedeći podaci pridodani su Vašoj stranici:';

?>