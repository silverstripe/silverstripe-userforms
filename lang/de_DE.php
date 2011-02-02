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

$lang['de_DE']['EditableOption']['Title'] = 'Titel';
$lang['de_DE']['EditableOption']['Value'] = 'Wert';
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
$lang['de_DE']['EditableCheckboxOption.ss']['DRAG'] = 'Klicken und Festhalten um die Anordnung der Auswahl zu veröndern';
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
$lang['de_DE']['EditableFileField.ss']['FILE'] = 'Dateihochlade Feld';
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
$lang['de_DE']['EditableTextField.ss']['DELETE'] = 'Dieses Feld löschen';
$lang['de_DE']['EditableTextField.ss']['DRAG'] = 'Klicken und Festhalten um die Anordnung der Felder zu verändern';
$lang['de_DE']['EditableTextField.ss']['MORE'] = 'Mehr Optionen';
$lang['de_DE']['EditableTextField.ss']['TEXTFIELD'] = 'Textfeld';
$lang['de_DE']['EditableTextField']['TEXTBOXLENGTH'] = 'Länge der Textbox';
$lang['de_DE']['EditableTextField']['TEXTLENGTH'] = 'Textlänge';
$lang['de_DE']['FieldEditor']['EMAILONSUBMIT'] = 'Eingaben nach Absenden per E-Mail verschicken:';
$lang['de_DE']['FieldEditor']['EMAILSUBMISSION'] = 'Anfrage absenden an:';
$lang['de_DE']['FieldEditor.ss']['ADD'] = 'Hinzufügen';
$lang['de_DE']['FieldEditor.ss']['CHECKBOX'] = 'Checkbox';
$lang['de_DE']['FieldEditor.ss']['CHECKBOXGROUP'] = 'Checkboxen';
$lang['de_DE']['FieldEditor.ss']['CHECKBOXGROUPTITLE'] = 'Checkbox Gruppenfeld hinzufügen';
$lang['de_DE']['FieldEditor.ss']['CHECKBOXTITLE'] = 'Checkbox hinzufügen';
$lang['de_DE']['FieldEditor.ss']['DATE'] = 'Datum';
$lang['de_DE']['FieldEditor.ss']['DATETITLE'] = 'Datum Überschrift hinzufügen';
$lang['de_DE']['FieldEditor.ss']['DROPDOWN'] = 'Dropdownfeld';
$lang['de_DE']['FieldEditor.ss']['DROPDOWNTITLE'] = 'Dropdown Feld hinzufügen';
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
$lang['de_DE']['SubmittedFormEmail.ss']['SUBMITTED'] = 'Die folgenden Daten wurden an Ihre Website Übermittelt:';
$lang['de_DE']['SubmittedFormReportField.ss']['SUBMITTED'] = 'Übermittelt an';
$lang['de_DE']['SubmittedFormReportField.ss']['NOSUBMISSIONS'] = 'keine Einreichungen vorhanden';
$lang['en_US']['SubmittedFormReportField.ss']['DELETEALLSUBMISSIONS'] = 'Alle Einreichungen löschen';
$lang['en_US']['SubmittedFormReportField.ss']['DELETESUBMISSION'] = 'Einreichung löschen';
$lang['en_US']['SubmittedFormReportField.ss']['EXPORTSUBMISSIONS'] = 'Einreichungen zu CSV exportieren';
$lang['de_DE']['UserDefinedForm']['NORESULTS'] = 'Es wurde kein passendes Ergebnis gefunden';
$lang['de_DE']['UserDefinedForm_SubmittedFormEmail']['EMAILSUBJECT'] = 'Eingereichtes Formular';
$lang['de_DE']['UserDefinedForm']['AEMAILRECIPIENT'] = 'Email Empfänger';
$lang['de_DE']['UserDefinedForm']['EMAILADDRESS'] = 'Email';
$lang['de_DE']['UserDefinedForm']['EMAILBODY'] = 'Body';
$lang['de_DE']['UserDefinedForm']['EMAILFROM'] = 'von';
$lang['de_DE']['UserDefinedForm']['EMAILRECIPIENTS'] = 'Email Empfänger';
$lang['de_DE']['UserDefinedForm']['EMAILSUBJECT'] = 'Email Betreff';
$lang['de_DE']['UserDefinedForm']['FORM'] = 'Formular';
$lang['de_DE']['UserDefinedForm']['FROMADDRESS'] = 'Versende Email von';
$lang['de_DE']['UserDefinedForm']['HIDEFORMDATA'] = 'Verstecke Formular Daten von Email';
$lang['de_DE']['UserDefinedForm']['ONCOMPLETE'] = 'Nach Fertigstellung';
$lang['de_DE']['UserDefinedForm']['ONCOMPLETELABEL'] = 'Nach Vervollständigung anzeigen';
$lang['de_DE']['UserDefinedForm']['OPTIONS'] = 'Einstellungen';
$lang['de_DE']['UserDefinedForm']['ORSELECTAFIELDTOUSEASFROM'] = '.. oder folgendes Feld als "von Adresse" verwenden';
$lang['de_DE']['UserDefinedForm']['ORSELECTAFIELDTOUSEASTO'] = '.. oder folgendes Feld als "zu Adresse" verwenden';
$lang['de_DE']['UserDefinedForm']['RECEIVED'] = 'Erhaltene Einreichungen';
$lang['de_DE']['UserDefinedForm']['SAVESUBMISSIONS'] = 'Keine Einreichungen am Server speichern';
$lang['de_DE']['UserDefinedForm']['SENDEMAILTO'] = 'Senden an';
$lang['de_DE']['UserDefinedForm']['SENDPLAIN'] = 'Email als reinen Text versenden (HTML wird entfernt)';
$lang['de_DE']['UserDefinedForm']['SHOWCLEARFORM'] = 'Zeige den Formular-Leeren-Schaltfläche';
$lang['de_DE']['UserDefinedForm']['SINGULARNAME'] = 'Benutzer definiertes Formular';
$lang['de_DE']['UserDefinedForm']['PLURALNAME'] = 'Benutzer definiertes Formulare';
$lang['de_DE']['UserDefinedForm']['SUBMISSIONS'] = 'Einreichungen';
$lang['de_DE']['UserDefinedForm']['SUBMITBUTTON'] = 'Absenden';
$lang['de_DE']['UserDefinedForm']['TEXTONSUBMIT'] = 'Text des Absende-Schaltfläche:';
$lang['de_DE']['UserDefinedForm_EmailRecipient']['PLURALNAME'] = 'Email Einreichungen des Benutzer definiertes Formulars';
$lang['de_DE']['UserDefinedForm_EmailRecipient']['SINGULARNAME'] = 'Email Einreichung des Benutzer definiertes Formulars';

?>