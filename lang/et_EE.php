<?php

/**
 * Estonian (Estonia) language pack
 * @package modules: userforms
 * @subpackage i18n
 */

i18n::include_locale_file('modules: userforms', 'en_US');

global $lang;

if(array_key_exists('et_EE', $lang) && is_array($lang['et_EE'])) {
	$lang['et_EE'] = array_merge($lang['en_US'], $lang['et_EE']);
} else {
	$lang['et_EE'] = $lang['en_US'];
}

$lang['et_EE']['EditableCheckbox']['ANY'] = 'Iga';
$lang['et_EE']['EditableCheckbox']['NOTSELECTED'] = 'Pole valitud';
$lang['et_EE']['EditableCheckbox']['SELECTED'] = 'Valitud';
$lang['et_EE']['EditableCheckbox.ss']['CHECKBOX'] = 'Märkeruudu väli';
$lang['et_EE']['EditableCheckbox.ss']['DELETE'] = 'Kustuta see väli';
$lang['et_EE']['EditableCheckbox.ss']['DRAG'] = 'Väljade ümber paigutamiseks lohistage neid';
$lang['et_EE']['EditableCheckbox.ss']['LOCKED'] = 'Seda välja ei saa muuta';
$lang['et_EE']['EditableCheckbox.ss']['MORE'] = 'Rohkem valikuid';
$lang['et_EE']['EditableCheckboxGroupField.ss']['ADD'] = 'Lisa uus valik';
$lang['et_EE']['EditableCheckboxGroupField.ss']['CHECKBOXGROUP'] = 'Märkeruudu rühm';
$lang['et_EE']['EditableCheckboxGroupField.ss']['DELETE'] = 'Kustuta see väli';
$lang['et_EE']['EditableCheckboxGroupField.ss']['DRAG'] = 'Väljade ümber paigutamiseks lohistage neid';
$lang['et_EE']['EditableCheckboxGroupField.ss']['LOCKED'] = 'Neid välju ei saa muuta';
$lang['et_EE']['EditableCheckboxGroupField.ss']['MORE'] = 'Rohkem valikuid';
$lang['et_EE']['EditableCheckboxGroupField.ss']['REQUIRED'] = 'See väli on selle vormi jaoks nõutav ja seda ei saa kustutada';
$lang['et_EE']['EditableCheckboxOption.ss']['DELETE'] = 'Eemalda see valik';
$lang['et_EE']['EditableCheckboxOption.ss']['DRAG'] = 'Valikute ümber paigutamiseks lohistage neid';
$lang['et_EE']['EditableCheckboxOption.ss']['LOCKED'] = 'Neid välju ei saa muuta';
$lang['et_EE']['EditableDateField.ss']['DATE'] = 'Kuupäeva väli';
$lang['et_EE']['EditableDateField.ss']['DELETE'] = 'Kustuta see väli';
$lang['et_EE']['EditableDateField.ss']['DRAG'] = 'Väljade ümber paigutamiseks lohistage neid';
$lang['et_EE']['EditableDateField.ss']['MORE'] = 'Rohkem valikuid';
$lang['et_EE']['EditableDropdown.ss']['ADD'] = 'Lisa uus valik';
$lang['et_EE']['EditableDropdown.ss']['DELETE'] = 'Kustuta see väli';
$lang['et_EE']['EditableDropdown.ss']['DRAG'] = 'Väljade ümber paigutamiseks lohistage neid';
$lang['et_EE']['EditableDropdown.ss']['DROPDOWN'] = 'Dropdown kast';
$lang['et_EE']['EditableDropdown.ss']['LOCKED'] = 'Neid välju ei saa muuta';
$lang['et_EE']['EditableDropdown.ss']['MORE'] = 'Rohkem valikuid';
$lang['et_EE']['EditableDropdown.ss']['REQUIRED'] = 'See väli on vormis vajalik ja seda ei saa kustutada';
$lang['et_EE']['EditableDropdownOption.ss']['DELETE'] = 'Eemalda see valik';
$lang['et_EE']['EditableDropdownOption.ss']['DRAG'] = 'Valikute ümber paigutamiseks lohistage neid';
$lang['et_EE']['EditableDropdownOption.ss']['LOCKED'] = 'Neid välju ei saa muuta';
$lang['et_EE']['EditableEmailField']['SENDCOPY'] = 'Saada kaaskirja koopia sellele aadressile';
$lang['et_EE']['EditableEmailField.ss']['DELETE'] = 'Kustuta see väli';
$lang['et_EE']['EditableEmailField.ss']['DRAG'] = 'Väljade ümber paigutamiseks lohistage neid';
$lang['et_EE']['EditableEmailField.ss']['EMAIL'] = 'E-maili aadressi väli';
$lang['et_EE']['EditableEmailField.ss']['MORE'] = 'Rohkem valikuid';
$lang['et_EE']['EditableEmailField.ss']['REQUIRED'] = 'See väli on vormis vajalik ja seda ei saa kustutada';
$lang['et_EE']['EditableFileField.ss']['DELETE'] = 'Kustuta see väli';
$lang['et_EE']['EditableFileField.ss']['DRAG'] = 'Väljade ümber paigutamiseks lohistage neid';
$lang['et_EE']['EditableFileField.ss']['FILE'] = 'Faili üleslaadimise väli';
$lang['et_EE']['EditableFileField.ss']['MORE'] = 'Rohkem valikuid';
$lang['et_EE']['EditableFormField']['ENTERQUESTION'] = 'Sisesta küsimus';
$lang['et_EE']['EditableFormField']['REQUIRED'] = 'Nõutav?';
$lang['et_EE']['EditableFormField.ss']['DELETE'] = 'Kustuta see väli';
$lang['et_EE']['EditableFormField.ss']['DRAG'] = 'Väljade ümber paigutamiseks lohistage neid';
$lang['et_EE']['EditableFormField.ss']['LOCKED'] = 'Neid väli ei saa muuta';
$lang['et_EE']['EditableFormField.ss']['MORE'] = 'Rohkem valikuid';
$lang['et_EE']['EditableFormField.ss']['REQUIRED'] = 'See väli on vormis vajalik ja seda ei saa kustutada';
$lang['et_EE']['EditableFormFieldOption.ss']['DELETE'] = 'Eemdala see valik';
$lang['et_EE']['EditableFormFieldOption.ss']['DRAG'] = 'Väljade ümber paigutamiseks lohistage neid';
$lang['et_EE']['EditableFormFieldOption.ss']['LOCKED'] = 'Seda välja ei saa muuta';
$lang['et_EE']['EditableFormHeading.ss']['DELETE'] = 'Kustuta see väli';
$lang['et_EE']['EditableFormHeading.ss']['DRAG'] = 'Väljade ümber paigutamiseks lohistage neid';
$lang['et_EE']['EditableFormHeading.ss']['HEADING'] = 'Päise väli';
$lang['et_EE']['EditableFormHeading.ss']['MORE'] = 'Rohkem valikuid';
$lang['et_EE']['EditableRadioField.ss']['ADD'] = 'Lisa uus valik';
$lang['et_EE']['EditableRadioField.ss']['DELETE'] = 'Kustuta see väli';
$lang['et_EE']['EditableRadioField.ss']['DRAG'] = 'Väljade ümber paigutamiseks lohistage neid';
$lang['et_EE']['EditableRadioField.ss']['LOCKED'] = 'Neid välju ei saa muuta';
$lang['et_EE']['EditableRadioField.ss']['MORE'] = 'Rohkem valikuid';
$lang['et_EE']['EditableRadioField.ss']['REQUIRED'] = 'See väli on vormis vajalik ja seda ei saa kustutada';
$lang['et_EE']['EditableRadioField.ss']['SET'] = 'Raadionupud';
$lang['et_EE']['EditableRadioOption.ss']['DELETE'] = 'Eemalda see valik';
$lang['et_EE']['EditableRadioOption.ss']['DRAG'] = 'Valikute ümber paigutamiseks lohistage neid';
$lang['et_EE']['EditableRadioOption.ss']['LOCKED'] = 'Neid välju ei saa muuta';
$lang['et_EE']['EditableTextField']['DEFAULTTEXT'] = 'Vaikimisi tekst';
$lang['et_EE']['EditableTextField']['NUMBERROWS'] = 'Arv ridu';
$lang['et_EE']['EditableTextField.ss']['DELETE'] = 'Kustuta see väli';
$lang['et_EE']['EditableTextField.ss']['DRAG'] = 'Väljade ümber paigutamiseks lohistage neid';
$lang['et_EE']['EditableTextField.ss']['MORE'] = 'Rohkem valikuid';
$lang['et_EE']['EditableTextField.ss']['TEXTFIELD'] = 'Teksti väli';
$lang['et_EE']['EditableTextField']['TEXTBOXLENGTH'] = 'Teksti kasti pikkus';
$lang['et_EE']['EditableTextField']['TEXTLENGTH'] = 'Tektsi pikkus';
$lang['et_EE']['FieldEditor']['EMAILONSUBMIT'] = 'E-posti vorm saatmisel:';
$lang['et_EE']['FieldEditor']['EMAILSUBMISSION'] = 'E-maili kaaskiri: ';
$lang['et_EE']['FieldEditor.ss']['ADD'] = 'Lisa';
$lang['et_EE']['FieldEditor.ss']['CHECKBOX'] = 'Märkeruut';
$lang['et_EE']['FieldEditor.ss']['CHECKBOXGROUP'] = 'Märkeruudud';
$lang['et_EE']['FieldEditor.ss']['CHECKBOXGROUPTITLE'] = 'Lisa märkeruudu rühma väli';
$lang['et_EE']['FieldEditor.ss']['CHECKBOXTITLE'] = 'Lisa märkeruut';
$lang['et_EE']['FieldEditor.ss']['DATE'] = 'Kuupäev';
$lang['et_EE']['FieldEditor.ss']['DATETITLE'] = 'Lisa kuupäeva päis';
$lang['et_EE']['FieldEditor.ss']['DROPDOWN'] = 'Dropdown';
$lang['et_EE']['FieldEditor.ss']['DROPDOWNTITLE'] = 'Lisa dropdown';
$lang['et_EE']['FieldEditor.ss']['EMAIL'] = 'E-mail';
$lang['et_EE']['FieldEditor.ss']['EMAILTITLE'] = 'Lisa E-maili väli';
$lang['et_EE']['FieldEditor.ss']['FILE'] = 'Fail';
$lang['et_EE']['FieldEditor.ss']['FILETITLE'] = 'Lisa faili üleslaadimise väli';
$lang['et_EE']['FieldEditor.ss']['FORMHEADING'] = 'Päis';
$lang['et_EE']['FieldEditor.ss']['FORMHEADINGTITLE'] = 'Lisa vormi päis';
$lang['et_EE']['FieldEditor.ss']['MEMBER'] = 'Liikmete loetelu';
$lang['et_EE']['FieldEditor.ss']['MEMBERTITLE'] = 'Lisa liikmete loetelu väli';
$lang['et_EE']['FieldEditor.ss']['RADIOSET'] = 'Raadio';
$lang['et_EE']['FieldEditor.ss']['RADIOSETTITLE'] = 'Lisa raadionuppe';
$lang['et_EE']['FieldEditor.ss']['TEXT'] = 'Tekst';
$lang['et_EE']['FieldEditor.ss']['TEXTTITLE'] = 'Lisa teksti väli';
$lang['et_EE']['SubmittedFormEmail.ss']['SUBMITTED'] = 'Sinu veebilehele saadeti järgnevad andmed:';
$lang['et_EE']['SubmittedFormReportField.ss']['SUBMITTED'] = 'Saadetud';
$lang['et_EE']['UserDefinedForm']['FORM'] = 'Vorm';
$lang['et_EE']['UserDefinedForm']['NORESULTS'] = 'Sobivad vasteid ei leitud';
$lang['et_EE']['UserDefinedForm']['ONCOMPLETE'] = 'Lõpetamisel';
$lang['et_EE']['UserDefinedForm']['ONCOMPLETELABEL'] = 'Näita lõpetamisel';
$lang['et_EE']['UserDefinedForm']['RECEIVED'] = 'Vastuvõetud kaastööd';
$lang['et_EE']['UserDefinedForm']['SUBMISSIONS'] = 'Kaastööd';
$lang['et_EE']['UserDefinedForm']['SUBMIT'] = 'Saada';
$lang['et_EE']['UserDefinedForm']['TEXTONSUBMIT'] = 'Kiri saatmise nupul:';
$lang['et_EE']['UserDefinedForm_SubmittedFormEmail']['EMAILSUBJECT'] = 'Vormi kaastööd';

?>