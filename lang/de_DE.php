<?php

/**
 * German (Germany) language pack
 * @package modules: userforms
 * @subpackage i18n
 */

i18n::include_locale_file('modules: userforms', 'en_US');

global $lang;

if(array_key_exists('de_DE', $lang) && is_array($lang['de_DE'])) {
	$lang['de_DE'] = array_merge($lang['en_US'], $lang['de_DE']);
} else {
	$lang['de_DE'] = $lang['en_US'];
}

$lang['de_DE']['EditableCheckbox']['ANY'] = 'jede';
$lang['de_DE']['EditableCheckbox']['NOTSELECTED'] = 'nicht selektiert';
$lang['de_DE']['EditableCheckbox']['SELECTED'] = 'selektiert';
$lang['de_DE']['EditableCheckbox.ss']['CHECKBOX'] = 'Auswahl Feld';
$lang['de_DE']['EditableCheckbox.ss']['DELETE'] = 'Lösche dieses Feld';
$lang['de_DE']['EditableCheckbox.ss']['DRAG'] = 'Ziehen und halten um die Anordnung der Felder zu verändern';
$lang['de_DE']['EditableCheckbox.ss']['LOCKED'] = 'Dieses Feld kann nicht verändert werden';
$lang['de_DE']['EditableCheckbox.ss']['MORE'] = 'Mehr Möglichkeiten';
$lang['de_DE']['EditableCheckboxGroupField.ss']['ADD'] = 'Neue Option hinzufügen';
$lang['de_DE']['EditableCheckboxGroupField.ss']['CHECKBOXGROUP'] = 'Checkbox Gruppe';
$lang['de_DE']['EditableCheckboxGroupField.ss']['DELETE'] = 'Dieses Feld löschen';
$lang['de_DE']['EditableCheckboxGroupField.ss']['DRAG'] = 'Klicken und Festhalten um die Anordnung der Felder zu verändern';
$lang['de_DE']['EditableCheckboxGroupField.ss']['LOCKED'] = 'Diese Felder können nicht verändert werden';
$lang['de_DE']['EditableCheckboxGroupField.ss']['MORE'] = 'Mehr Optionen';
$lang['de_DE']['EditableCheckboxGroupField.ss']['REQUIRED'] = 'Dieses Feld ist für dieses Formular erforderlich und kann nicht gelöscht werden';
$lang['de_DE']['EditableCheckboxOption.ss']['DELETE'] = 'Diese Option entfernen';
$lang['de_DE']['EditableCheckboxOption.ss']['DRAG'] = 'Klicken und Festhalten um die Anordnung der Auswahl zu verändern';
$lang['de_DE']['EditableCheckboxOption.ss']['LOCKED'] = 'Diese Felder können nicht angepasst werden';
$lang['de_DE']['EditableDateField.ss']['DATE'] = 'Datumsfeld';
$lang['de_DE']['EditableDateField.ss']['DELETE'] = 'Diese Feld löschen';
$lang['de_DE']['EditableDateField.ss']['DRAG'] = 'Klicken und Festhalten um die Anordnung der Felder zu verändern';
$lang['de_DE']['EditableDateField.ss']['MORE'] = 'Mehr Optionen';
$lang['de_DE']['EditableDropdown.ss']['ADD'] = 'Neue Auswahl hinzufügen';
$lang['de_DE']['EditableDropdown.ss']['DELETE'] = 'Dieses Feld löschen';
$lang['de_DE']['EditableDropdown.ss']['DRAG'] = 'Klicken und Festhalten um die Anordnung der Felder zu verändern';
$lang['de_DE']['EditableDropdown.ss']['DROPDOWN'] = 'Dropdownfeld';
$lang['de_DE']['EditableDropdown.ss']['LOCKED'] = 'Diese Felder können nicht angepasst werden';
$lang['de_DE']['EditableDropdown.ss']['MORE'] = 'Mehr Optionen';
$lang['de_DE']['EditableDropdown.ss']['REQUIRED'] = 'Dieses Feld wird für dieses Formular benötigt und kann nicht gelöscht werden.';
$lang['de_DE']['EditableDropdownOption.ss']['DELETE'] = 'Diese Auswahl löschen';
$lang['de_DE']['EditableDropdownOption.ss']['DRAG'] = 'Klicken und Festhalten um die Anordnung der Auswahl zu verändern';
$lang['de_DE']['EditableDropdownOption.ss']['LOCKED'] = 'Diese Felder können nicht verändert werden';
$lang['de_DE']['EditableEmailField']['SENDCOPY'] = 'Senden Sie eine Kopie der Anfrage an diese Adresse';
$lang['de_DE']['EditableEmailField.ss']['DELETE'] = 'Lösche dieses Feld';
$lang['de_DE']['EditableEmailField.ss']['DRAG'] = 'Klicken und Festhalten um die Anordnung der Felder zu verändern';
$lang['de_DE']['EditableEmailField.ss']['EMAIL'] = 'E-Mail Adress Feld';
$lang['de_DE']['EditableEmailField.ss']['MORE'] = 'Mehr Möglichkeiten';
$lang['de_DE']['EditableEmailField.ss']['REQUIRED'] = 'Dieses Feld wird für dieses Formular benötigt und kann nicht gelöscht werden.';
$lang['de_DE']['EditableFileField.ss']['DELETE'] = 'Lösche dieses Feld';
$lang['de_DE']['EditableFileField.ss']['DRAG'] = 'Klicken und Festhalten um die Anordnung der Felder zu verändern';
$lang['de_DE']['EditableFileField.ss']['FILE'] = 'Dateiuplad Feld';
$lang['de_DE']['EditableFileField.ss']['MORE'] = 'Mehr Möglichkeiten';
$lang['de_DE']['EditableFormField']['ENTERQUESTION'] = 'Tragen Sie die Frage hier ein';
$lang['de_DE']['EditableFormField']['REQUIRED'] = 'Pflichtfeld?';
$lang['de_DE']['EditableFormField.ss']['DELETE'] = 'Lösche dieses Feld';
$lang['de_DE']['EditableFormField.ss']['DRAG'] = 'Klicken und Festhalten um die Anordnung der Felder zu verändern';
$lang['de_DE']['EditableFormField.ss']['LOCKED'] = 'Diese Felder können nicht verändert werden';
$lang['de_DE']['EditableFormField.ss']['MORE'] = 'Mehr Möglichkeiten';
$lang['de_DE']['EditableFormField.ss']['REQUIRED'] = 'Dieses Feld wird für dieses Formular benötigt und kann nicht gelöscht werden.';
$lang['de_DE']['EditableFormFieldOption.ss']['DELETE'] = 'Diese Auswahl entfernen';
$lang['de_DE']['EditableFormFieldOption.ss']['DRAG'] = 'Klicken und Festhalten um die Anordnung der Felder zu verändern';
$lang['de_DE']['EditableFormFieldOption.ss']['LOCKED'] = 'Diese Felder können nicht verändert werden';
$lang['de_DE']['EditableFormHeading.ss']['DELETE'] = 'Löschen Sie dieses Feld';
$lang['de_DE']['EditableFormHeading.ss']['DRAG'] = 'Klicken und Festhalten um die Anordnung der Felder zu verändern';
$lang['de_DE']['EditableFormHeading.ss']['HEADING'] = 'Überschriftsfeld';
$lang['de_DE']['EditableFormHeading.ss']['MORE'] = 'Mehr Optionen';
$lang['de_DE']['EditableRadioField.ss']['ADD'] = 'Auswahloption hinzufügen';
$lang['de_DE']['EditableRadioField.ss']['DELETE'] = 'Dieses Feld löschen';
$lang['de_DE']['EditableRadioField.ss']['DRAG'] = 'Klicken und Festhalten um die Anordnung der Felder zu verändern';
$lang['de_DE']['EditableRadioField.ss']['LOCKED'] = 'Diese Felder können nicht verändert werden';
$lang['de_DE']['EditableRadioField.ss']['MORE'] = 'Mehr Optionen';
$lang['de_DE']['EditableRadioField.ss']['REQUIRED'] = 'Dieses Feld wird für dieses Formular benötigt, und kann deswegen nicht gelöscht werden';
$lang['de_DE']['EditableRadioField.ss']['SET'] = 'Radiobutton';
$lang['de_DE']['EditableRadioOption.ss']['DELETE'] = 'Diese Auswahloption entfernen';
$lang['de_DE']['EditableRadioOption.ss']['DRAG'] = 'Ziehen neu anordnen, von Optionen';
$lang['de_DE']['EditableRadioOption.ss']['LOCKED'] = 'Diese Felder können nicht modifiziert werden';
$lang['de_DE']['EditableTextField']['DEFAULTTEXT'] = 'Standardtext';
$lang['de_DE']['EditableTextField']['NUMBERROWS'] = 'Anzahl der Zeilen';
$lang['de_DE']['EditableTextField.ss']['DELETE'] = 'Diese Feld löschen';
$lang['de_DE']['EditableTextField.ss']['DRAG'] = 'Ziehen neu ordnen Reihenfolge der Felder';
$lang['de_DE']['EditableTextField.ss']['MORE'] = 'Mehr Optionen';
$lang['de_DE']['EditableTextField.ss']['TEXTFIELD'] = 'Textfeld';
$lang['de_DE']['EditableTextField']['TEXTBOXLENGTH'] = 'Länger der Textbox';
$lang['de_DE']['EditableTextField']['TEXTLENGTH'] = 'Textlänge';
$lang['de_DE']['FieldEditor']['EMAILONSUBMIT'] = 'Eingaben nach Absenden per E-Mail verschicken:';
$lang['de_DE']['FieldEditor']['EMAILSUBMISSION'] = 'Anfrage absenden an:';
$lang['de_DE']['FieldEditor.ss']['ADD'] = 'Hinzufügen';
$lang['de_DE']['FieldEditor.ss']['CHECKBOX'] = 'Checkbox';
$lang['de_DE']['FieldEditor.ss']['CHECKBOXGROUP'] = 'Checkboxen';
$lang['de_DE']['FieldEditor.ss']['CHECKBOXGROUPTITLE'] = 'Hinzufügen Checkbox Gruppenfeld';
$lang['de_DE']['FieldEditor.ss']['CHECKBOXTITLE'] = 'Checkbox hinzufügen';
$lang['de_DE']['FieldEditor.ss']['DATE'] = 'Datum';
$lang['de_DE']['FieldEditor.ss']['DATETITLE'] = 'Hinzufügen Datum Überschrift';
$lang['de_DE']['FieldEditor.ss']['DROPDOWN'] = 'Dropdownfeld';
$lang['de_DE']['FieldEditor.ss']['DROPDOWNTITLE'] = 'Dropdownfeld hinzufügen';
$lang['de_DE']['FieldEditor.ss']['EMAIL'] = 'E-Mail';
$lang['de_DE']['FieldEditor.ss']['EMAILTITLE'] = 'E-Mail Feld hinzufügen';
$lang['de_DE']['FieldEditor.ss']['FILE'] = 'Datei';
$lang['de_DE']['FieldEditor.ss']['FILETITLE'] = 'Datei-Upload Feld hinzufügen';
$lang['de_DE']['FieldEditor.ss']['FORMHEADING'] = 'Überschrift';
$lang['de_DE']['FieldEditor.ss']['FORMHEADINGTITLE'] = 'Formularüberschrift hinzufügen';
$lang['de_DE']['FieldEditor.ss']['MEMBER'] = 'Mitglieder Liste';
$lang['de_DE']['FieldEditor.ss']['MEMBERTITLE'] = 'Hinzufügen Mitglieder Liste Feld';
$lang['de_DE']['FieldEditor.ss']['RADIOSET'] = 'Radio';
$lang['de_DE']['FieldEditor.ss']['RADIOSETTITLE'] = 'Hinzufügen Radio-Button';
$lang['de_DE']['FieldEditor.ss']['TEXT'] = 'Text';
$lang['de_DE']['FieldEditor.ss']['TEXTTITLE'] = 'Textfeld hinzufügen';
$lang['de_DE']['SubmittedFormEmail.ss']['SUBMITTED'] = 'Die folgenden Daten wurden an Ihre Website übermittelt:';
$lang['de_DE']['SubmittedFormReportField.ss']['SUBMITTED'] = 'Übermittelt an';
$lang['de_DE']['UserDefinedForm']['FORM'] = 'Formular';
$lang['de_DE']['UserDefinedForm']['NORESULTS'] = 'Es wurde kein passendes Ergebnis gefunden';
$lang['de_DE']['UserDefinedForm']['ONCOMPLETE'] = 'Nach Fertigstellung';
$lang['de_DE']['UserDefinedForm']['ONCOMPLETELABEL'] = 'Nach Vervollständigung anzeigen';
$lang['de_DE']['UserDefinedForm']['RECEIVED'] = 'Erhaltene Einreichungen';
$lang['de_DE']['UserDefinedForm']['SUBMISSIONS'] = 'Einreichungen';
$lang['de_DE']['UserDefinedForm']['SUBMIT'] = 'Einreichen';
$lang['de_DE']['UserDefinedForm']['TEXTONSUBMIT'] = 'Text auf der Einreich-Schaltfläche';
$lang['de_DE']['UserDefinedForm_SubmittedFormEmail']['EMAILSUBJECT'] = 'Eingereichtes Formular';

?>