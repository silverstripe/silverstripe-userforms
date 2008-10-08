<?php

/**
 * Spanish (Spain) language pack
 * @package modules: userforms
 * @subpackage i18n
 */

i18n::include_locale_file('modules: userforms', 'en_US');

global $lang;

if(array_key_exists('es_ES', $lang) && is_array($lang['es_ES'])) {
	$lang['es_ES'] = array_merge($lang['en_US'], $lang['es_ES']);
} else {
	$lang['es_ES'] = $lang['en_US'];
}

$lang['es_ES']['EditableCheckbox']['ANY'] = 'Cualquiera';
$lang['es_ES']['EditableCheckbox']['NOTSELECTED'] = 'No selecionado';
$lang['es_ES']['EditableCheckbox']['SELECTED'] = 'Seleccionado';
$lang['es_ES']['EditableCheckbox.ss']['DELETE'] = 'Borrar este campo';
$lang['es_ES']['EditableCheckbox.ss']['LOCKED'] = 'Este campo no puede ser modificado';
$lang['es_ES']['EditableCheckbox.ss']['MORE'] = 'Más opciones';
$lang['es_ES']['EditableCheckboxGroupField.ss']['ADD'] = 'Añadir opción nueva';
$lang['es_ES']['EditableCheckboxGroupField.ss']['DELETE'] = 'Borrar este campo';
$lang['es_ES']['EditableCheckboxGroupField.ss']['LOCKED'] = 'Estos campos no pueden ser modificados';
$lang['es_ES']['EditableCheckboxGroupField.ss']['MORE'] = 'Más opciones';
$lang['es_ES']['EditableCheckboxGroupField.ss']['REQUIRED'] = 'Este campo es requerido para este formulario y no puede ser borrado';
$lang['es_ES']['EditableCheckboxOption.ss']['DELETE'] = 'Eliminar esta opción';
$lang['es_ES']['EditableCheckboxOption.ss']['LOCKED'] = 'Estos campos no pueden ser modificados';
$lang['es_ES']['EditableDateField.ss']['DATE'] = 'Campo Fecha';
$lang['es_ES']['EditableDateField.ss']['DELETE'] = 'Borrar este campo';
$lang['es_ES']['EditableDateField.ss']['MORE'] = 'Más opciones';
$lang['es_ES']['EditableDropdown.ss']['ADD'] = 'Añadir nueva opción';
$lang['es_ES']['EditableDropdown.ss']['DELETE'] = 'Borrar este campo';
$lang['es_ES']['EditableDropdown.ss']['LOCKED'] = 'Estos campos no pueden ser modificados';
$lang['es_ES']['EditableDropdown.ss']['MORE'] = 'Más opciones';
$lang['es_ES']['EditableDropdown.ss']['REQUIRED'] = 'Este campo es necesario para este formulario y no puede ser borrado';
$lang['es_ES']['EditableDropdownOption.ss']['DELETE'] = 'Quitar esta opción';
$lang['es_ES']['EditableDropdownOption.ss']['LOCKED'] = 'Estos campos no pueden ser modificados';
$lang['es_ES']['EditableEmailField']['SENDCOPY'] = 'Enviar una copia de este envío a esta dirección';
$lang['es_ES']['EditableEmailField.ss']['DELETE'] = 'Borrar este campo';
$lang['es_ES']['EditableEmailField.ss']['MORE'] = 'Más opciones';
$lang['es_ES']['EditableEmailField.ss']['REQUIRED'] = 'Este campo es necesario para este formulario y no puede ser borrado';
$lang['es_ES']['EditableFileField.ss']['DELETE'] = 'Borrar este campo';
$lang['es_ES']['EditableFileField.ss']['MORE'] = 'Más opciones';
$lang['es_ES']['EditableFormField']['ENTERQUESTION'] = 'IntroducirPregunta';
$lang['es_ES']['EditableFormField']['REQUIRED'] = 'Obligatorio?';
$lang['es_ES']['EditableFormField.ss']['DELETE'] = 'Borrar este campo';
$lang['es_ES']['EditableFormField.ss']['DRAG'] = 'Arrastre para reajustar el orden de los campos';
$lang['es_ES']['EditableFormField.ss']['LOCKED'] = 'Estos campos no pueden ser modificados';
$lang['es_ES']['EditableFormField.ss']['MORE'] = 'Más opciones';
$lang['es_ES']['EditableFormField.ss']['REQUIRED'] = 'Este campo es necesario para este formulario y no puede ser borrado';
$lang['es_ES']['EditableFormFieldOption.ss']['DELETE'] = 'Quitar esta opción';
$lang['es_ES']['EditableFormFieldOption.ss']['LOCKED'] = 'Estos campos no pueden ser modificados';
$lang['es_ES']['EditableFormHeading.ss']['DELETE'] = 'Borrar este campo';
$lang['es_ES']['EditableFormHeading.ss']['MORE'] = 'Más opciones';
$lang['es_ES']['EditableRadioField.ss']['ADD'] = 'Añadir nueva opción';
$lang['es_ES']['EditableRadioField.ss']['DELETE'] = 'Borrar este campo';
$lang['es_ES']['EditableRadioField.ss']['LOCKED'] = 'Estos campos no pueden ser modificados';
$lang['es_ES']['EditableRadioField.ss']['MORE'] = 'Más opciones';
$lang['es_ES']['EditableRadioField.ss']['REQUIRED'] = 'Este campo es necesario para este formulario y no puede ser borrado';
$lang['es_ES']['EditableRadioOption.ss']['DELETE'] = 'Quitar esta opción';
$lang['es_ES']['EditableRadioOption.ss']['LOCKED'] = 'Estos campos no pueden ser modificados';
$lang['es_ES']['EditableTextField']['DEFAULTTEXT'] = 'Texto por defecto';
$lang['es_ES']['EditableTextField']['NUMBERROWS'] = 'Número de filas';
$lang['es_ES']['EditableTextField.ss']['DELETE'] = 'Borrar este campo';
$lang['es_ES']['EditableTextField.ss']['MORE'] = 'Más opciones';
$lang['es_ES']['EditableTextField.ss']['TEXTFIELD'] = 'Campo de Texto';
$lang['es_ES']['EditableTextField']['TEXTBOXLENGTH'] = 'Longitud de la caja de texto';
$lang['es_ES']['EditableTextField']['TEXTLENGTH'] = 'Longitud del texto';
$lang['es_ES']['FieldEditor']['EMAILONSUBMIT'] = 'Enviar formulario por email :';
$lang['es_ES']['FieldEditor']['EMAILSUBMISSION'] = 'Enviar por email a:';
$lang['es_ES']['FieldEditor.ss']['ADD'] = 'Añadir';
$lang['es_ES']['FieldEditor.ss']['DATE'] = 'Fecha';
$lang['es_ES']['FieldEditor.ss']['FILE'] = 'Archivo';
$lang['es_ES']['FieldEditor.ss']['MEMBER'] = 'Lista de usuarios';
$lang['es_ES']['FieldEditor.ss']['RADIOSET'] = 'Radio';
$lang['es_ES']['FieldEditor.ss']['TEXT'] = 'Texto';
$lang['es_ES']['FieldEditor.ss']['TEXTTITLE'] = 'Añadir campo de texto';
$lang['es_ES']['SubmittedFormEmail.ss']['SUBMITTED'] = 'Los siguientes datos fueron enviados a su sitio web:';
$lang['es_ES']['UserDefinedForm']['FORM'] = 'Formulario';
$lang['es_ES']['UserDefinedForm']['NORESULTS'] = 'No se han encontrado coincidencias';
$lang['es_ES']['UserDefinedForm']['ONCOMPLETE'] = 'Completado';
$lang['es_ES']['UserDefinedForm']['ONCOMPLETELABEL'] = 'Mostrar al terminar';
$lang['es_ES']['UserDefinedForm']['RECEIVED'] = 'Propuestas recibidas';
$lang['es_ES']['UserDefinedForm']['SUBMISSIONS'] = 'Propuestas';
$lang['es_ES']['UserDefinedForm']['SUBMIT'] = 'Aceptar';
$lang['es_ES']['UserDefinedForm']['TEXTONSUBMIT'] = 'Texto en el botón aceptar/submit:';
$lang['es_ES']['UserDefinedForm_SubmittedFormEmail']['EMAILSUBJECT'] = 'Propuesta de formulario';

?>