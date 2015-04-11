(function($) {
	$(document).ready(function() {
		var messages = {<% loop $Fields %><% if $ErrorMessage && not $SetsOwnError %><% if ClassName == EditableCheckboxGroupField %>
			'{$Name.JS}[]': '{$ErrorMessage.JS}'<% if not Last %>,<% end_if %><% else %>
			'{$Name.JS}': '{$ErrorMessage.JS}'<% if not Last %>,<% end_if %><% end_if %><% end_if %><% end_loop %>
		};

		$(document).on("click", "input.text[data-showcalendar]", function() {
			$(this).ssDatepicker();

			if($(this).data('datepicker')) {
				$(this).datepicker('show');
			}
		});

		$("#Form_Form").validate({
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
			},
			<% if $EnableLiveValidation %>
				// Enable live validation
				onfocusout : function(element) { this.element(element); }
			<% end_if %>
		});
		<% if $HideFieldLabels %>
			// Hide field labels (use HTML5 placeholder instead)
			$("#Form_Form label.left").each(function() {
				$("#"+$(this).attr("for"))
					.attr("placeholder", $(this).text());
				$(this).remove();
			});
			Placeholders.init();
		<% end_if %>
	});
})(jQuery);
