/**
 * Email recipient behaviour.
 */

(function ($) {
	$(document).ready(function () {

		var recipient = {
			// Some fields are only visible when HTML email are being sent.
			updateFormatSpecificFields: function () {
				var sendPlainChecked = $('#SendPlain').find('input[type="checkbox"]').is(':checked');

				// Hide the preview link when 'SendPlain' is selected.
				$('#EmailPreview')[sendPlainChecked ? 'hide' : 'show']();

				// Hide the template selector when 'SendPlain' is selected.
				$('#EmailTemplate')[sendPlainChecked ? 'hide' : 'show']();

				// Hide the HTML editor when 'SendPlain' is selected.
				$('#EmailBodyHtml')[sendPlainChecked ? 'hide' : 'show']();

				// Show the body teaxtarea when 'SendPlain' is selected.
				$('#EmailBody')[sendPlainChecked ? 'show' : 'hide']();
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

			$('#SendPlain').entwine({
				onchange: function () {
					recipient.updateFormatSpecificFields();
				}
			});
		});
	});
}(jQuery));
