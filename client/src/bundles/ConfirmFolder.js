/* global window */
import i18n from 'i18n';
import jQuery from 'jquery';
import React from 'react';
import ReactDOM from 'react-dom';
import { loadComponent } from 'lib/Injector';
import url from 'url';
import qs from 'qs';

const FormBuilderModal = loadComponent('FormBuilderModal');

jQuery.entwine('ss', ($) => {
  /** Mark newly added fields as new */
  $('#Form_EditForm_Fields').entwine({
    onmatch() {
      this._super();

      this.on('addnewinline', () => {
        this.one('reload', () => {
          const newField = this.find('.ss-gridfield-item').last();
          newField.find('.col-ClassName select').attr('data-folderconfirmed', 0);
        });
      });
    }
  });

  function toggleVisibility(check, show, hide) {
    if (check) {
      $(show).show();
      $(hide).hide();
    } else {
      $(hide).show();
      $(show).hide();
    }
  }

  /** Move our options under the radio button areas */
  $('#Form_ConfirmFolderForm_FolderOptions-new').entwine({
    onmatch() {
      $('#Form_ConfirmFolderForm_CreateFolder_Holder').detach().appendTo($('#Form_ConfirmFolderForm_FolderOptions-new').parent().parent());
      toggleVisibility($(this).prop('checked'), '#Form_ConfirmFolderForm_CreateFolder_Holder', '#Form_ConfirmFolderForm_FolderID_Holder');
    },
    onchange() {
      toggleVisibility($(this).prop('checked'), '#Form_ConfirmFolderForm_CreateFolder_Holder', '#Form_ConfirmFolderForm_FolderID_Holder');
    }
  });

  /** Move our options under the radio button areas */
  $('#Form_ConfirmFolderForm_FolderOptions-existing').entwine({
    onmatch() {
      $('#Form_ConfirmFolderForm_FolderID_Holder').detach().appendTo($('#Form_ConfirmFolderForm_FolderOptions-existing').parent().parent());
      toggleVisibility($(this).prop('checked'), '#Form_ConfirmFolderForm_FolderID_Holder', '#Form_ConfirmFolderForm_CreateFolder_Holder');
    },
    onchange() {
      toggleVisibility($(this).prop('checked'), '#Form_ConfirmFolderForm_FolderID_Holder', '#Form_ConfirmFolderForm_CreateFolder_Holder');
    }
  });

  /** Display permissions for folder selected */
  $('#Form_ConfirmFolderForm_FolderID_Holder .treedropdownfield.is-open,#Form_ItemEditForm_FolderID .treedropdownfield.is-open').entwine({
    onunmatch() {
      // Build url
      const adminUrl = $(this).closest('#Form_ConfirmFolderForm').data('adminUrl');
      const parsedURL = url.parse(`${adminUrl}user-forms/getfoldergrouppermissions`);
      const parsedQs = qs.parse(parsedURL.query);
      parsedQs.FolderID = $(this).find('input[name=FolderID]').val();
      const fetchURL = url.format({ ...parsedURL, search: qs.stringify(parsedQs) });

      return fetch(fetchURL, {
        credentials: 'same-origin',
      })
        .then(response => response.json())
        .then(response => {
          $(this).siblings('.form__field-description').html(response);
          $(this).parent().siblings('.form__field-description').html(response);
          return response;
        })
        .catch((error) => {
          jQuery.noticeAdd({ text: error.message, stay: false, type: 'error' });
        });
    }
  });

  /**
   * Monitor new fields to intercept when EditableFileField is selected
   */
  $(".uf-field-editor .ss-gridfield-items .dropdown.editable-column-field.form-group--no-label:not([data-folderconfirmed='1'])").entwine({
    onchange() {
      // ensure EditableFileField is selected
      if (this.get(0).value !== 'SilverStripe\\UserForms\\Model\\EditableFormField\\EditableFileField') {
        return;
      }

      // ensure there are no other EditableFileField confirmed
      if ($(".uf-field-editor .ss-gridfield-items .dropdown.editable-column-field.form-group--no-label[data-folderconfirmed='1']").length) {
        return;
      }

      // open folder confirmation dialog
      let dialog = $('#confirm-folder__dialog-wrapper');

      if (dialog.length) {
        dialog.remove();
      }

      dialog = $('<div id="confirm-folder__dialog-wrapper" />');
      const id = $(this).closest('tr').data('id');
      const adminUrl = $(this).closest('.uf-field-editor').data('adminUrl');
      dialog.data('id', id);
      dialog.data('adminUrl', adminUrl);
      $('body').append(dialog);

      dialog.open();
    },
  });

  /** handle modal rendering */
  $('#confirm-folder__dialog-wrapper').entwine({
    onunmatch() {
      // solves errors given by ReactDOM "no matched root found" error.
      this._clearModal();
    },

    open() {
      this._renderModal(true);
    },

    close(noRevert) {
      if (!noRevert) {
        // revert field to TextField
        const id = $('#confirm-folder__dialog-wrapper').data('id');
        const select = $(`.ss-gridfield-item[data-id='${id}'] .dropdown.editable-column-field.form-group--no-label[data-folderconfirmed='0']`);
        select.val('SilverStripe\\UserForms\\Model\\EditableFormField\\EditableTextField');
      }

      this._renderModal(false);
    },

    _renderModal(isOpen) {
      const handleHide = (...args) => this._handleHideModal(...args);
      const handleSubmit = (...args) => this._handleSubmitModal(...args);
      const title = i18n._t('UserForms.FILE_CONFIRMATION_TITLE', 'Select file upload folder');
      const editableFileFieldID = $(this).data('id');

      // Build schema url
      const adminUrl = $(this).data('adminUrl');
      const parsedURL = url.parse(`${adminUrl}user-forms/confirmfolderformschema`);
      const parsedQs = qs.parse(parsedURL.query);
      parsedQs.ID = editableFileFieldID;
      const schemaUrl = url.format({ ...parsedURL, search: qs.stringify(parsedQs) });

      ReactDOM.render(
        <FormBuilderModal
          title={title}
          isOpen={isOpen}
          onSubmit={handleSubmit}
          onClosed={handleHide}
          schemaUrl={schemaUrl}
          bodyClassName="modal__dialog"
          className="confirm-folder-modal"
          responseClassBad="modal__response modal__response--error"
          responseClassGood="modal__response modal__response--good"
          identifier="UserForms.ConfirmFolder"
        />,
        this[0]
      );
    },

    _clearModal() {
      ReactDOM.unmountComponentAtNode(this[0]);
      // this.empty();
    },

    _handleHideModal() {
      // close the modal
      return this.close();
    },

    _handleSubmitModal(data, action, submitFn) {
      return submitFn()
        .then(() => {
          jQuery.noticeAdd({ text: i18n._t('UserForms.FILE_CONFIRMATION_CONFIRMATION', 'Folder confirmed successfully.'), stay: false, type: 'success' });
          this.close(true);
          $('[name=action_doSave], [name=action_save]').click();
        })
        .catch((error) => {
          jQuery.noticeAdd({ text: error.message, stay: false, type: 'error' });
        });
    },
  });

  $('#Form_ConfirmFolderForm_action_cancel').entwine({
    onclick() {
      $('#confirm-folder__dialog-wrapper').close();
    }
  });
});
