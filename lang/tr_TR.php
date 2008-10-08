<?php

/**
 * Turkish (Turkey) language pack
 * @package modules: userforms
 * @subpackage i18n
 */

i18n::include_locale_file('modules: userforms', 'en_US');

global $lang;

if(array_key_exists('tr_TR', $lang) && is_array($lang['tr_TR'])) {
	$lang['tr_TR'] = array_merge($lang['en_US'], $lang['tr_TR']);
} else {
	$lang['tr_TR'] = $lang['en_US'];
}

$lang['tr_TR']['EditableCheckbox']['ANY'] = 'Herhangi';
$lang['tr_TR']['EditableCheckbox']['NOTSELECTED'] = 'Seçili değil';
$lang['tr_TR']['EditableCheckbox']['SELECTED'] = 'Seçili';
$lang['tr_TR']['EditableCheckbox.ss']['CHECKBOX'] = 'Seçim kutusu alani';
$lang['tr_TR']['EditableCheckbox.ss']['DELETE'] = 'Bu alanı sil';
$lang['tr_TR']['EditableCheckbox.ss']['DRAG'] = 'Alanları yeniden sıralamak için sürükleyiniz';
$lang['tr_TR']['EditableCheckbox.ss']['LOCKED'] = 'Bu alan degiştirilemez';
$lang['tr_TR']['EditableCheckbox.ss']['MORE'] = 'Diğer seçenekler';
$lang['tr_TR']['EditableCheckboxGroupField.ss']['ADD'] = 'Yeni seçenek ekle';
$lang['tr_TR']['EditableCheckboxGroupField.ss']['CHECKBOXGROUP'] = 'Seçim kutusu gurubu';
$lang['tr_TR']['EditableCheckboxGroupField.ss']['DELETE'] = 'Bu alanı sil';
$lang['tr_TR']['EditableCheckboxGroupField.ss']['DRAG'] = 'Alanlari yeniden sıralamak için sürükleyin';
$lang['tr_TR']['EditableCheckboxGroupField.ss']['LOCKED'] = 'Bu alanlar değiştirilemez';
$lang['tr_TR']['EditableCheckboxGroupField.ss']['MORE'] = 'Diğer seçenekler';
$lang['tr_TR']['EditableCheckboxGroupField.ss']['REQUIRED'] = 'Bu alan bu form için gereklidir, silinemez';
$lang['tr_TR']['EditableCheckboxOption.ss']['DELETE'] = 'Bu opsiyon kutusunu sil';
$lang['tr_TR']['EditableCheckboxOption.ss']['DRAG'] = 'Opsiyon kutularını yeniden sıralamak için sürükleyin';
$lang['tr_TR']['EditableCheckboxOption.ss']['LOCKED'] = 'Bu alanlar değiştirilemez';
$lang['tr_TR']['EditableDateField.ss']['DATE'] = 'Tarih Alanı';
$lang['tr_TR']['EditableDateField.ss']['DELETE'] = 'Bu alanı sil';
$lang['tr_TR']['EditableDateField.ss']['DRAG'] = 'Alanlari yeniden sıralamak için sürükleyin';
$lang['tr_TR']['EditableDateField.ss']['MORE'] = 'Diğer seçenekler';
$lang['tr_TR']['EditableDropdown.ss']['ADD'] = 'Yeni seçenek ekle';
$lang['tr_TR']['EditableDropdown.ss']['DELETE'] = 'Bu alanı sil';
$lang['tr_TR']['EditableDropdown.ss']['DRAG'] = 'Alanlari yeniden sıralamak için sürükleyin';
$lang['tr_TR']['EditableDropdown.ss']['DROPDOWN'] = 'Düşen kutu';
$lang['tr_TR']['EditableDropdown.ss']['LOCKED'] = 'Bu alanlar değiştirilemez';
$lang['tr_TR']['EditableDropdown.ss']['MORE'] = 'Diğer seçenekler';
$lang['tr_TR']['EditableDropdown.ss']['REQUIRED'] = 'Bu alan bu form için gereklidir, silinemez';
$lang['tr_TR']['EditableDropdownOption.ss']['DELETE'] = 'Bu seçeneği sil';
$lang['tr_TR']['EditableDropdownOption.ss']['DRAG'] = 'Alanlari yeniden sıralamak için sürükleyin';
$lang['tr_TR']['EditableDropdownOption.ss']['LOCKED'] = 'Bu alanlar değiştirilemez';
$lang['tr_TR']['EditableEmailField']['SENDCOPY'] = 'Bu adrese gönderi nin bir kopyasini postala';
$lang['tr_TR']['EditableEmailField.ss']['DELETE'] = 'Bu alanı sil';
$lang['tr_TR']['EditableEmailField.ss']['DRAG'] = 'Alanlari yeniden sıralamak için sürükleyin';
$lang['tr_TR']['EditableEmailField.ss']['EMAIL'] = 'E-posta adresi alanı';
$lang['tr_TR']['EditableEmailField.ss']['MORE'] = 'Diğer seçenekler';
$lang['tr_TR']['EditableEmailField.ss']['REQUIRED'] = 'Bu alan bu form için gereklidir, silinemez';
$lang['tr_TR']['EditableFileField.ss']['DELETE'] = 'Bu alanı sil';
$lang['tr_TR']['EditableFileField.ss']['DRAG'] = 'Alanlari yeniden sıralamak için sürükleyin';
$lang['tr_TR']['EditableFileField.ss']['FILE'] = 'Dosya yükleme(upload) alanı';
$lang['tr_TR']['EditableFileField.ss']['MORE'] = 'Diğer seçenekler';
$lang['tr_TR']['EditableFormField']['ENTERQUESTION'] = 'Soruyu Gir';
$lang['tr_TR']['EditableFormField']['REQUIRED'] = 'Gerekli mi?';
$lang['tr_TR']['EditableFormField.ss']['DELETE'] = 'Bu alanı sil';
$lang['tr_TR']['EditableFormField.ss']['DRAG'] = 'Alanlari yeniden sıralamak için sürükleyin';
$lang['tr_TR']['EditableFormField.ss']['LOCKED'] = 'Bu alanlar değiştirilemez';
$lang['tr_TR']['EditableFormField.ss']['MORE'] = 'Diğer seçenekler';
$lang['tr_TR']['EditableFormField.ss']['REQUIRED'] = 'Bu alan bu form için gereklidir, silinemez';
$lang['tr_TR']['EditableFormFieldOption.ss']['DELETE'] = 'Bu seçeneği sil';
$lang['tr_TR']['EditableFormFieldOption.ss']['DRAG'] = 'Alanlari yeniden sıralamak için sürükleyin';
$lang['tr_TR']['EditableFormFieldOption.ss']['LOCKED'] = 'Bu alanlar değiştirilemez';
$lang['tr_TR']['EditableFormHeading.ss']['DELETE'] = 'Bu alanı sil';
$lang['tr_TR']['EditableFormHeading.ss']['DRAG'] = 'Alanlari yeniden sıralamak için sürükleyin';
$lang['tr_TR']['EditableFormHeading.ss']['HEADING'] = 'Başlık alanı';
$lang['tr_TR']['EditableFormHeading.ss']['MORE'] = 'Diğer seçenekler';
$lang['tr_TR']['EditableRadioField.ss']['ADD'] = 'Yeni seçenek ekle';
$lang['tr_TR']['EditableRadioField.ss']['DELETE'] = 'Bu alani sil';
$lang['tr_TR']['EditableRadioField.ss']['DRAG'] = 'Alanlari yeniden sıralamak için sürükleyin';
$lang['tr_TR']['EditableRadioField.ss']['LOCKED'] = 'Bu alanlar değiştirilemez';
$lang['tr_TR']['EditableRadioField.ss']['MORE'] = 'Diger seçenekler';
$lang['tr_TR']['EditableRadioField.ss']['REQUIRED'] = 'Bu alan bu form için gereklidir, silinemez';
$lang['tr_TR']['EditableRadioField.ss']['SET'] = 'Radyo kutusu seti';
$lang['tr_TR']['EditableRadioOption.ss']['DELETE'] = 'Bu seçenegi sil';
$lang['tr_TR']['EditableRadioOption.ss']['DRAG'] = 'Seçenekleri yeniden sıralamak için sürükleyiniz';
$lang['tr_TR']['EditableRadioOption.ss']['LOCKED'] = 'Bu alanlar degiştirilemez';
$lang['tr_TR']['EditableTextField']['DEFAULTTEXT'] = 'Varsayılan Text';
$lang['tr_TR']['EditableTextField']['NUMBERROWS'] = 'Satır adedi';
$lang['tr_TR']['EditableTextField.ss']['DELETE'] = 'Bu alanı sil';
$lang['tr_TR']['EditableTextField.ss']['DRAG'] = 'Alanları yeniden sıralamak için sürükleyiniz';
$lang['tr_TR']['EditableTextField.ss']['MORE'] = 'Diğer seçenekler';
$lang['tr_TR']['EditableTextField.ss']['TEXTFIELD'] = 'Text Alanı';
$lang['tr_TR']['EditableTextField']['TEXTBOXLENGTH'] = 'Text kutusunun uzunluğu';
$lang['tr_TR']['EditableTextField']['TEXTLENGTH'] = 'Text uzunluğu';
$lang['tr_TR']['FieldEditor']['EMAILONSUBMIT'] = 'Form gönderiminde E-posta at';
$lang['tr_TR']['FieldEditor']['EMAILSUBMISSION'] = 'Gönderi nin bir kopyasini postala:';
$lang['tr_TR']['FieldEditor.ss']['ADD'] = 'Ekle';
$lang['tr_TR']['FieldEditor.ss']['CHECKBOX'] = 'Checkbox';
$lang['tr_TR']['FieldEditor.ss']['CHECKBOXGROUP'] = 'Checkbox lar';
$lang['tr_TR']['FieldEditor.ss']['CHECKBOXGROUPTITLE'] = 'Checkbox grubu alanı ekle';
$lang['tr_TR']['FieldEditor.ss']['CHECKBOXTITLE'] = 'Checkbox ekle';
$lang['tr_TR']['FieldEditor.ss']['DATE'] = 'Tarih';
$lang['tr_TR']['FieldEditor.ss']['DATETITLE'] = 'Tarih başlığı ekle';
$lang['tr_TR']['FieldEditor.ss']['DROPDOWN'] = 'Dropdown';
$lang['tr_TR']['FieldEditor.ss']['DROPDOWNTITLE'] = 'Dropdown Ekle';
$lang['tr_TR']['FieldEditor.ss']['EMAIL'] = 'E-posta';
$lang['tr_TR']['FieldEditor.ss']['EMAILTITLE'] = 'E-posta alani ekle';
$lang['tr_TR']['FieldEditor.ss']['FILE'] = 'Dosya';
$lang['tr_TR']['FieldEditor.ss']['FILETITLE'] = 'Dosya yükleme alanı ekle';
$lang['tr_TR']['FieldEditor.ss']['FORMHEADING'] = 'Başlık';
$lang['tr_TR']['FieldEditor.ss']['FORMHEADINGTITLE'] = 'Form başlığı ekle';
$lang['tr_TR']['FieldEditor.ss']['MEMBER'] = 'Üye Listesi';
$lang['tr_TR']['FieldEditor.ss']['MEMBERTITLE'] = 'Üye listesi alanı ekle';
$lang['tr_TR']['FieldEditor.ss']['RADIOSET'] = 'Radio';
$lang['tr_TR']['FieldEditor.ss']['RADIOSETTITLE'] = 'Radio button grubu ekle';
$lang['tr_TR']['FieldEditor.ss']['TEXT'] = 'Text';
$lang['tr_TR']['FieldEditor.ss']['TEXTTITLE'] = 'Text alanı ekle';
$lang['tr_TR']['SubmittedFormEmail.ss']['SUBMITTED'] = 'Bu içerik, websitesine yollanmıştır:';
$lang['tr_TR']['SubmittedFormReportField.ss']['SUBMITTED'] = 'Gönderilme zamanı';
$lang['tr_TR']['UserDefinedForm']['FORM'] = 'Form';
$lang['tr_TR']['UserDefinedForm']['NORESULTS'] = 'Herhangi bir sonuç bulunamadı';
$lang['tr_TR']['UserDefinedForm']['ONCOMPLETE'] = 'Tamamlanınca';
$lang['tr_TR']['UserDefinedForm']['ONCOMPLETELABEL'] = 'Tamamlanınca görüntüle';
$lang['tr_TR']['UserDefinedForm']['RECEIVED'] = 'Alınan Gönderiler';
$lang['tr_TR']['UserDefinedForm']['SUBMISSIONS'] = 'Gönderiler';
$lang['tr_TR']['UserDefinedForm']['SUBMIT'] = 'Gönder';
$lang['tr_TR']['UserDefinedForm']['TEXTONSUBMIT'] = 'Gönder düğmesi üzerindeki yazı:';
$lang['tr_TR']['UserDefinedForm_SubmittedFormEmail']['EMAILSUBJECT'] = 'Form gönderimi';

?>