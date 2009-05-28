<?php

/**
 * Arabic (Saudi Arabia) language pack
 * @package modules: userforms
 * @subpackage i18n
 */

i18n::include_locale_file('modules: userforms', 'en_US');

global $lang;

if(array_key_exists('ar_SA', $lang) && is_array($lang['ar_SA'])) {
	$lang['ar_SA'] = array_merge($lang['en_US'], $lang['ar_SA']);
} else {
	$lang['ar_SA'] = $lang['en_US'];
}

$lang['ar_SA']['EditableCheckbox']['ANY'] = 'أي';
$lang['ar_SA']['EditableCheckbox']['NOTSELECTED'] = 'غير المختارة';
$lang['ar_SA']['EditableCheckbox']['SELECTED'] = 'المختارة';
$lang['ar_SA']['EditableCheckbox.ss']['CHECKBOX'] = 'حقل Checkbox';
$lang['ar_SA']['EditableCheckbox.ss']['DELETE'] = 'حذف هذا الحقل';
$lang['ar_SA']['EditableCheckbox.ss']['DRAG'] = 'أعد ترتيب الحقول عن طريق خاصية السحب و الإفلات';
$lang['ar_SA']['EditableCheckbox.ss']['LOCKED'] = 'هذا الحقل لايمكن تعديله';
$lang['ar_SA']['EditableCheckbox.ss']['MORE'] = 'خيارات إضافية';
$lang['ar_SA']['EditableCheckboxGroupField.ss']['ADD'] = 'إضافة خيار جديد';
$lang['ar_SA']['EditableCheckboxGroupField.ss']['CHECKBOXGROUP'] = 'مجموعة Checkbox';
$lang['ar_SA']['EditableCheckboxGroupField.ss']['DELETE'] = 'حذف هذا الحقل';
$lang['ar_SA']['EditableCheckboxGroupField.ss']['DRAG'] = 'أعد ترتيب الحقول عن طريق خاصية السحب و الإفلات';
$lang['ar_SA']['EditableCheckboxGroupField.ss']['LOCKED'] = 'هذه الحقول لا يمكن تعديلها';
$lang['ar_SA']['EditableCheckboxGroupField.ss']['MORE'] = 'خيارات إضافية';
$lang['ar_SA']['EditableCheckboxGroupField.ss']['REQUIRED'] = 'هذا الحقل ضروري لهذا النموذج و لا يمكن حذفه';
$lang['ar_SA']['EditableCheckboxOption.ss']['DELETE'] = 'إزالة هذا الخيار';
$lang['ar_SA']['EditableCheckboxOption.ss']['DRAG'] = 'أعد ترتيب خيارات الطلب بواسطة خاصية السحب و الإفلات';
$lang['ar_SA']['EditableCheckboxOption.ss']['LOCKED'] = 'هذه الحقول لايمكن تعديلها';
$lang['ar_SA']['EditableDateField.ss']['DATE'] = 'حقل التاريخ';
$lang['ar_SA']['EditableDateField.ss']['DELETE'] = 'حذف هذا الحقل';
$lang['ar_SA']['EditableDateField.ss']['DRAG'] = 'أعد ترتيب الحقول بواسة خاصية السحب و الإفلات';
$lang['ar_SA']['EditableDateField.ss']['MORE'] = 'خيارات إضافية';
$lang['ar_SA']['EditableDropdown.ss']['ADD'] = 'إضافة خيار جديد';
$lang['ar_SA']['EditableDropdown.ss']['DELETE'] = 'حذف هذا الحقل';
$lang['ar_SA']['EditableDropdown.ss']['DRAG'] = 'أعد ترتيب الحقول بواسة خاضصية السحب و الإفلات';
$lang['ar_SA']['EditableDropdown.ss']['DROPDOWN'] = 'صندق القائمة المنسدلة';
$lang['ar_SA']['EditableDropdown.ss']['LOCKED'] = 'هذه الحقول لا يمكن تعديلها';
$lang['ar_SA']['EditableDropdown.ss']['MORE'] = 'خيارات إضافية';
$lang['ar_SA']['EditableDropdown.ss']['REQUIRED'] = 'هذا الحقل مطلوب لهذا النموذج و لا يمكن حذفه';
$lang['ar_SA']['EditableDropdownOption.ss']['DELETE'] = 'حذف هذا الخيار';
$lang['ar_SA']['EditableDropdownOption.ss']['DRAG'] = 'أعد ترتيب الخيارات بواسطة خاصية السحب و الإفلات';
$lang['ar_SA']['EditableDropdownOption.ss']['LOCKED'] = 'هذه الحقول لايمكن تعديلها';
$lang['ar_SA']['EditableEmailField']['SENDCOPY'] = 'أرسل نسخة من الملف المرسل إلى هذا العنوان';
$lang['ar_SA']['EditableEmailField.ss']['DELETE'] = 'حذف هذا الحقل';
$lang['ar_SA']['EditableEmailField.ss']['DRAG'] = 'أعد ترتيب الحقول بواسطة خاصية السحب و الإفلات';
$lang['ar_SA']['EditableEmailField.ss']['EMAIL'] = 'حقل عنوان البريد الإلكتروني';
$lang['ar_SA']['EditableEmailField.ss']['MORE'] = 'خيارات إضافية';
$lang['ar_SA']['EditableEmailField.ss']['REQUIRED'] = 'هذا الحقل مطلوب لهذا النموذج و لا يمكن حذفه';
$lang['ar_SA']['EditableFileField.ss']['DELETE'] = 'حذف هذا الحقل';
$lang['ar_SA']['EditableFileField.ss']['DRAG'] = 'أعد ترتيب الحقول بواسطة خاصية السحب و الإفلات';
$lang['ar_SA']['EditableFileField.ss']['FILE'] = 'حقل رفع الملفات';
$lang['ar_SA']['EditableFileField.ss']['MORE'] = 'خيارات إضافية';
$lang['ar_SA']['EditableFormField']['ENTERQUESTION'] = 'أدخل سؤال';
$lang['ar_SA']['EditableFormField']['REQUIRED'] = 'مطلوب؟';
$lang['ar_SA']['EditableFormField.ss']['DELETE'] = 'حذف هذا الحقل';
$lang['ar_SA']['EditableFormField.ss']['DRAG'] = 'أعد ترتيب الحقول بواسطة خاصية السحب و الإفلات';
$lang['ar_SA']['EditableFormField.ss']['LOCKED'] = 'هذه الحقول لايمكن تعديلها';
$lang['ar_SA']['EditableFormField.ss']['MORE'] = 'خيارات أخرى';
$lang['ar_SA']['EditableFormField.ss']['REQUIRED'] = 'هذا الحقل مطلوب لهذا النموذج ولا يمكن حذفه';
$lang['ar_SA']['EditableFormFieldOption.ss']['DELETE'] = 'حذف هذا الخيار';
$lang['ar_SA']['EditableFormFieldOption.ss']['DRAG'] = 'أعد ترتيب الحقول بواسطة خاصية السحب و الإفلات';
$lang['ar_SA']['EditableFormFieldOption.ss']['LOCKED'] = 'هذه الحقول لايمكن تعديلها';
$lang['ar_SA']['EditableFormHeading.ss']['DELETE'] = 'حذف هذا الحقل';
$lang['ar_SA']['EditableFormHeading.ss']['DRAG'] = 'أعد ترتيب الحقول بواسطة خاصية السحب و الإفلات';
$lang['ar_SA']['EditableFormHeading.ss']['HEADING'] = 'حقل العنوان';
$lang['ar_SA']['EditableFormHeading.ss']['MORE'] = 'خيارات إضافية';
$lang['ar_SA']['EditableRadioField.ss']['ADD'] = 'إضافة خيار جديد';
$lang['ar_SA']['EditableRadioField.ss']['DELETE'] = 'حذف هذا الحقل';
$lang['ar_SA']['EditableRadioField.ss']['DRAG'] = 'أعد ترتيب الحقول بواسطة خاصية السحب و الإفلات';
$lang['ar_SA']['EditableRadioField.ss']['LOCKED'] = 'هذه الحقول لايمكن تعديلها';
$lang['ar_SA']['EditableRadioField.ss']['MORE'] = 'خيارات إضافية';
$lang['ar_SA']['EditableRadioField.ss']['REQUIRED'] = 'هذا الحقل مطلوب لهذا النموذج و لا يمكن حذفه';
$lang['ar_SA']['EditableRadioField.ss']['SET'] = 'مجموعة أزرار الاختيار Radio';
$lang['ar_SA']['EditableRadioOption.ss']['DELETE'] = 'إلغاء هذا الخيار';
$lang['ar_SA']['EditableRadioOption.ss']['DRAG'] = 'أعد ترتيب الخيارات بواسطة خاصية السحب و الإفلات';
$lang['ar_SA']['EditableRadioOption.ss']['LOCKED'] = 'هذه الحقول لا يمكن تعديلها';
$lang['ar_SA']['EditableTextField']['DEFAULTTEXT'] = 'النص الافتراضي';
$lang['ar_SA']['EditableTextField']['NUMBERROWS'] = 'عدد الصفوف';
$lang['ar_SA']['EditableTextField.ss']['DELETE'] = 'حذف هذا الحقل';
$lang['ar_SA']['EditableTextField.ss']['DRAG'] = 'أعد ترتيب الحقول بواسطة خاصية السحب و الإفلات';
$lang['ar_SA']['EditableTextField.ss']['MORE'] = 'خيارات إضافية';
$lang['ar_SA']['EditableTextField.ss']['TEXTFIELD'] = 'حقل النص';
$lang['ar_SA']['EditableTextField']['TEXTBOXLENGTH'] = 'طول حقل النص';
$lang['ar_SA']['EditableTextField']['TEXTLENGTH'] = 'طول النص';
$lang['ar_SA']['FieldEditor']['EMAILONSUBMIT'] = 'أرسل النموذج إلى البريد التالي';
$lang['ar_SA']['FieldEditor']['EMAILSUBMISSION'] = 'أرسل الملف إلى البريد التالي :';
$lang['ar_SA']['FieldEditor.ss']['ADD'] = 'إضافة';
$lang['ar_SA']['FieldEditor.ss']['CHECKBOX'] = 'Checkbox';
$lang['ar_SA']['FieldEditor.ss']['CHECKBOXGROUP'] = 'Checkboxes';
$lang['ar_SA']['FieldEditor.ss']['CHECKBOXGROUPTITLE'] = 'إضافة حقل مجموعة Checkbox';
$lang['ar_SA']['FieldEditor.ss']['CHECKBOXTITLE'] = 'إضافة Checkbox';
$lang['ar_SA']['FieldEditor.ss']['DATE'] = 'التاريخ';
$lang['ar_SA']['FieldEditor.ss']['DATETITLE'] = 'إضافة عنوان التاريخ';
$lang['ar_SA']['FieldEditor.ss']['DROPDOWN'] = 'قائمة منسدلة';
$lang['ar_SA']['FieldEditor.ss']['DROPDOWNTITLE'] = 'إضافة قائمة منسدلة';
$lang['ar_SA']['FieldEditor.ss']['EMAIL'] = 'البريد الإلكتروني';
$lang['ar_SA']['FieldEditor.ss']['EMAILTITLE'] = 'إضافة حقل البريد الإلكتروني';
$lang['ar_SA']['FieldEditor.ss']['FILE'] = 'ملف';
$lang['ar_SA']['FieldEditor.ss']['FILETITLE'] = 'إضافة حقل ارفع الملفات';
$lang['ar_SA']['FieldEditor.ss']['FORMHEADING'] = 'العنوان';
$lang['ar_SA']['FieldEditor.ss']['FORMHEADINGTITLE'] = 'إضافة ';
$lang['ar_SA']['FieldEditor.ss']['MEMBER'] = 'قائمة الأعضاء';
$lang['ar_SA']['FieldEditor.ss']['MEMBERTITLE'] = 'إضافة حقل قائمة الأعضاء';
$lang['ar_SA']['FieldEditor.ss']['RADIOSET'] = 'Radio button';
$lang['ar_SA']['FieldEditor.ss']['RADIOSETTITLE'] = 'إضافة  مجموعة أزرار الاختيار radio ';
$lang['ar_SA']['FieldEditor.ss']['TEXT'] = 'نص';
$lang['ar_SA']['FieldEditor.ss']['TEXTTITLE'] = 'إضافة حقل النص';
$lang['ar_SA']['SubmittedFormEmail.ss']['SUBMITTED'] = 'البيانات تم إرسالها إلى موقعك';
$lang['ar_SA']['SubmittedFormReportField.ss']['SUBMITTED'] = 'تم إرساله في';
$lang['ar_SA']['UserDefinedForm']['FORM'] = 'النموذج';
$lang['ar_SA']['UserDefinedForm']['NORESULTS'] = 'لايوجد نتائج للبحث';
$lang['ar_SA']['UserDefinedForm']['ONCOMPLETE'] = 'تم اكتمال الطلب';
$lang['ar_SA']['UserDefinedForm']['ONCOMPLETELABEL'] = 'عرض الطلبات المكتملة';
$lang['ar_SA']['UserDefinedForm']['RECEIVED'] = 'الملفات المستقبلة';
$lang['ar_SA']['UserDefinedForm']['SUBMISSIONS'] = 'الملفات المرسلة';
$lang['ar_SA']['UserDefinedForm']['SUBMIT'] = 'تسليم';
$lang['ar_SA']['UserDefinedForm']['TEXTONSUBMIT'] = 'النص ال';
$lang['ar_SA']['UserDefinedForm_SubmittedFormEmail']['EMAILSUBJECT'] = 'الطلبات المرسلة للنماذج';

?>