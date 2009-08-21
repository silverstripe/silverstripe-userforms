<?php

/**
 * French (France) language pack
 * @package modules: userforms
 * @subpackage i18n
 */

i18n::include_locale_file('modules: userforms', 'en_US');

global $lang;

if(array_key_exists('fr_FR', $lang) && is_array($lang['fr_FR'])) {
	$lang['fr_FR'] = array_merge($lang['en_US'], $lang['fr_FR']);
} else {
	$lang['fr_FR'] = $lang['en_US'];
}

$lang['fr_FR']['EditableCheckbox']['ANY'] = 'N\'importe lequel';
$lang['fr_FR']['EditableCheckbox']['NOTSELECTED'] = 'Non sélectionné';
$lang['fr_FR']['EditableCheckbox']['SELECTED'] = 'Sélectionner';
$lang['fr_FR']['EditableCheckbox.ss']['CHECKBOX'] = 'Champ case à cocher';
$lang['fr_FR']['EditableCheckbox.ss']['DELETE'] = 'Supprimer ce champ';
$lang['fr_FR']['EditableCheckbox.ss']['DRAG'] = 'Glisser pour arranger l\'ordre des champs';
$lang['fr_FR']['EditableCheckbox.ss']['LOCKED'] = 'Ce champ ne peut pas être modifié';
$lang['fr_FR']['EditableCheckbox.ss']['MORE'] = 'Plus d\'options';
$lang['fr_FR']['EditableCheckboxGroupField.ss']['ADD'] = 'Ajouter une nouvelle option';
$lang['fr_FR']['EditableCheckboxGroupField.ss']['CHECKBOXGROUP'] = 'Goupe de cases à cocher';
$lang['fr_FR']['EditableCheckboxGroupField.ss']['DELETE'] = 'Supprimer ce champ';
$lang['fr_FR']['EditableCheckboxGroupField.ss']['DRAG'] = 'Glisser pour arranger l\'ordre des champs';
$lang['fr_FR']['EditableCheckboxGroupField.ss']['LOCKED'] = 'Ces champs ne peuvent pas être modifiés';
$lang['fr_FR']['EditableCheckboxGroupField.ss']['MORE'] = 'Plus d\'options';
$lang['fr_FR']['EditableCheckboxGroupField.ss']['REQUIRED'] = 'Ce champ est requis pour ce formulaire et ne peut pas être supprimé';
$lang['fr_FR']['EditableCheckboxOption.ss']['DELETE'] = 'Enlever cette option';
$lang['fr_FR']['EditableCheckboxOption.ss']['DRAG'] = 'Glisser pour arranger l\'ordre des options';
$lang['fr_FR']['EditableCheckboxOption.ss']['LOCKED'] = 'Ces champs ne peuvent pas être modifiés';
$lang['fr_FR']['EditableDateField.ss']['DATE'] = 'Champ date';
$lang['fr_FR']['EditableDateField.ss']['DELETE'] = 'Supprimer ce champ';
$lang['fr_FR']['EditableDateField.ss']['DRAG'] = 'Glisser pour arranger l\'ordre des champs';
$lang['fr_FR']['EditableDateField.ss']['MORE'] = 'Plus d\'options';
$lang['fr_FR']['EditableDropdown.ss']['ADD'] = 'Ajouter une nouvelle option';
$lang['fr_FR']['EditableDropdown.ss']['DELETE'] = 'Supprimer ce champ';
$lang['fr_FR']['EditableDropdown.ss']['DRAG'] = 'Glisser pour arranger l\'ordre des champs';
$lang['fr_FR']['EditableDropdown.ss']['DROPDOWN'] = 'Boite liste déroulante';
$lang['fr_FR']['EditableDropdown.ss']['LOCKED'] = 'Ces champs ne peuvent pas être modifiés';
$lang['fr_FR']['EditableDropdown.ss']['MORE'] = 'Plus d\'options';
$lang['fr_FR']['EditableDropdown.ss']['REQUIRED'] = 'Ce champ est requis pour ce formulaire et ne peut pas être supprimé';
$lang['fr_FR']['EditableDropdownOption.ss']['DELETE'] = 'Enlever cette option';
$lang['fr_FR']['EditableDropdownOption.ss']['DRAG'] = 'Glisser pour arranger l\'ordre des options';
$lang['fr_FR']['EditableDropdownOption.ss']['LOCKED'] = 'Ces champs ne peuvent pas être modifié';
$lang['fr_FR']['EditableEmailField']['SENDCOPY'] = 'Envoyer une copie de la proposition à cette adresse';
$lang['fr_FR']['EditableEmailField.ss']['DELETE'] = 'Supprimer ce champ';
$lang['fr_FR']['EditableEmailField.ss']['DRAG'] = 'Glisser pour arranger l\'ordre des champs';
$lang['fr_FR']['EditableEmailField.ss']['EMAIL'] = 'Champ de l\'adresse email';
$lang['fr_FR']['EditableEmailField.ss']['MORE'] = 'Plus d\'options';
$lang['fr_FR']['EditableEmailField.ss']['REQUIRED'] = 'Ce champ est requis pour ce formulaire et ne peut pas être supprimé';
$lang['fr_FR']['EditableFileField.ss']['DELETE'] = 'Supprimer ce champ';
$lang['fr_FR']['EditableFileField.ss']['DRAG'] = 'Glisser pour arranger l\'ordre des champs';
$lang['fr_FR']['EditableFileField.ss']['FILE'] = 'Champ d\'envoi de fichier';
$lang['fr_FR']['EditableFileField.ss']['MORE'] = 'Plus d\'options';
$lang['fr_FR']['EditableFormField']['ENTERQUESTION'] = 'Saisir la question';
$lang['fr_FR']['EditableFormField']['REQUIRED'] = 'Requis ?';
$lang['fr_FR']['EditableFormField.ss']['DELETE'] = 'Supprimer ce champ';
$lang['fr_FR']['EditableFormField.ss']['DRAG'] = 'Glisser pour arranger l\'ordre des champs';
$lang['fr_FR']['EditableFormField.ss']['LOCKED'] = 'Ces champs ne peuvent pas être modifiés';
$lang['fr_FR']['EditableFormField.ss']['MORE'] = 'Plus d\'options';
$lang['fr_FR']['EditableFormField.ss']['REQUIRED'] = 'Ce champ est requis pour ce formulaire et ne peut pas être supprimé';
$lang['fr_FR']['EditableFormFieldOption.ss']['DELETE'] = 'Enlever cette option';
$lang['fr_FR']['EditableFormFieldOption.ss']['DRAG'] = 'Glisser pour arranger l\'ordre des champs';
$lang['fr_FR']['EditableFormFieldOption.ss']['LOCKED'] = 'Ces champs ne peuvent pas être modifiés';
$lang['fr_FR']['EditableFormHeading.ss']['DELETE'] = 'Supprimer ce champ';
$lang['fr_FR']['EditableFormHeading.ss']['DRAG'] = 'Glisser pour arranger l\'ordre des champs';
$lang['fr_FR']['EditableFormHeading.ss']['HEADING'] = 'Champ entête';
$lang['fr_FR']['EditableFormHeading.ss']['MORE'] = 'Plus d\'options';
$lang['fr_FR']['EditableRadioField.ss']['ADD'] = 'Ajouter une nouvelle option';
$lang['fr_FR']['EditableRadioField.ss']['DELETE'] = 'Supprimer ce champ';
$lang['fr_FR']['EditableRadioField.ss']['DRAG'] = 'Glisser pour arranger l\'ordre des champs';
$lang['fr_FR']['EditableRadioField.ss']['LOCKED'] = 'Ces champs ne peuvent pas être modifiés';
$lang['fr_FR']['EditableRadioField.ss']['MORE'] = 'Plus d\'options';
$lang['fr_FR']['EditableRadioField.ss']['REQUIRED'] = 'Ce champ est requis pour ce formulaire et ne peut pas être supprimé';
$lang['fr_FR']['EditableRadioField.ss']['SET'] = 'Ensemble de bouton par radio';
$lang['fr_FR']['EditableRadioOption.ss']['DELETE'] = 'Supprimer cette option';
$lang['fr_FR']['EditableRadioOption.ss']['DRAG'] = 'Glisser pour arranger l\'ordre des options';
$lang['fr_FR']['EditableRadioOption.ss']['LOCKED'] = 'Ces champs ne peuvent pas être modifiés';
$lang['fr_FR']['EditableTextField']['DEFAULTTEXT'] = 'Texte par défaut';
$lang['fr_FR']['EditableTextField']['NUMBERROWS'] = 'Nombre de lignes';
$lang['fr_FR']['EditableTextField.ss']['DELETE'] = 'Supprimer ce champ';
$lang['fr_FR']['EditableTextField.ss']['DRAG'] = 'Glisser pour arranger l\'ordre des champs';
$lang['fr_FR']['EditableTextField.ss']['MORE'] = 'Plus d\'options';
$lang['fr_FR']['EditableTextField.ss']['TEXTFIELD'] = 'Champ texte';
$lang['fr_FR']['EditableTextField']['TEXTBOXLENGTH'] = 'Longueur de la zone de texte';
$lang['fr_FR']['EditableTextField']['TEXTLENGTH'] = 'Longueur du texte';
$lang['fr_FR']['FieldEditor']['EMAILONSUBMIT'] = 'Envoyer le formulaire par email à la soumission';
$lang['fr_FR']['FieldEditor']['EMAILSUBMISSION'] = 'Envoyer la soumission à:';
$lang['fr_FR']['FieldEditor.ss']['ADD'] = 'Ajouter';
$lang['fr_FR']['FieldEditor.ss']['CHECKBOX'] = 'Bouton à cocher';
$lang['fr_FR']['FieldEditor.ss']['CHECKBOXGROUP'] = 'Boutons à cocher';
$lang['fr_FR']['FieldEditor.ss']['CHECKBOXGROUPTITLE'] = 'Ajouter un groupe de champs de cases à cocher';
$lang['fr_FR']['FieldEditor.ss']['CHECKBOXTITLE'] = 'Ajouter un bouton à cocher';
$lang['fr_FR']['FieldEditor.ss']['DATE'] = 'Date';
$lang['fr_FR']['FieldEditor.ss']['DATETITLE'] = 'Ajouter une date à l\'entête';
$lang['fr_FR']['FieldEditor.ss']['DROPDOWN'] = 'Liste déroulante';
$lang['fr_FR']['FieldEditor.ss']['DROPDOWNTITLE'] = 'Ajouter une liste déroulante';
$lang['fr_FR']['FieldEditor.ss']['EMAIL'] = 'Email';
$lang['fr_FR']['FieldEditor.ss']['EMAILTITLE'] = 'Ajouter un champ email';
$lang['fr_FR']['FieldEditor.ss']['FILE'] = 'Fichier';
$lang['fr_FR']['FieldEditor.ss']['FILETITLE'] = 'Ajouter un champ d\'envoi de fichiers';
$lang['fr_FR']['FieldEditor.ss']['FORMHEADING'] = 'Entête';
$lang['fr_FR']['FieldEditor.ss']['FORMHEADINGTITLE'] = 'Ajouter l\'entête d\'un formulaire';
$lang['fr_FR']['FieldEditor.ss']['MEMBER'] = 'Liste des membres';
$lang['fr_FR']['FieldEditor.ss']['MEMBERTITLE'] = 'Ajouter un champ liste des membres';
$lang['fr_FR']['FieldEditor.ss']['RADIOSET'] = 'Radio';
$lang['fr_FR']['FieldEditor.ss']['RADIOSETTITLE'] = 'Ajouter un ensemble de bouton par radio';
$lang['fr_FR']['FieldEditor.ss']['TEXT'] = 'Texte';
$lang['fr_FR']['FieldEditor.ss']['TEXTTITLE'] = 'Ajouter un champ texte';
$lang['fr_FR']['SubmittedFormEmail.ss']['SUBMITTED'] = 'Les données suivantes ont été envoyées à votre site web :';
$lang['fr_FR']['SubmittedFormReportField.ss']['SUBMITTED'] = 'Proposé à';
$lang['fr_FR']['UserDefinedForm']['FORM'] = 'Formulaire';
$lang['fr_FR']['UserDefinedForm']['NORESULTS'] = 'Aucun résultat trouvé';
$lang['fr_FR']['UserDefinedForm']['ONCOMPLETE'] = 'Complétion en cours';
$lang['fr_FR']['UserDefinedForm']['ONCOMPLETELABEL'] = 'Utiliser la complétion';
$lang['fr_FR']['UserDefinedForm']['RECEIVED'] = 'Soumissions reçues';
$lang['fr_FR']['UserDefinedForm']['SUBMISSIONS'] = 'Soumissions';
$lang['fr_FR']['UserDefinedForm']['SUBMIT'] = 'Soumettre';
$lang['fr_FR']['UserDefinedForm']['TEXTONSUBMIT'] = 'Texte du bouton Envoyer :';
$lang['fr_FR']['UserDefinedForm_SubmittedFormEmail']['EMAILSUBJECT'] = 'Envoi de formulaire';

?>