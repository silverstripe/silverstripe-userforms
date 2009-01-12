<?php

/**
 * Danish (Denmark) language pack
 * @package modules: userforms
 * @subpackage i18n
 */

i18n::include_locale_file('modules: userforms', 'en_US');

global $lang;

if(array_key_exists('da_DK', $lang) && is_array($lang['da_DK'])) {
	$lang['da_DK'] = array_merge($lang['en_US'], $lang['da_DK']);
} else {
	$lang['da_DK'] = $lang['en_US'];
}

$lang['da_DK']['EditableCheckbox']['ANY'] = 'alle';
$lang['da_DK']['EditableCheckbox']['NOTSELECTED'] = 'Ikke valgt';
$lang['da_DK']['EditableCheckbox']['SELECTED'] = 'Valgt';
$lang['da_DK']['EditableCheckbox.ss']['CHECKBOX'] = 'Checkbox felt';
$lang['da_DK']['EditableCheckbox.ss']['DELETE'] = 'Slet dette felt';
$lang['da_DK']['EditableCheckbox.ss']['DRAG'] = 'Træk for at ændre felternes rækkefølge';
$lang['da_DK']['EditableCheckbox.ss']['LOCKED'] = 'Dette felt kan ikke modificeres';
$lang['da_DK']['EditableCheckbox.ss']['MORE'] = 'Flere valgmuligheder';
$lang['da_DK']['EditableCheckboxGroupField.ss']['ADD'] = 'Tilføj mulighed';
$lang['da_DK']['EditableCheckboxGroupField.ss']['CHECKBOXGROUP'] = 'Checkbox gruppe';
$lang['da_DK']['EditableCheckboxGroupField.ss']['DELETE'] = 'Slet dette felt';
$lang['da_DK']['EditableCheckboxGroupField.ss']['DRAG'] = 'Træk for at ændre felternes rækkefølge';
$lang['da_DK']['EditableCheckboxGroupField.ss']['LOCKED'] = 'Disse felter kan ikke modificeres';
$lang['da_DK']['EditableCheckboxGroupField.ss']['MORE'] = 'Flere valgmuligheder';
$lang['da_DK']['EditableCheckboxGroupField.ss']['REQUIRED'] = 'Dette felt er påkrævet i denne formular og kan ikke slettes';
$lang['da_DK']['EditableCheckboxOption.ss']['DELETE'] = 'Slet denne mulighed';
$lang['da_DK']['EditableCheckboxOption.ss']['DRAG'] = 'Træk for at ændre mulighedernes rækkefølge';
$lang['da_DK']['EditableCheckboxOption.ss']['LOCKED'] = 'Disse felter kan ikke modificeres';
$lang['da_DK']['EditableDateField.ss']['DRAG'] = 'Træk for at ændre felternes rækkefølge';
$lang['da_DK']['EditableEmailField']['SENDCOPY'] = 'Send kopi af det indsendte til denne adresse';
$lang['da_DK']['EditableEmailField.ss']['DELETE'] = 'Slet dette felt';
$lang['da_DK']['EditableEmailField.ss']['MORE'] = 'Flere indstillinger';
$lang['da_DK']['EditableEmailField.ss']['REQUIRED'] = 'Dette felt er påkrævet i denne formular og kan derfor ikke slettet';
$lang['da_DK']['EditableFileField.ss']['DELETE'] = 'Slet dette felt';
$lang['da_DK']['EditableFileField.ss']['DRAG'] = 'Træk for at ændre felternes rækkefølge';
$lang['da_DK']['EditableFileField.ss']['FILE'] = 'Fil-upload felt';
$lang['da_DK']['EditableFileField.ss']['MORE'] = 'Flere indstillinger';
$lang['da_DK']['EditableFormField']['ENTERQUESTION'] = 'Indtast spørgsmål';
$lang['da_DK']['EditableFormField']['REQUIRED'] = 'Påkrævet?';
$lang['da_DK']['EditableFormField.ss']['DELETE'] = 'Slet dette felt';
$lang['da_DK']['EditableFormField.ss']['DRAG'] = 'Træk for at ændre felternes rækkefølge';
$lang['da_DK']['EditableFormField.ss']['LOCKED'] = 'Dette felt kan ikke ændres';
$lang['da_DK']['EditableFormField.ss']['MORE'] = 'Flere indstillinger';
$lang['da_DK']['EditableFormField.ss']['REQUIRED'] = 'Dette felt er påkrævet i denne formular og kan derfor ikke slettet';
$lang['da_DK']['EditableFormFieldOption.ss']['DELETE'] = 'Slet denne valgmulighed';
$lang['da_DK']['EditableFormFieldOption.ss']['DRAG'] = 'Træk for at ændre felternes rækkefølge';
$lang['da_DK']['EditableFormFieldOption.ss']['LOCKED'] = 'Dette felt kan ikke ændres';
$lang['da_DK']['EditableFormHeading.ss']['DELETE'] = 'Slet dette felt';
$lang['da_DK']['EditableFormHeading.ss']['DRAG'] = 'Træk for at ændre felternes rækkefølge';
$lang['da_DK']['EditableFormHeading.ss']['HEADING'] = 'Overskriftsfelt';
$lang['da_DK']['EditableFormHeading.ss']['MORE'] = 'Flere indstillinger';
$lang['da_DK']['EditableRadioField.ss']['ADD'] = 'Tilføj ny valgmulighed';
$lang['da_DK']['EditableRadioField.ss']['DELETE'] = 'Slet dette felt';
$lang['da_DK']['EditableRadioField.ss']['DRAG'] = 'Træk for at ændre felternes rækkefølge';
$lang['da_DK']['EditableRadioField.ss']['LOCKED'] = 'Disse felter kan ikke ændres';
$lang['da_DK']['EditableRadioField.ss']['MORE'] = 'Flere indstillinger';
$lang['da_DK']['EditableRadioField.ss']['REQUIRED'] = 'Dette felt er påkrævet i denne formular og kan derfor ikke slettet';
$lang['da_DK']['EditableRadioField.ss']['SET'] = 'Sæt radioknap';
$lang['da_DK']['EditableRadioOption.ss']['DELETE'] = 'Slet denne valgmulighed';
$lang['da_DK']['EditableRadioOption.ss']['DRAG'] = 'Træk for at ændre mulighedernes rækkefølge';
$lang['da_DK']['EditableRadioOption.ss']['LOCKED'] = 'Disse felter kan ikke ændres';
$lang['da_DK']['EditableTextField']['DEFAULTTEXT'] = 'Standard tekst';
$lang['da_DK']['EditableTextField']['NUMBERROWS'] = 'Antal rækker';
$lang['da_DK']['EditableTextField.ss']['DELETE'] = 'Slet dette felt';
$lang['da_DK']['EditableTextField.ss']['DRAG'] = 'Træk for at ændre felternes rækkefølge';
$lang['da_DK']['EditableTextField.ss']['MORE'] = 'Flere indstillinger';
$lang['da_DK']['EditableTextField.ss']['TEXTFIELD'] = 'Tekstfelt';
$lang['da_DK']['EditableTextField']['TEXTBOXLENGTH'] = 'Længde af tekstboks';
$lang['da_DK']['EditableTextField']['TEXTLENGTH'] = 'Tekstlængde';
$lang['da_DK']['FieldEditor']['EMAILONSUBMIT'] = 'Send formular som e-mail efter indsendelse:';
$lang['da_DK']['FieldEditor']['EMAILSUBMISSION'] = 'Send indsendelse via email til:';
$lang['da_DK']['FieldEditor.ss']['ADD'] = 'Tilføj';
$lang['da_DK']['FieldEditor.ss']['CHECKBOX'] = 'Checkbox';
$lang['da_DK']['FieldEditor.ss']['CHECKBOXGROUP'] = 'Checkboxes';
$lang['da_DK']['FieldEditor.ss']['CHECKBOXTITLE'] = 'Tilføj checkbox';
$lang['da_DK']['FieldEditor.ss']['DATE'] = 'Dato';
$lang['da_DK']['FieldEditor.ss']['DATETITLE'] = 'Tilføj datooverskrift';
$lang['da_DK']['FieldEditor.ss']['DROPDOWN'] = 'Dropdown';
$lang['da_DK']['FieldEditor.ss']['DROPDOWNTITLE'] = 'Tilføj dropdown';
$lang['da_DK']['FieldEditor.ss']['EMAIL'] = 'Email';
$lang['da_DK']['FieldEditor.ss']['EMAILTITLE'] = 'Tilføj emailfelt';
$lang['da_DK']['FieldEditor.ss']['FILE'] = 'Fil';
$lang['da_DK']['FieldEditor.ss']['FILETITLE'] = 'Tilføj felt til upload af fil';
$lang['da_DK']['FieldEditor.ss']['FORMHEADING'] = 'Overskrift';
$lang['da_DK']['FieldEditor.ss']['FORMHEADINGTITLE'] = 'Tilføj formularoverskrift';
$lang['da_DK']['FieldEditor.ss']['RADIOSET'] = 'Radio';
$lang['da_DK']['FieldEditor.ss']['RADIOSETTITLE'] = 'Tilføj sæt af radiio knapper';
$lang['da_DK']['FieldEditor.ss']['TEXT'] = 'Tekst';
$lang['da_DK']['FieldEditor.ss']['TEXTTITLE'] = 'Tilføj tekstfelt';
$lang['da_DK']['SubmittedFormEmail.ss']['SUBMITTED'] = 'Følgende data er indsendt til din hjemmeside:';
$lang['da_DK']['SubmittedFormReportField.ss']['SUBMITTED'] = 'Indsendt af';
$lang['da_DK']['UserDefinedForm']['FORM'] = 'Formular';
$lang['da_DK']['UserDefinedForm']['NORESULTS'] = 'Ingen resultater fundet';
$lang['da_DK']['UserDefinedForm']['ONCOMPLETE'] = 'Ved gennemført';
$lang['da_DK']['UserDefinedForm']['ONCOMPLETELABEL'] = 'Vis ved gennemført';
$lang['da_DK']['UserDefinedForm']['RECEIVED'] = 'Indsendelse modtaget';
$lang['da_DK']['UserDefinedForm']['SUBMISSIONS'] = 'Indsendelse';
$lang['da_DK']['UserDefinedForm']['SUBMIT'] = 'Send';
$lang['da_DK']['UserDefinedForm']['TEXTONSUBMIT'] = 'Tekst på send-knap';
$lang['da_DK']['UserDefinedForm_SubmittedFormEmail']['EMAILSUBJECT'] = 'Indsendelse af formular';

?>