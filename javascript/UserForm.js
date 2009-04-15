(function($) {
	$(document).ready(function() {
		$("#FormSubmissions .deleteSubmission").click(function() {
			var deletedSubmission = $(this);
			$.post($(this).attr('href'), function(data) {
				deletedSubmission.parents('div.report').fadeOut();
			});
			return false;
		});
	})
})(jQuery);