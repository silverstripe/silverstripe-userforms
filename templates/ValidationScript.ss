(function($) {
	$(document).ready(function() {
		var formId = "{$Form.FormName.JS}",
			errorContainerId = "{$ErrorContainerID.JS}",
			errorContainer = $('<fieldset><div><h4></h4><ul></ul></div></fieldset>');

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
			errorClass: "required",
			errorElement: "span",
			errorPlacement: function(error, element) {
				error.addClass('message');

				if(element.is(":radio") || element.parents(".checkboxset").length > 0) {
					error.insertAfter(element.closest("ul"));
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
				,invalidHandler: function (event, validator) {
					var errorList = $('#' + errorContainerId + ' ul');

					// Update the error list with errors from the validator.
					// We do this because top messages are not part of the regular
					// error message life cycle, which jquery.validate handles for us.
					errorList.empty();

					$.each(validator.errorList, function () {
						applyTopErrorMessage($(this.element), this.message);
					});
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
			function applyTopErrorMessage(input, message) {
				var inputID = input.attr('id'),
					anchor = '#' + inputID,
					elementID = inputID + '-top-error',
					errorContainer = $('#' + errorContainerId),
					messageElement = $('#' + elementID),
					describedBy = input.attr('aria-describedby');

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
					// Generate better link to field
					input.closest('.field[id]').each(function(){
						anchor = '#' + $(this).attr('id');
					});
					
					// Add a new error message
					messageElement = $('<li><a></a></li>');
					messageElement
						.attr('id', elementID)
						.find('a')
							.attr('href', location.pathname + location.search + anchor)
							.html(message);
					errorContainer
						.find('ul')
						.append(messageElement);
						
					// link back to original input via aria
					// Respect existing non-error aria-describedby
					if ( !describedBy ) {
						describedBy = elementID;
					} else if ( !describedBy.match( new RegExp( "\\b" + elementID + "\\b" ) ) ) {
						// Add to end of list if not already present
						describedBy += " " + elementID;
					}
					input.attr( "aria-describedby", describedBy );
				}
			}
			
			// Build container
			errorContainer
				.hide()
				.attr('id', errorContainerId)
				.find('h4')
					.text(ss.i18n._t(
						"UserForms.ERROR_CONTAINER_HEADER",
						"Please correct the following errors and try again:"
					));
			$('#' + formId).prepend(errorContainer);
		<% end_if %>
	});
})(jQuery);
