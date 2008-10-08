<?php

/**
 * Spanish (Mexico) language pack
 * @package modules: userforms
 * @subpackage i18n
 */

i18n::include_locale_file('modules: userforms', 'en_US');

global $lang;

if(array_key_exists('es_MX', $lang) && is_array($lang['es_MX'])) {
	$lang['es_MX'] = array_merge($lang['en_US'], $lang['es_MX']);
} else {
	$lang['es_MX'] = $lang['en_US'];
}

$lang['es_MX']['EditableCheckbox']['ANY'] = 'Cualquiera';
$lang['es_MX']['EditableCheckbox']['NOTSELECTED'] = 'No seleccionado';
$lang['es_MX']['EditableCheckbox']['SELECTED'] = 'Seleccionado';
$lang['es_MX']['EditableCheckbox.ss']['CHECKBOX'] = 'Campo Checkbox';
$lang['es_MX']['EditableCheckbox.ss']['DELETE'] = 'Eliminar este campo';
$lang['es_MX']['EditableCheckbox.ss']['DRAG'] = 'Arrastra para reajustar el orden de los campos';
$lang['es_MX']['EditableCheckbox.ss']['LOCKED'] = 'No se puede modificar éste campo';
$lang['es_MX']['EditableCheckbox.ss']['MORE'] = 'Más opciones';
$lang['es_MX']['EditableCheckboxGroupField.ss']['ADD'] = 'Agregar una nueva opción';
$lang['es_MX']['EditableCheckboxGroupField.ss']['CHECKBOXGROUP'] = 'Grupo de cajas de verificación';
$lang['es_MX']['EditableCheckboxGroupField.ss']['DELETE'] = 'Eliminar este campo';
$lang['es_MX']['EditableCheckboxGroupField.ss']['DRAG'] = 'Arrastra para reajustar el orden de los campos';
$lang['es_MX']['EditableCheckboxGroupField.ss']['LOCKED'] = 'Estos campos no se pueden modificar';
$lang['es_MX']['EditableCheckboxGroupField.ss']['MORE'] = 'Más opciones';
$lang['es_MX']['EditableCheckboxGroupField.ss']['REQUIRED'] = 'Este campo es obligatorio para este formulario y no se puede eliminar';
$lang['es_MX']['EditableCheckboxOption.ss']['DELETE'] = 'Remover esta opción';
$lang['es_MX']['EditableCheckboxOption.ss']['DRAG'] = 'Arrastra para cambiar el orden de las opciones';
$lang['es_MX']['EditableCheckboxOption.ss']['LOCKED'] = 'No se pueden modificar estos campos';
$lang['es_MX']['EditableDateField.ss']['DATE'] = 'Campo Fecha';
$lang['es_MX']['EditableDateField.ss']['DELETE'] = 'Eliminar este campo';
$lang['es_MX']['EditableDateField.ss']['DRAG'] = 'Arrastra para cambiar el orden de los campos';
$lang['es_MX']['EditableDateField.ss']['MORE'] = 'Más opciones';
$lang['es_MX']['EditableDropdown.ss']['ADD'] = 'Agregar una nueva opción';
$lang['es_MX']['EditableDropdown.ss']['DELETE'] = 'Eliminar este campo';
$lang['es_MX']['EditableDropdown.ss']['DRAG'] = 'Arrastra para cambiar el orden de los campos';
$lang['es_MX']['EditableDropdown.ss']['DROPDOWN'] = 'Caja Dropdown';
$lang['es_MX']['EditableDropdown.ss']['LOCKED'] = 'No se pueden modificar estos campos';
$lang['es_MX']['EditableDropdown.ss']['MORE'] = 'Más opciones';
$lang['es_MX']['EditableDropdown.ss']['REQUIRED'] = 'Los campos son obligatorios para este formulario y no se pueden eliminar';
$lang['es_MX']['EditableDropdownOption.ss']['DELETE'] = 'Remover esta opción';
$lang['es_MX']['EditableDropdownOption.ss']['DRAG'] = 'Arrastra para cambiar el orden de las opciones';
$lang['es_MX']['EditableDropdownOption.ss']['LOCKED'] = 'Estos campos no se pueden modificar';
$lang['es_MX']['EditableEmailField']['SENDCOPY'] = 'Enviar una copia de aceptación a ésta dirección';
$lang['es_MX']['EditableEmailField.ss']['DELETE'] = 'Eliminar este campo';
$lang['es_MX']['EditableEmailField.ss']['DRAG'] = 'Arrastra para cambiar el orden de los campos';
$lang['es_MX']['EditableEmailField.ss']['EMAIL'] = 'Campo de dirección de correo-e';
$lang['es_MX']['EditableEmailField.ss']['MORE'] = 'Más opciones';
$lang['es_MX']['EditableEmailField.ss']['REQUIRED'] = 'Este campo es obligatorio para este formulario y no se puede eliminar';
$lang['es_MX']['EditableFileField.ss']['DELETE'] = 'Eliminar este campo';
$lang['es_MX']['EditableFileField.ss']['DRAG'] = 'Arrastra para cambiar el orden de los campos';
$lang['es_MX']['EditableFileField.ss']['FILE'] = 'Campo para subir ficheros';
$lang['es_MX']['EditableFileField.ss']['MORE'] = 'Más opciones';
$lang['es_MX']['EditableFormField']['ENTERQUESTION'] = 'Ingresa Tu Pregunta';
$lang['es_MX']['EditableFormField']['REQUIRED'] = '¿Requerido?';
$lang['es_MX']['EditableFormField.ss']['DELETE'] = 'Eliminar este campo';
$lang['es_MX']['EditableFormField.ss']['DRAG'] = 'Arrastra para cambiar el orden de los campos';
$lang['es_MX']['EditableFormField.ss']['LOCKED'] = 'Estos campos no de pueden modificar';
$lang['es_MX']['EditableFormField.ss']['MORE'] = 'Más opciones';
$lang['es_MX']['EditableFormField.ss']['REQUIRED'] = 'Este campo es obligatorio para el formulario y no se puede eliminar';
$lang['es_MX']['EditableFormFieldOption.ss']['DELETE'] = 'Remueve esta opción';
$lang['es_MX']['EditableFormFieldOption.ss']['DRAG'] = 'Arrastra para cambiar el orden de los campos';
$lang['es_MX']['EditableFormFieldOption.ss']['LOCKED'] = 'Estos campos no se pueden modificar';
$lang['es_MX']['EditableFormHeading.ss']['DELETE'] = 'Eliminar este campo';
$lang['es_MX']['EditableFormHeading.ss']['DRAG'] = 'Arrastra para cambiar el orden de los campos';
$lang['es_MX']['EditableFormHeading.ss']['HEADING'] = 'Campo de Título';
$lang['es_MX']['EditableFormHeading.ss']['MORE'] = 'Más opciones';
$lang['es_MX']['EditableRadioField.ss']['ADD'] = 'Agregar una nueva opción';
$lang['es_MX']['EditableRadioField.ss']['DELETE'] = 'Eliminar este campo';
$lang['es_MX']['EditableRadioField.ss']['DRAG'] = 'Arrastra para cambiar el orden de los campos';
$lang['es_MX']['EditableRadioField.ss']['LOCKED'] = 'Estos campos no se pueden modificar';
$lang['es_MX']['EditableRadioField.ss']['MORE'] = 'Más opciones';
$lang['es_MX']['EditableRadioField.ss']['REQUIRED'] = 'Este campo es obligatorio para este formulario y no se puede eliminar';
$lang['es_MX']['EditableRadioField.ss']['SET'] = 'Conjunto de botones de Radio';
$lang['es_MX']['EditableRadioOption.ss']['DELETE'] = 'Remueve esta opción';
$lang['es_MX']['EditableRadioOption.ss']['DRAG'] = 'Arrastra para reorganizar las opciones';
$lang['es_MX']['EditableRadioOption.ss']['LOCKED'] = 'Estos campos no de pueden modificar';
$lang['es_MX']['EditableTextField']['DEFAULTTEXT'] = 'Texto predeterminado';
$lang['es_MX']['EditableTextField']['NUMBERROWS'] = 'Número de renglones';
$lang['es_MX']['EditableTextField.ss']['DELETE'] = 'Eliminar este campo';
$lang['es_MX']['EditableTextField.ss']['DRAG'] = 'Arrastra para reorganizar esto campos';
$lang['es_MX']['EditableTextField.ss']['MORE'] = 'Más opciones';
$lang['es_MX']['EditableTextField.ss']['TEXTFIELD'] = 'Campo de Texto';
$lang['es_MX']['EditableTextField']['TEXTBOXLENGTH'] = 'Longitud de la caja de texto';
$lang['es_MX']['EditableTextField']['TEXTLENGTH'] = 'Longitud del texto';
$lang['es_MX']['FieldEditor']['EMAILONSUBMIT'] = 'Enviar en forma de correo-e';
$lang['es_MX']['FieldEditor']['EMAILSUBMISSION'] = 'Enviar correo a:';
$lang['es_MX']['FieldEditor.ss']['ADD'] = 'Agregar';
$lang['es_MX']['FieldEditor.ss']['CHECKBOX'] = 'Checkbox';
$lang['es_MX']['FieldEditor.ss']['CHECKBOXGROUP'] = 'Checkboxes';
$lang['es_MX']['FieldEditor.ss']['CHECKBOXGROUPTITLE'] = 'Agregar grupo de campos checkbox';
$lang['es_MX']['FieldEditor.ss']['CHECKBOXTITLE'] = 'Agregar un checkbox';
$lang['es_MX']['FieldEditor.ss']['DATE'] = 'Fecha';
$lang['es_MX']['FieldEditor.ss']['DATETITLE'] = 'Agrega la fecha en el Título';
$lang['es_MX']['FieldEditor.ss']['DROPDOWN'] = 'Dropdown';
$lang['es_MX']['FieldEditor.ss']['DROPDOWNTITLE'] = 'Agregar un dropdown';
$lang['es_MX']['FieldEditor.ss']['EMAIL'] = 'Correo-e';
$lang['es_MX']['FieldEditor.ss']['EMAILTITLE'] = 'Agrega un campo de correo-e';
$lang['es_MX']['FieldEditor.ss']['FILE'] = 'Fichero';
$lang['es_MX']['FieldEditor.ss']['FILETITLE'] = 'Agrega un campo para envío de ficheros';
$lang['es_MX']['FieldEditor.ss']['FORMHEADING'] = 'Título';
$lang['es_MX']['FieldEditor.ss']['FORMHEADINGTITLE'] = 'Agrega el título del formulario';
$lang['es_MX']['FieldEditor.ss']['MEMBER'] = 'Lista de Miembros';
$lang['es_MX']['FieldEditor.ss']['MEMBERTITLE'] = 'Agrega un campo de listado de miembros';
$lang['es_MX']['FieldEditor.ss']['RADIOSET'] = 'Radio';
$lang['es_MX']['FieldEditor.ss']['RADIOSETTITLE'] = 'Agrega un conjunto de botones de radio';
$lang['es_MX']['FieldEditor.ss']['TEXT'] = 'Texto';
$lang['es_MX']['FieldEditor.ss']['TEXTTITLE'] = 'Agrega un campo de texto';
$lang['es_MX']['SubmittedFormEmail.ss']['SUBMITTED'] = 'Los siguientes datos fueron enviados a tu sitio web:';
$lang['es_MX']['SubmittedFormReportField.ss']['SUBMITTED'] = 'Enviado a';
$lang['es_MX']['UserDefinedForm']['FORM'] = 'Formulario';
$lang['es_MX']['UserDefinedForm']['NORESULTS'] = 'No encuentro resultados coincidentes';
$lang['es_MX']['UserDefinedForm']['ONCOMPLETE'] = 'Al completar';
$lang['es_MX']['UserDefinedForm']['ONCOMPLETELABEL'] = 'Mostrar cuando se complete';
$lang['es_MX']['UserDefinedForm']['RECEIVED'] = 'Peticiones Recibidas';
$lang['es_MX']['UserDefinedForm']['SUBMISSIONS'] = 'Peticiones';
$lang['es_MX']['UserDefinedForm']['SUBMIT'] = 'Enviar';
$lang['es_MX']['UserDefinedForm']['TEXTONSUBMIT'] = 'Texto en el botón de envío:';
$lang['es_MX']['UserDefinedForm_SubmittedFormEmail']['EMAILSUBJECT'] = 'Presentación del formulario';

?>