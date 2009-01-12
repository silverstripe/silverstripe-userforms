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
$lang['ms_MY']['EditableCheckbox.ss']['MORE'] = 'Pilihan-pilihan lanjut';
$lang['ms_MY']['EditableCheckboxGroupField.ss']['ADD'] = 'Tambah pilihan baru';
$lang['ms_MY']['EditableCheckboxGroupField.ss']['CHECKBOXGROUP'] = 'Kumpulan kotak pilihan';
$lang['ms_MY']['EditableCheckboxGroupField.ss']['DELETE'] = 'Hapuskan medan ini';
$lang['ms_MY']['EditableCheckboxGroupField.ss']['DRAG'] = 'Seret untuk mengubah susunan medan-medan';
$lang['ms_MY']['EditableCheckboxGroupField.ss']['LOCKED'] = 'Medan-medan ini tidak boleh dipinda';
$lang['ms_MY']['EditableCheckboxGroupField.ss']['MORE'] = 'Pilihan-pilihan lanjut';
$lang['ms_MY']['EditableCheckboxGroupField.ss']['REQUIRED'] = 'Medan ini diperlukan untuk borang ini dan tidak boleh dihapuskan';
$lang['ms_MY']['EditableCheckboxOption.ss']['DELETE'] = 'Hapuskan pilihan ini';
$lang['ms_MY']['EditableCheckboxOption.ss']['DRAG'] = 'Seret untuk mengubah susunan pilihan';
$lang['ms_MY']['EditableCheckboxOption.ss']['LOCKED'] = 'Medan ini tidak bole diubah';
$lang['ms_MY']['EditableDateField.ss']['DATE'] = 'Medan Tarikh';
$lang['ms_MY']['EditableDateField.ss']['DELETE'] = 'Hapuskan medan ini';
$lang['ms_MY']['EditableDateField.ss']['DRAG'] = 'Seret untuk mengubah susunan medan-medan';
$lang['ms_MY']['EditableDateField.ss']['MORE'] = 'Pilihan-pilihan lanjut';
$lang['ms_MY']['EditableDropdown.ss']['ADD'] = 'Tambah pilihan baru';
$lang['ms_MY']['EditableDropdown.ss']['DELETE'] = 'Hapuskan medan ini';
$lang['ms_MY']['EditableDropdown.ss']['DRAG'] = 'Seret untuk mengubah susunan medan';
$lang['ms_MY']['EditableDropdown.ss']['DROPDOWN'] = 'Kotak dropdown';
$lang['ms_MY']['EditableDropdown.ss']['LOCKED'] = 'Medan ini tidak bole diubah';
$lang['ms_MY']['EditableDropdown.ss']['MORE'] = 'Pilihan-pilihan lanjut';
$lang['ms_MY']['EditableDropdown.ss']['REQUIRED'] = 'Medan ini diperlukan untuk borang ini dan tidak boleh dihapuskan';
$lang['ms_MY']['EditableDropdownOption.ss']['DELETE'] = 'Hapuskan pilihan ini';
$lang['ms_MY']['EditableDropdownOption.ss']['DRAG'] = 'Seret untuk mengubah susunan pilihan';
$lang['ms_MY']['EditableDropdownOption.ss']['LOCKED'] = 'Medan-medan ini tidak boleh diubah';
$lang['ms_MY']['EditableEmailField.ss']['DELETE'] = 'Hapuskan medan ini';
$lang['ms_MY']['EditableEmailField.ss']['DRAG'] = 'Seret untuk mengubah susunan medan';
$lang['ms_MY']['EditableEmailField.ss']['EMAIL'] = 'Medan alamat emel';
$lang['ms_MY']['EditableEmailField.ss']['MORE'] = 'Pilihan-pilihan lanjut';
$lang['ms_MY']['EditableEmailField.ss']['REQUIRED'] = 'Medan ini diperlukan untuk borang ini dan tidak boleh dihapuskan';
$lang['ms_MY']['EditableFileField.ss']['DELETE'] = 'Hapuskan medan ini';
$lang['ms_MY']['EditableFileField.ss']['DRAG'] = 'Seret untuk mengubah susunan medan-medan';
$lang['ms_MY']['EditableFileField.ss']['FILE'] = 'Medan muat naik fail';
$lang['ms_MY']['EditableFileField.ss']['MORE'] = 'Pilihan-pilihan lanjut';
$lang['ms_MY']['EditableFormField.ss']['DELETE'] = 'Hapuskan medan ini';
$lang['ms_MY']['EditableFormField.ss']['DRAG'] = 'Seret untuk mengubah susunan medan-medan';
$lang['ms_MY']['EditableFormField.ss']['LOCKED'] = 'Medan-medan ini tidak boleh dihapuskan';
$lang['ms_MY']['EditableFormField.ss']['MORE'] = 'Pilihan-pilihan lanjut';
$lang['ms_MY']['EditableFormField.ss']['REQUIRED'] = 'Medan ini diperlukan untuk borang ini dan tidak boleh dihapuskan';
$lang['ms_MY']['EditableFormFieldOption.ss']['DELETE'] = 'Hapuskan pilihan ini';
$lang['ms_MY']['EditableFormFieldOption.ss']['DRAG'] = 'Seret untuk mengubah tatatertib medan-medan';
$lang['ms_MY']['EditableFormFieldOption.ss']['LOCKED'] = 'Medan-medan ini tidak boleh dipinda';
$lang['ms_MY']['EditableFormHeading.ss']['DELETE'] = 'Hapuskan medan ini';
$lang['ms_MY']['EditableFormHeading.ss']['HEADING'] = 'Medan tajuk';
$lang['ms_MY']['EditableRadioField.ss']['ADD'] = 'Tambahkan pilihan baru';
$lang['ms_MY']['EditableRadioField.ss']['DELETE'] = 'Hapuskan medan ini';
$lang['ms_MY']['EditableRadioField.ss']['DRAG'] = 'Heret untuk mengatur semula tatatertib medan-medan';
$lang['ms_MY']['EditableRadioField.ss']['LOCKED'] = 'Medan-medan ini tidak boleh dipinda';
$lang['ms_MY']['EditableRadioField.ss']['REQUIRED'] = 'Medan ini dikehendaki untuk borang ini dan tidak boleh dihapuskan';
$lang['ms_MY']['EditableRadioOption.ss']['DELETE'] = 'Hapuskan pilihan ini';
$lang['ms_MY']['EditableRadioOption.ss']['DRAG'] = 'Heret untuk mengatur semula tatatertib pilihan-pilihan';
$lang['ms_MY']['EditableRadioOption.ss']['LOCKED'] = 'Medan-medan ini tidak boleh dipinda';
$lang['ms_MY']['EditableTextField.ss']['DELETE'] = 'Hapuskan medan ini';
$lang['ms_MY']['EditableTextField.ss']['DRAG'] = 'Heret untuk mengatur semula tatatertib medan-medan';
$lang['ms_MY']['EditableTextField.ss']['MORE'] = 'Pilihan-pilihan lanjut';
$lang['ms_MY']['EditableTextField.ss']['TEXTFIELD'] = 'Medan teks';
$lang['ms_MY']['FieldEditor']['EMAILONSUBMIT'] = 'Emel borang apabila diserahkan:';
$lang['ms_MY']['FieldEditor']['EMAILSUBMISSION'] = 'Pengajuan emel kepada:';
$lang['ms_MY']['FieldEditor.ss']['ADD'] = 'Tambah';
$lang['ms_MY']['FieldEditor.ss']['CHECKBOX'] = 'Kotak tanda semak';
$lang['ms_MY']['FieldEditor.ss']['CHECKBOXGROUP'] = 'Kotak-kotak tanda semak';
$lang['ms_MY']['FieldEditor.ss']['CHECKBOXGROUPTITLE'] = 'Tambah medan kumpulan kotak tanda semak';
$lang['ms_MY']['FieldEditor.ss']['CHECKBOXTITLE'] = 'Tambah kotak tanda semak';
$lang['ms_MY']['FieldEditor.ss']['DATE'] = 'Tarikh';
$lang['ms_MY']['FieldEditor.ss']['EMAIL'] = 'Emel';
$lang['ms_MY']['FieldEditor.ss']['EMAILTITLE'] = 'Tambah medan emel';
$lang['ms_MY']['FieldEditor.ss']['FILE'] = 'Fail';
$lang['ms_MY']['FieldEditor.ss']['FILETITLE'] = 'Tambah medan muat naik fail';
$lang['ms_MY']['FieldEditor.ss']['FORMHEADING'] = 'Kepala Tajuk';
$lang['ms_MY']['FieldEditor.ss']['FORMHEADINGTITLE'] = 'Tambah kepala tajuk borang';
$lang['ms_MY']['FieldEditor.ss']['MEMBER'] = 'Senarai Ahli';
$lang['ms_MY']['FieldEditor.ss']['MEMBERTITLE'] = 'Tambah medan senarai ahli';
$lang['ms_MY']['FieldEditor.ss']['RADIOSET'] = 'Radio';
$lang['ms_MY']['FieldEditor.ss']['RADIOSETTITLE'] = 'Tambah set butang radio';
$lang['ms_MY']['FieldEditor.ss']['TEXT'] = 'Teks';
$lang['ms_MY']['FieldEditor.ss']['TEXTTITLE'] = 'Tambah medan teks';
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