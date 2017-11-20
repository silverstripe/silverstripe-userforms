/**
 * Email recipient behaviour.
 */

import jQuery from 'jquery';

jQuery.entwine('ss', ($) => {
  const recipient = {
    // Some fields are only visible when HTML email are being sent.
    updateFormatSpecificFields: () => {
      const sendPlainChecked = $('input[name="SendPlain"]').is(':checked');

      $('.field.toggle-html-only')[sendPlainChecked ? 'hide' : 'show']();
      $('.field.toggle-plain-only')[sendPlainChecked ? 'show' : 'hide']();
    },
  };

  $('#Form_ItemEditForm .EmailRecipientForm').entwine({
    onmatch: () => {
      recipient.updateFormatSpecificFields();
    },

    onunmatch: () => {
      this._super();
    },
  });

  $('#Form_ItemEditForm .EmailRecipientForm input[name="SendPlain"]').entwine({
    onchange: () => {
      recipient.updateFormatSpecificFields();
    },
  });
});
