/**
 * Email recipient behaviour.
 */

(function ($) {
	$(document).ready(function () {

        var sendPlain = $('input[name="SendPlain"]');
		var recipient = {
			// Some fields are only visible when HTML email are being sent.
			updateFormatSpecificFields: function () {
				var sendPlainChecked = sendPlain.is(':checked');

				$(".field.toggle-html-only")[sendPlainChecked ? 'hide' : 'show']();
				$(".field.toggle-plain-only")[sendPlainChecked ? 'show' : 'hide']();
			}
		};

		$.entwine('udf.recipient', function ($) {
			$('#Form_ItemEditForm').entwine({
				onmatch: function () {
					recipient.updateFormatSpecificFields();
				},
				onunmatch: function () {
					this._super();
				}
			});

			sendPlain.entwine({
				onchange: function () {
					recipient.updateFormatSpecificFields();
				}
			});
		});
	});
}(jQuery));
