<?php

/**
 * Malay (Malaysia) language pack
 * @package modules: userforms
 * @subpackage i18n
 */

i18n::include_locale_file('modules: userforms', 'en_US');

global $lang;

if(array_key_exists('ms_MY', $lang) && is_array($lang['ms_MY'])) {
	$lang['ms_MY'] = array_merge($lang['en_US'], $lang['ms_MY']);
} else {
	$lang['ms_MY'] = $lang['en_US'];
}

$lang['ms_MY']['EditableCheckbox.ss']['CHECKBOX'] = 'Medan kotak pilihan';
$lang['ms_MY']['EditableCheckbox.ss']['DELETE'] = 'Hapuskan medan ini';
$lang['ms_MY']['EditableCheckbox.ss']['DRAG'] = 'Seret untuk mengubah susunan medan';
$lang['ms_MY']['EditableCheckbox.ss']['LOCKED'] = 'Medan ini tidak boleh diubah';
$lang['ms_MY']['EditableCheckbox.ss']['MORE'] = 'Pilihan lanjut';
$lang['ms_MY']['EditableCheckboxGroupField.ss']['ADD'] = 'Tambah pilihan baru';
$lang['ms_MY']['EditableCheckboxGroupField.ss']['CHECKBOXGROUP'] = 'Kumpulan kotak pilihan';
$lang['ms_MY']['EditableCheckboxGroupField.ss']['DELETE'] = 'Hapuskan medan ini';
$lang['ms_MY']['EditableCheckboxGroupField.ss']['DRAG'] = 'Seret untuk mengubah susunan medan';
$lang['ms_MY']['EditableCheckboxGroupField.ss']['LOCKED'] = 'Medan ini tidak boleh diubah';
$lang['ms_MY']['EditableCheckboxGroupField.ss']['MORE'] = 'Pilihan lanjut';
$lang['ms_MY']['EditableCheckboxGroupField.ss']['REQUIRED'] = 'Medan ini diperlukan untuk boran gini dan tidak bole dihapuskan';
$lang['ms_MY']['EditableCheckboxOption.ss']['DELETE'] = 'Hapuskan pilihan ini';
$lang['ms_MY']['EditableCheckboxOption.ss']['DRAG'] = 'Seret untuk mengubah susunan pilihan';
$lang['ms_MY']['EditableCheckboxOption.ss']['LOCKED'] = 'Medan ini tidak bole diubah';
$lang['ms_MY']['EditableDateField.ss']['DATE'] = 'Medan Tarikh';
$lang['ms_MY']['EditableDateField.ss']['DELETE'] = 'Hapuskan medan ini';
$lang['ms_MY']['EditableDateField.ss']['DRAG'] = 'Seret untuk mengubah susunan medan';
$lang['ms_MY']['EditableDateField.ss']['MORE'] = 'Pilihan lanjut';
$lang['ms_MY']['EditableDropdown.ss']['ADD'] = 'Tambah pilihan baru';
$lang['ms_MY']['EditableDropdown.ss']['DELETE'] = 'Hapuskan medan ini';
$lang['ms_MY']['EditableDropdown.ss']['DRAG'] = 'Seret untuk mengubah susunan medan';
$lang['ms_MY']['EditableDropdown.ss']['DROPDOWN'] = 'Kotak dropdown';
$lang['ms_MY']['EditableDropdown.ss']['LOCKED'] = 'Medan ini tidak bole diubah';
$lang['ms_MY']['EditableDropdown.ss']['MORE'] = 'Pilihan lanjut';
$lang['ms_MY']['EditableDropdown.ss']['REQUIRED'] = 'Medan ini diperlukan untuk borang ini dan tidak boleh dihapuskan';
$lang['ms_MY']['EditableDropdownOption.ss']['DELETE'] = 'Hapuskan pilihan ini';
$lang['ms_MY']['EditableDropdownOption.ss']['DRAG'] = 'Seret untuk mengubah susunan pilihan';
$lang['ms_MY']['EditableDropdownOption.ss']['LOCKED'] = 'Medan-medan ini tidak boleh diubah';
$lang['ms_MY']['EditableEmailField.ss']['DELETE'] = 'Hapuskan medan ini';
$lang['ms_MY']['EditableEmailField.ss']['DRAG'] = 'Seret untuk mengubah susunan medan';
$lang['ms_MY']['EditableEmailField.ss']['EMAIL'] = 'Medan alamat e-mail';
$lang['ms_MY']['EditableEmailField.ss']['MORE'] = 'Pilihan Lanjut';
$lang['ms_MY']['EditableEmailField.ss']['REQUIRED'] = 'Medan ini diperlukan untuk borang ini dan tidak boleh dihapuskan';
$lang['ms_MY']['EditableFileField.ss']['DELETE'] = 'Hapuskan medan ini';
$lang['ms_MY']['EditableFileField.ss']['DRAG'] = 'Seret untuk mengubah susunan medan';
$lang['ms_MY']['EditableFileField.ss']['FILE'] = 'Medan muatnaik fail';
$lang['ms_MY']['EditableFileField.ss']['MORE'] = 'Pilihan lanjut';
$lang['ms_MY']['EditableFormField.ss']['DELETE'] = 'Hapuskan medan ini';
$lang['ms_MY']['EditableFormField.ss']['DRAG'] = 'Seret untuk mengubah susunan medan';
$lang['ms_MY']['EditableFormField.ss']['LOCKED'] = 'Medan ini tidak boleh dihapuskan';
$lang['ms_MY']['EditableFormField.ss']['MORE'] = 'Pilihan lanjut';
$lang['ms_MY']['EditableFormField.ss']['REQUIRED'] = 'Medan ini diperlukan untuk borang ini dan tidak boleh dihapuskan';
$lang['ms_MY']['EditableFormFieldOption.ss']['DELETE'] = 'Hapuskan pilihan ini';
$lang['ms_MY']['EditableFormFieldOption.ss']['DRAG'] = 'Seret untuk mengubah susunan medan';
$lang['ms_MY']['EditableFormFieldOption.ss']['LOCKED'] = 'Medan-medan ini tidak boleh diubah';
$lang['ms_MY']['SubmittedFormEmail.ss']['SUBMITTED'] = 'Data berikut telah dihantar ke laman web anda:';
$lang['ms_MY']['UserDefinedForm']['FORM'] = 'Borang';
$lang['ms_MY']['UserDefinedForm']['NORESULTS'] = 'Tiada carian sepadan ditemui';
$lang['ms_MY']['UserDefinedForm']['ONCOMPLETE'] = 'Setelah lengkap';
$lang['ms_MY']['UserDefinedForm']['ONCOMPLETELABEL'] = 'Paparkan setelah lengkap';
$lang['ms_MY']['UserDefinedForm']['RECEIVED'] = 'Kiriman yang diterima';
$lang['ms_MY']['UserDefinedForm']['SUBMISSIONS'] = 'Kiriman';
$lang['ms_MY']['UserDefinedForm']['SUBMIT'] = 'Hantar';
$lang['ms_MY']['UserDefinedForm']['TEXTONSUBMIT'] = 'Teks di atas butang hantar:';
$lang['ms_MY']['UserDefinedForm_SubmittedFormEmail']['EMAILSUBJECT'] = 'Penghantaran borang';

?>