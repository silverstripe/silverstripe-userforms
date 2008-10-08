<?php

/**
 * Finnish (Finland) language pack
 * @package modules: userforms
 * @subpackage i18n
 */

i18n::include_locale_file('modules: userforms', 'en_US');

global $lang;

if(array_key_exists('fi_FI', $lang) && is_array($lang['fi_FI'])) {
	$lang['fi_FI'] = array_merge($lang['en_US'], $lang['fi_FI']);
} else {
	$lang['fi_FI'] = $lang['en_US'];
}

$lang['fi_FI']['EditableCheckbox']['ANY'] = 'Mikä tahansa';
$lang['fi_FI']['EditableCheckbox']['NOTSELECTED'] = 'Ei valittu';
$lang['fi_FI']['EditableCheckbox']['SELECTED'] = 'Valittu';
$lang['fi_FI']['EditableCheckbox.ss']['DELETE'] = 'Poista tämä kenttä';
$lang['fi_FI']['EditableCheckbox.ss']['DRAG'] = 'Vedä järjestääksesi kentät uudelleen';
$lang['fi_FI']['EditableCheckbox.ss']['LOCKED'] = 'Tätä kenttää ei voi muokata';
$lang['fi_FI']['EditableCheckbox.ss']['MORE'] = 'Lisää valintoja';
$lang['fi_FI']['EditableCheckboxGroupField.ss']['ADD'] = 'Lisää uusi valinta';
$lang['fi_FI']['EditableCheckboxGroupField.ss']['DELETE'] = 'Poista tämä kenttä';
$lang['fi_FI']['EditableCheckboxGroupField.ss']['DRAG'] = 'Vedä järjestääksesi kentät uudelleen';
$lang['fi_FI']['EditableCheckboxGroupField.ss']['LOCKED'] = 'Näitä kenttiä ei voi muokata';
$lang['fi_FI']['EditableCheckboxGroupField.ss']['MORE'] = 'Lisää valintoja';
$lang['fi_FI']['EditableCheckboxGroupField.ss']['REQUIRED'] = 'Tätä kenttää tarvitaan tässä lomakkeessa eikä sitä voi poistaa';
$lang['fi_FI']['EditableCheckboxOption.ss']['DELETE'] = 'Poista tämä valinta';
$lang['fi_FI']['EditableCheckboxOption.ss']['DRAG'] = 'Vedä järjestääksesi valinnat uudelleen';
$lang['fi_FI']['EditableCheckboxOption.ss']['LOCKED'] = 'Näitä kenttiä ei voi muokata';
$lang['fi_FI']['EditableDateField.ss']['DATE'] = 'Päivämääräkenttä';
$lang['fi_FI']['EditableDateField.ss']['DELETE'] = 'Poista tämä kenttä';
$lang['fi_FI']['EditableDateField.ss']['DRAG'] = 'Vedä järjestääksesi kentät uudelleen';
$lang['fi_FI']['EditableDateField.ss']['MORE'] = 'Lisää valintoja';
$lang['fi_FI']['EditableDropdown.ss']['DELETE'] = 'Poista tämä kenttä';
$lang['fi_FI']['EditableDropdown.ss']['DRAG'] = 'Vedä järjestääksesi kentät uudelleen';
$lang['fi_FI']['EditableDropdown.ss']['DROPDOWN'] = 'Pudotusvalikko';
$lang['fi_FI']['EditableDropdown.ss']['LOCKED'] = 'Näitä kenttiä ei voi muokata';
$lang['fi_FI']['EditableDropdown.ss']['MORE'] = 'Lisää valintoja';
$lang['fi_FI']['EditableDropdown.ss']['REQUIRED'] = 'Tätä kenttää tarvitaan tässä lomakkeessa eikä sitä voi poistaa';
$lang['fi_FI']['EditableEmailField']['SENDCOPY'] = 'Lähetä kopio lähetyksestä tähän osoitteeseen';
$lang['fi_FI']['EditableFormField']['ENTERQUESTION'] = 'Syötä kysymys';
$lang['fi_FI']['EditableFormField']['REQUIRED'] = 'Vaadittu?';
$lang['fi_FI']['EditableTextField']['DEFAULTTEXT'] = 'Oletusteksti';
$lang['fi_FI']['EditableTextField']['NUMBERROWS'] = 'Rivejen määrä';
$lang['fi_FI']['EditableTextField']['TEXTBOXLENGTH'] = 'Tekstilaatikon pituus';
$lang['fi_FI']['EditableTextField']['TEXTLENGTH'] = 'Tekstin pituus';
$lang['fi_FI']['FieldEditor']['EMAILSUBMISSION'] = 'Lähetä lähetys kohteeseen: ';
$lang['fi_FI']['SubmittedFormEmail.ss']['SUBMITTED'] = 'Seuraava tiedot lähetettiin verkkosivustollesi:';
$lang['fi_FI']['UserDefinedForm']['FORM'] = 'Lähettäjä';
$lang['fi_FI']['UserDefinedForm']['NORESULTS'] = 'Vastaavia tuloksia ei löytynyt.';
$lang['fi_FI']['UserDefinedForm']['ONCOMPLETE'] = 'Valmistuessa';
$lang['fi_FI']['UserDefinedForm']['ONCOMPLETELABEL'] = 'Näytä valmistuessa';
$lang['fi_FI']['UserDefinedForm']['RECEIVED'] = 'Vastaanotetut lähetykset';
$lang['fi_FI']['UserDefinedForm']['SUBMISSIONS'] = 'Lähetykset';
$lang['fi_FI']['UserDefinedForm']['SUBMIT'] = 'Lähetä';
$lang['fi_FI']['UserDefinedForm']['TEXTONSUBMIT'] = 'Lähetä-painikkeen teksti: ';
$lang['fi_FI']['UserDefinedForm_SubmittedFormEmail']['EMAILSUBJECT'] = 'Lomakkeen lähetys';

?>