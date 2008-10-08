<?php

/**
 * Portuguese (Portugal) language pack
 * @package modules: userforms
 * @subpackage i18n
 */

i18n::include_locale_file('modules: userforms', 'en_US');

global $lang;

if(array_key_exists('pt_PT', $lang) && is_array($lang['pt_PT'])) {
	$lang['pt_PT'] = array_merge($lang['en_US'], $lang['pt_PT']);
} else {
	$lang['pt_PT'] = $lang['en_US'];
}

$lang['pt_PT']['EditableCheckbox']['ANY'] = 'Qualquer Um';
$lang['pt_PT']['EditableCheckbox']['NOTSELECTED'] = 'Não seleccionado';
$lang['pt_PT']['EditableCheckbox']['SELECTED'] = 'Seleccionado';
$lang['pt_PT']['EditableCheckbox.ss']['CHECKBOX'] = 'Campo Checkbox';
$lang['pt_PT']['EditableCheckbox.ss']['DELETE'] = 'Apagar o campo';
$lang['pt_PT']['EditableCheckbox.ss']['DRAG'] = 'Arraste para reordenar os campos';
$lang['pt_PT']['EditableCheckbox.ss']['LOCKED'] = 'Este campo não pode ser modificado';
$lang['pt_PT']['EditableCheckbox.ss']['MORE'] = 'Mais opções';
$lang['pt_PT']['EditableCheckboxGroupField.ss']['ADD'] = 'Adicionar nova opção';
$lang['pt_PT']['EditableCheckboxGroupField.ss']['CHECKBOXGROUP'] = 'Grupo de Checkbox';
$lang['pt_PT']['EditableCheckboxGroupField.ss']['DELETE'] = 'Apagar este campo';
$lang['pt_PT']['EditableCheckboxGroupField.ss']['DRAG'] = 'Arraste para reordenar os campos';
$lang['pt_PT']['EditableCheckboxGroupField.ss']['LOCKED'] = 'Estes campos não podem ser modificados';
$lang['pt_PT']['EditableCheckboxGroupField.ss']['MORE'] = 'Mais opções';
$lang['pt_PT']['EditableCheckboxGroupField.ss']['REQUIRED'] = 'Este campo é obrigatório para este formulário e não pode ser apagado';
$lang['pt_PT']['EditableCheckboxOption.ss']['DELETE'] = 'Remover esta opção';
$lang['pt_PT']['EditableCheckboxOption.ss']['DRAG'] = 'Arraste para reordenar as opções';
$lang['pt_PT']['EditableCheckboxOption.ss']['LOCKED'] = 'Estes campos não podem ser modificados';
$lang['pt_PT']['EditableDateField.ss']['DATE'] = 'Campo Data';
$lang['pt_PT']['EditableDateField.ss']['DELETE'] = 'Apagar este campo';
$lang['pt_PT']['EditableDateField.ss']['DRAG'] = 'Arraste para reordenar os campos';
$lang['pt_PT']['EditableDateField.ss']['MORE'] = 'Mais opções';
$lang['pt_PT']['EditableDropdown.ss']['ADD'] = 'Adicionar nova opção';
$lang['pt_PT']['EditableDropdown.ss']['DELETE'] = 'Apagar este campo';
$lang['pt_PT']['EditableDropdown.ss']['DRAG'] = 'Arraste para reordenar os campos';
$lang['pt_PT']['EditableDropdown.ss']['LOCKED'] = 'Estes campos não podem ser modificados';
$lang['pt_PT']['EditableDropdown.ss']['MORE'] = 'Mais opções';
$lang['pt_PT']['EditableDropdown.ss']['REQUIRED'] = 'Este campo é obrigatório para este formulário e não pode ser apagado';
$lang['pt_PT']['EditableDropdownOption.ss']['DELETE'] = 'Remover esta opção';
$lang['pt_PT']['EditableDropdownOption.ss']['DRAG'] = 'Arraste para reordenar as opções';
$lang['pt_PT']['EditableDropdownOption.ss']['LOCKED'] = 'Estes campos não podem ser modificados';
$lang['pt_PT']['EditableEmailField.ss']['DELETE'] = 'Apagar este campo';
$lang['pt_PT']['EditableEmailField.ss']['DRAG'] = 'Arraste para reordenar os campos';
$lang['pt_PT']['EditableEmailField.ss']['EMAIL'] = 'Campo de Endereço de Email';
$lang['pt_PT']['EditableEmailField.ss']['MORE'] = 'Mais opções';
$lang['pt_PT']['EditableEmailField.ss']['REQUIRED'] = 'Este campo é obrigatório para este formulário e não pode ser apagado.';
$lang['pt_PT']['EditableFileField.ss']['DELETE'] = 'Apagar este campo';
$lang['pt_PT']['EditableFileField.ss']['DRAG'] = 'Arraste para reordenar os campos';
$lang['pt_PT']['EditableFileField.ss']['FILE'] = 'Campo para envio de ficheiros';
$lang['pt_PT']['EditableFileField.ss']['MORE'] = 'Mais opções';
$lang['pt_PT']['EditableFormField']['ENTERQUESTION'] = 'Insira a pergunta';
$lang['pt_PT']['EditableFormField']['REQUIRED'] = 'Obrigatório?';
$lang['pt_PT']['EditableFormField.ss']['DELETE'] = 'Apagar este campo';
$lang['pt_PT']['EditableFormField.ss']['DRAG'] = 'Arraste para reordenar os campos';
$lang['pt_PT']['EditableFormField.ss']['LOCKED'] = 'Estes campos não podem ser modificados';
$lang['pt_PT']['EditableFormField.ss']['MORE'] = 'Mais opções';
$lang['pt_PT']['EditableFormField.ss']['REQUIRED'] = 'Este campo é obrigatório para este formulário e não pode ser apagado.';
$lang['pt_PT']['EditableFormFieldOption.ss']['DELETE'] = 'Remover esta opção';
$lang['pt_PT']['EditableFormFieldOption.ss']['DRAG'] = 'Arraste para reordenar os campos';
$lang['pt_PT']['EditableFormFieldOption.ss']['LOCKED'] = 'Estes campos não podem ser modificados';
$lang['pt_PT']['EditableFormHeading.ss']['DELETE'] = 'Apagar este campo';
$lang['pt_PT']['EditableFormHeading.ss']['DRAG'] = 'Arraste para reordenar os campos';
$lang['pt_PT']['EditableFormHeading.ss']['HEADING'] = 'Campo de cabeçalho';
$lang['pt_PT']['EditableFormHeading.ss']['MORE'] = 'Mais opções';
$lang['pt_PT']['EditableRadioField.ss']['ADD'] = 'Adicionar nova opção';
$lang['pt_PT']['EditableRadioField.ss']['DELETE'] = 'Apagar este campo';
$lang['pt_PT']['EditableRadioField.ss']['DRAG'] = 'Arraste para reordenar os campos';
$lang['pt_PT']['EditableRadioField.ss']['LOCKED'] = 'Estes campos não podem ser modificados';
$lang['pt_PT']['EditableRadioField.ss']['MORE'] = 'Mais opções';
$lang['pt_PT']['EditableRadioField.ss']['REQUIRED'] = 'Este campo é obrigatório para este formulário e não pode ser apagado.';
$lang['pt_PT']['EditableRadioOption.ss']['DELETE'] = 'Remover esta opção';
$lang['pt_PT']['EditableRadioOption.ss']['DRAG'] = 'Arraste para reordenar os campos';
$lang['pt_PT']['EditableRadioOption.ss']['LOCKED'] = 'Estes campos não podem ser modificados';
$lang['pt_PT']['EditableTextField']['DEFAULTTEXT'] = 'Texto por defeito';
$lang['pt_PT']['EditableTextField']['NUMBERROWS'] = 'Número de linhas';
$lang['pt_PT']['EditableTextField.ss']['DELETE'] = 'Apagar este campo';
$lang['pt_PT']['EditableTextField.ss']['DRAG'] = 'Arraste para reordenar os campos';
$lang['pt_PT']['EditableTextField.ss']['MORE'] = 'Mais opções';
$lang['pt_PT']['EditableTextField.ss']['TEXTFIELD'] = 'Campo de texto';
$lang['pt_PT']['EditableTextField']['TEXTBOXLENGTH'] = 'Tamanho da text box';
$lang['pt_PT']['EditableTextField']['TEXTLENGTH'] = 'Comprimento do texto';
$lang['pt_PT']['FieldEditor.ss']['ADD'] = 'Adicionar';
$lang['pt_PT']['FieldEditor.ss']['CHECKBOXGROUP'] = 'Checkboxes';
$lang['pt_PT']['FieldEditor.ss']['CHECKBOXGROUPTITLE'] = 'Adicionar grupo de checkbox';
$lang['pt_PT']['FieldEditor.ss']['CHECKBOXTITLE'] = 'Adicionar checkbox';
$lang['pt_PT']['FieldEditor.ss']['DATE'] = 'Data';
$lang['pt_PT']['FieldEditor.ss']['DATETITLE'] = 'Adicionar Cabeçalho de Data';
$lang['pt_PT']['FieldEditor.ss']['EMAIL'] = 'Email';
$lang['pt_PT']['FieldEditor.ss']['EMAILTITLE'] = 'Adicionar campo de email';
$lang['pt_PT']['FieldEditor.ss']['FILE'] = 'Ficheiro';
$lang['pt_PT']['FieldEditor.ss']['FILETITLE'] = 'Adicionar campo para envio de ficheiros';
$lang['pt_PT']['FieldEditor.ss']['FORMHEADING'] = 'Cabeçalho';
$lang['pt_PT']['FieldEditor.ss']['FORMHEADINGTITLE'] = 'Adicionar Cabeçalho de formulário';
$lang['pt_PT']['FieldEditor.ss']['MEMBER'] = 'Lista de Utilizadores';
$lang['pt_PT']['FieldEditor.ss']['MEMBERTITLE'] = 'Adicionar lista de utilizadores';
$lang['pt_PT']['FieldEditor.ss']['TEXT'] = 'Texto';
$lang['pt_PT']['FieldEditor.ss']['TEXTTITLE'] = 'Adicionar campo de texto';
$lang['pt_PT']['SubmittedFormEmail.ss']['SUBMITTED'] = 'Os seguintes dados foram inseridos no seu site:';
$lang['pt_PT']['UserDefinedForm']['FORM'] = 'Formulário';
$lang['pt_PT']['UserDefinedForm']['NORESULTS'] = 'Resultados não encontrados';
$lang['pt_PT']['UserDefinedForm']['RECEIVED'] = 'Dados Recolhidos';
$lang['pt_PT']['UserDefinedForm']['SUBMIT'] = 'Enviar';
$lang['pt_PT']['UserDefinedForm']['TEXTONSUBMIT'] = 'Texto no botão "Enviar"';

?>