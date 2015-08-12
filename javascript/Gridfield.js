/**
 * form builder behaviour.
 */

 (function($) {
	$.entwine('ss', function($) {
		$(".ss-gridfield-orderable tbody").entwine({
			onmatch: function() {
				this._super();

				this.find('.ss-gridfield-item')
					.removeClass('inFieldGroup');

				this.find('.ss-gridfield-item[data-class="EditableFieldGroup"]')
					.nextUntil('.ss-gridfield-item[data-class="EditableFieldGroupEnd"]')
					.addClass('inFieldGroup');
			},
			onunmatch: function () {
				this._super();
			}
		});
	});
}(jQuery));
