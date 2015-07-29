(function($) {
	$(document).ready(function() {
		var formId = "{$Form.FormName.JS}",
			errorContainerId = "{$ErrorContainerID.JS}",
			errorContainer = $('<fieldset><div><h2 id="errorContainerHeading" tabindex="-1"></h2><ul></ul></div></fieldset>');

		var messages = {<% loop $Fields %><% if $ErrorMessage && not $SetsOwnError %><% if $ClassName == 'EditableCheckboxGroupField' %>
			'{$Name.JS}[]': '{$ErrorMessage.JS}'<% if not Last %>,<% end_if %><% else %>
			'{$Name.JS}': '{$ErrorMessage.JS}'<% if not Last %>,<% end_if %><% end_if %><% end_if %><% end_loop %>
		};

		$(document).on("click", "input.text[data-showcalendar]", function() {
			$(this).ssDatepicker();

			if($(this).data('datepicker')) {
				$(this).datepicker('show');
			}
		});

		$("#" + formId).validate({
			ignore: ':hidden',
			errorClass: "error",
			errorElement: "span",
			errorPlacement: function(error, element) {
				error.addClass('message');
				error.attr('tabindex', '-1');

				if(element.is(":radio") || element.parents(".checkboxset").length > 0) {
					error.insertAfter(element.closest("ul"));
					element.closest(".field").find(".left").attr({
						"id": element.attr("id") + "-legend", 
						"tabindex": "-1"
						});
				} else if (element.is(".checkbox")) {
					error.insertAfter(element.closest("label"));
				} else {
					error.insertAfter(element);
				}

				<% if $DisplayErrorMessagesAtTop %>
					applyTopErrorMessage(element, error.html());
				<% end_if %>
			},
			success: function (error) {
				error.remove();
			},
			messages: messages,
			rules: {
				<% loop $Fields %>
					<% if $Validation %><% if ClassName == EditableCheckboxGroupField %>
						'{$Name.JS}[]': {$ValidationJSON.RAW},
					<% else %>
						'{$Name.JS}': {$ValidationJSON.RAW},
					<% end_if %><% end_if %>
				<% end_loop %>
			}

			/* 
			 * Conditional options.
			 * Using leading commas so we don't get a trailing comma on
			 * the last option. Trailing commas can break IE.
			 */
			<% if $EnableLiveValidation %>
				// Enable live validation
				,onfocusout: function (element) { this.element(element); }
			<% end_if %>

			<% if $DisplayErrorMessagesAtTop %>
				,focusInvalid: false
				,invalidHandler: function (event, validator) {
					var errorList = $('#' + errorContainerId + ' ul');

					// Update the error list with errors from the validator.
					// We do this because top messages are not part of the regular
					// error message life cycle, which jquery.validate handles for us.
					errorList.empty();

					$.each(validator.errorList, function () {
						applyTopErrorMessage($(this.element), this.message);
					});

					$("#errorContainerHeading").focus();
				}
				,onfocusout: false
			<% end_if %>
		});

		<% if $HideFieldLabels %>
			// Hide field labels (use HTML5 placeholder instead)
			$("#" + formId + "label.left").each(function() {
				$("#"+$(this).attr("for"))
					.attr("placeholder", $(this).text());
				$(this).remove();
			});
			Placeholders.init();
		<% end_if %>

		<% if $DisplayErrorMessagesAtTop %>
			/**
			 * @applyTopErrorMessage
			 * @param {jQuery} input - The jQuery input object which contains the field to validate
			 * @param {string} message - The error message to display (html escaped)
			 * @desc Update an error message (displayed at the top of the form).
			 */
			function elementIsFieldType(element, fieldType) {
				return element.attr('id').toLowerCase().indexOf(fieldType) !== -1;
			}
			function applyTopErrorMessage(input, message) {
				var inputID = input.attr('id'),
					anchor = '#' + inputID,
					elementID = inputID + '-top-error',
					errorContainer = $('#' + errorContainerId),
					messageElement = $('#' + elementID);

				// The 'message' param will be an empty string if the field is valid.
				if (!message) {
					// Style issues as fixed if they already exist
					messageElement.addClass('fixed');
					return;
				}

				messageElement.removeClass('fixed');
				errorContainer.show();

				if (messageElement.length === 1) {
					// Update the existing error message.
					messageElement.show().find('a').html(message);
				} else {
					
					// Add a new error message
					if (elementIsFieldType(input, 'checkboxgroup') || elementIsFieldType(input, 'radio')) {
						messageElement = $('<li><a></a></li>');
						messageElement
						.attr('id', elementID)
						.find('a')
							.attr('href', location.pathname + location.search + anchor + '-legend')
							.html(message);
					} else {
						messageElement = $('<li><label></label></li>');
						messageElement
						.attr('id', elementID)
						.find('label')
							.attr('for', elementID.split('-top-error')[0])
							.html(message);
					}
					errorContainer
						.find('ul')
						.append(messageElement);
				}
			}
			
			// Build container
			errorContainer
				.hide()
				.attr('id', errorContainerId)
				.find('h2')
					.text(ss.i18n._t(
						"UserForms.ERROR_CONTAINER_HEADER",
						"Please correct the following errors and try again:"
					));
			$('#' + formId).prepend(errorContainer);
		<% end_if %>
	});
})(jQuery);
