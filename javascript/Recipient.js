/**
 * Email recipient behaviour.
 */

(function($) {
    $.entwine('ss', function($) {
        var recipient = {
            // Some fields are only visible when HTML email are being sent.
            updateFormatSpecificFields: function () {
                var sendPlainChecked = $('input[name="SendPlain"]').is(':checked');

                $('.field.toggle-html-only')[sendPlainChecked ? 'hide' : 'show']();
                $('.field.toggle-plain-only')[sendPlainChecked ? 'show' : 'hide']();
            }
        };

        $('#Form_ItemEditForm .EmailRecipientForm').entwine({
            onmatch: function () {
                recipient.updateFormatSpecificFields();
            },

            onunmatch: function () {
                this._super();
            }
        });

        $('#Form_ItemEditForm .EmailRecipientForm input[name="SendPlain"]').entwine({
            onchange: function () {
                recipient.updateFormatSpecificFields();
            }
        });
    });
}(jQuery));
