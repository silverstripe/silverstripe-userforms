<?php

/**
 * Dutch (Netherlands) language pack
 * @package modules: userforms
 * @subpackage i18n
 */

i18n::include_locale_file('modules: userforms', 'en_US');

global $lang;

if(array_key_exists('nl_NL', $lang) && is_array($lang['nl_NL'])) {
	$lang['nl_NL'] = array_merge($lang['en_US'], $lang['nl_NL']);
} else {
	$lang['nl_NL'] = $lang['en_US'];
}

$lang['nl_NL']['EditableCheckbox']['ANY'] = 'eender';
$lang['nl_NL']['EditableCheckbox']['NOTSELECTED'] = 'Niet geselecteerd';
$lang['nl_NL']['EditableCheckbox']['SELECTED'] = 'Geselecteerd';
$lang['nl_NL']['EditableCheckbox.ss']['CHECKBOX'] = 'Checkbox veld';
$lang['nl_NL']['EditableCheckbox.ss']['DELETE'] = 'Verwijder dit veld';
$lang['nl_NL']['EditableCheckbox.ss']['DRAG'] = 'Versleep om de rangschikking van de velden te wijzigen';
$lang['nl_NL']['EditableCheckbox.ss']['LOCKED'] = 'Dit veld kan niet gewijzigd worden';
$lang['nl_NL']['EditableCheckbox.ss']['MORE'] = 'Meer opties';
$lang['nl_NL']['EditableCheckboxGroupField.ss']['ADD'] = 'Voeg een nieuwe optie toe';
$lang['nl_NL']['EditableCheckboxGroupField.ss']['CHECKBOXGROUP'] = 'Checkbox groep';
$lang['nl_NL']['EditableCheckboxGroupField.ss']['DELETE'] = 'Verwijder dit veld';
$lang['nl_NL']['EditableCheckboxGroupField.ss']['DRAG'] = 'Versleep om de rangschikking van de velden te wijzigen';
$lang['nl_NL']['EditableCheckboxGroupField.ss']['LOCKED'] = 'Deze velden kunnen niet gewijzigd worden';
$lang['nl_NL']['EditableCheckboxGroupField.ss']['MORE'] = 'Meer opties';
$lang['nl_NL']['EditableCheckboxGroupField.ss']['REQUIRED'] = 'Dit veld is verplicht en kan niet verwijderd worden';
$lang['nl_NL']['EditableCheckboxOption.ss']['DELETE'] = 'Verwijder deze optie';
$lang['nl_NL']['EditableCheckboxOption.ss']['DRAG'] = 'Versleep om de rangschikking van de opties te wijzigen';
$lang['nl_NL']['EditableCheckboxOption.ss']['LOCKED'] = 'Deze velden kunnen niet gewijzigd worden';
$lang['nl_NL']['EditableDateField.ss']['DATE'] = 'Datum veld';
$lang['nl_NL']['EditableDateField.ss']['DELETE'] = 'Verwijder dit veld';
$lang['nl_NL']['EditableDateField.ss']['DRAG'] = 'Versleep om de rangschikking van de velden te wijzigen';
$lang['nl_NL']['EditableDateField.ss']['MORE'] = 'Meer opties';
$lang['nl_NL']['EditableDropdown.ss']['ADD'] = 'Voeg een nieuwe optie toe';
$lang['nl_NL']['EditableDropdown.ss']['DELETE'] = 'Verwijder dit veld';
$lang['nl_NL']['EditableDropdown.ss']['DRAG'] = 'Versleep om de rangschikking van de velden te wijzigen';
$lang['nl_NL']['EditableDropdown.ss']['DROPDOWN'] = 'Dropdown lijst';
$lang['nl_NL']['EditableDropdown.ss']['LOCKED'] = 'Deze velden kunnen niet gewijzigd worden';
$lang['nl_NL']['EditableDropdown.ss']['MORE'] = 'Meer opties';
$lang['nl_NL']['EditableDropdown.ss']['REQUIRED'] = 'Dit veld is verplicht en kan niet verwijderd worden';
$lang['nl_NL']['EditableDropdownOption.ss']['DELETE'] = 'Verwijder deze optie';
$lang['nl_NL']['EditableDropdownOption.ss']['DRAG'] = 'Versleep om de rangschikking van de opties te wijzigen';
$lang['nl_NL']['EditableDropdownOption.ss']['LOCKED'] = 'Deze velden kunnen niet gewijzigd worden';
$lang['nl_NL']['EditableEmailField']['SENDCOPY'] = 'Stuur een kopie naar dit adres';
$lang['nl_NL']['EditableEmailField.ss']['DELETE'] = 'Verwijder dit veld';
$lang['nl_NL']['EditableEmailField.ss']['DRAG'] = 'Versleep om de rangschikking van de velden te wijzigen';
$lang['nl_NL']['EditableEmailField.ss']['EMAIL'] = 'Email adres veld';
$lang['nl_NL']['EditableEmailField.ss']['MORE'] = 'Meer opties';
$lang['nl_NL']['EditableEmailField.ss']['REQUIRED'] = 'Dit veld is verplicht en kan niet verwijderd worden';
$lang['nl_NL']['EditableFileField.ss']['DELETE'] = 'Verwijder dit veld';
$lang['nl_NL']['EditableFileField.ss']['DRAG'] = 'Versleep om de rangschikking van de velden te wijzigen';
$lang['nl_NL']['EditableFileField.ss']['FILE'] = 'Upload veld voor bestanden';
$lang['nl_NL']['EditableFileField.ss']['MORE'] = 'Meer opties';
$lang['nl_NL']['EditableFormField']['ENTERQUESTION'] = 'Vraag ingeven';
$lang['nl_NL']['EditableFormField']['REQUIRED'] = 'Verplicht?';
$lang['nl_NL']['EditableFormField.ss']['DELETE'] = 'Verwijder dit veld';
$lang['nl_NL']['EditableFormField.ss']['DRAG'] = 'Versleep om de rangschikking van de velden te wijzigen';
$lang['nl_NL']['EditableFormField.ss']['LOCKED'] = 'Deze velden kunnen niet gewijzigd worden';
$lang['nl_NL']['EditableFormField.ss']['MORE'] = 'Meer opties';
$lang['nl_NL']['EditableFormField.ss']['REQUIRED'] = 'Dit veld is verplicht en kan niet gewijzigd worden';
$lang['nl_NL']['EditableFormFieldOption.ss']['DELETE'] = 'Verwijder deze optie';
$lang['nl_NL']['EditableFormFieldOption.ss']['DRAG'] = 'Versleep om de rangschikking van de velden te wijzigen';
$lang['nl_NL']['EditableFormFieldOption.ss']['LOCKED'] = 'Deze velden kunnen niet gewijzigd worden';
$lang['nl_NL']['EditableFormHeading.ss']['DELETE'] = 'Verwijder dit veld';
$lang['nl_NL']['EditableFormHeading.ss']['DRAG'] = 'Versleep om de rangschikking van de velden te wijzigen';
$lang['nl_NL']['EditableFormHeading.ss']['HEADING'] = 'Hoofding';
$lang['nl_NL']['EditableFormHeading.ss']['MORE'] = 'Meer opties';
$lang['nl_NL']['EditableRadioField.ss']['ADD'] = 'Voed nieuwe optie toe';
$lang['nl_NL']['EditableRadioField.ss']['DELETE'] = 'Verwijder dit veld';
$lang['nl_NL']['EditableRadioField.ss']['DRAG'] = 'Versleep om de rangschikking van de velden te wijzigen';
$lang['nl_NL']['EditableRadioField.ss']['LOCKED'] = 'Deze velden kunnen niet gewijzigd worden';
$lang['nl_NL']['EditableRadioField.ss']['MORE'] = 'Meer opties';
$lang['nl_NL']['EditableRadioField.ss']['REQUIRED'] = 'Dit veld is verplicht en kan niet verwijderd worden';
$lang['nl_NL']['EditableRadioField.ss']['SET'] = 'Radio knoppen set';
$lang['nl_NL']['EditableRadioOption.ss']['DELETE'] = 'Verwijder deze optie';
$lang['nl_NL']['EditableRadioOption.ss']['DRAG'] = 'Versleep om de rangschikking van de velden te wijzigen';
$lang['nl_NL']['EditableRadioOption.ss']['LOCKED'] = 'Deze velden kunnen niet gewijzigd worden';
$lang['nl_NL']['EditableTextField']['DEFAULTTEXT'] = 'Standaard tekst';
$lang['nl_NL']['EditableTextField']['NUMBERROWS'] = 'Aantal rijen';
$lang['nl_NL']['EditableTextField.ss']['DELETE'] = 'Verwijder dit veld';
$lang['nl_NL']['EditableTextField.ss']['DRAG'] = 'Versleep om de rangschikking van de velden te wijzigen';
$lang['nl_NL']['EditableTextField.ss']['MORE'] = 'Meer opties';
$lang['nl_NL']['EditableTextField.ss']['TEXTFIELD'] = 'Tekst Veld';
$lang['nl_NL']['EditableTextField']['TEXTBOXLENGTH'] = 'Lengte van tekstveld';
$lang['nl_NL']['EditableTextField']['TEXTLENGTH'] = 'Tekst lengte';
$lang['nl_NL']['FieldEditor']['EMAILONSUBMIT'] = 'E-mail formulier na bevestiging';
$lang['nl_NL']['FieldEditor']['EMAILSUBMISSION'] = 'E-mail inzending naar:';
$lang['nl_NL']['FieldEditor.ss']['ADD'] = 'Voeg toe';
$lang['nl_NL']['FieldEditor.ss']['CHECKBOX'] = 'Checkbox';
$lang['nl_NL']['FieldEditor.ss']['CHECKBOXGROUP'] = 'Selectievakjes';
$lang['nl_NL']['FieldEditor.ss']['CHECKBOXGROUPTITLE'] = 'Selectievak toevoegen aan groep veld';
$lang['nl_NL']['FieldEditor.ss']['CHECKBOXTITLE'] = 'Voeg een checkbox toe';
$lang['nl_NL']['FieldEditor.ss']['DATE'] = 'Datum';
$lang['nl_NL']['FieldEditor.ss']['DATETITLE'] = 'Voeg toe datum kop';
$lang['nl_NL']['FieldEditor.ss']['DROPDOWN'] = 'Dropdown';
$lang['nl_NL']['FieldEditor.ss']['DROPDOWNTITLE'] = 'voeg toe dropdown';
$lang['nl_NL']['FieldEditor.ss']['EMAIL'] = 'Email';
$lang['nl_NL']['FieldEditor.ss']['EMAILTITLE'] = 'Voeg een email veld toe';
$lang['nl_NL']['FieldEditor.ss']['FILE'] = 'Bestand';
$lang['nl_NL']['FieldEditor.ss']['FILETITLE'] = 'Voeg bestand upload veld toe';
$lang['nl_NL']['FieldEditor.ss']['FORMHEADING'] = 'Kop';
$lang['nl_NL']['FieldEditor.ss']['FORMHEADINGTITLE'] = 'Voeg toe formulier kop';
$lang['nl_NL']['FieldEditor.ss']['MEMBER'] = 'Leden lijst';
$lang['nl_NL']['FieldEditor.ss']['MEMBERTITLE'] = 'Toevoegen van ledenlijst veld';
$lang['nl_NL']['FieldEditor.ss']['RADIOSET'] = 'Radio';
$lang['nl_NL']['FieldEditor.ss']['RADIOSETTITLE'] = 'Voeg toe radio button set';
$lang['nl_NL']['FieldEditor.ss']['TEXT'] = 'Tekst';
$lang['nl_NL']['FieldEditor.ss']['TEXTTITLE'] = 'Voeg een tekst veld toe';
$lang['nl_NL']['SubmittedFormEmail.ss']['SUBMITTED'] = 'Onderstaande data is ingestuurd op de website:';
$lang['nl_NL']['SubmittedFormReportField.ss']['SUBMITTED'] = 'Verstuurd op';
$lang['nl_NL']['UserDefinedForm']['FORM'] = 'Formulier';
$lang['nl_NL']['UserDefinedForm']['NORESULTS'] = 'Geen overeenkomstige resultaten gevonden';
$lang['nl_NL']['UserDefinedForm']['ONCOMPLETE'] = 'Als ingevuld';
$lang['nl_NL']['UserDefinedForm']['ONCOMPLETELABEL'] = 'Toon na invullen';
$lang['nl_NL']['UserDefinedForm']['RECEIVED'] = 'Ontvangen Verzendingen';
$lang['nl_NL']['UserDefinedForm']['SUBMISSIONS'] = 'Verzendingen';
$lang['nl_NL']['UserDefinedForm']['SUBMIT'] = 'Verzenden';
$lang['nl_NL']['UserDefinedForm']['TEXTONSUBMIT'] = 'Tekst op de verzend-knop:';
$lang['nl_NL']['UserDefinedForm_SubmittedFormEmail']['EMAILSUBJECT'] = 'Verwerken van formulier';

?>