(function($) {
	$(document).ready(function() {
		$("#Form_Form").validate({
			ignore: ':hidden',
			errorClass: "required",
			errorElement: "span",
			errorPlacement: function(error, element) {
				error.addClass('message')
				if(element.is(":radio")) {
					error.insertAfter(element.closest("ul"));
				} else {
					error.insertAfter(element);
				}
			},
			messages: {
				<% loop $Fields %>
					<% if $ErrorMessage && not $SetsOwnError %>
						'{$Name.JS}': '{$ErrorMessage.JS}',
					<% end_if %>
				<% end_loop %>
			},
			rules: {
				<% loop $Fields %>
					<% if $Validation %>
						'{$Name.JS}': {$ValidationJSON.RAW},
					<% end_if %>
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
