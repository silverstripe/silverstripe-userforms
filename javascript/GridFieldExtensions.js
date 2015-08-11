(function($) {
	$.entwine("ss", function($) {
		// See gridfieldextensions/javascript/GridFieldExtensions.js
		
		$(".ss-gridfield.ss-gridfield-editable").entwine({
			onaddnewiteminline: function(e, template) {
				var tmpl = window.tmpl;
				var row = this.find("." + template);
				var num = this.data("add-inline-num") || 1;

				tmpl.cache[template] = tmpl(row.html());

				this.find("tbody").append(tmpl(template, { num: num }));
				this.find(".ss-gridfield-no-items").hide();
				this.data("add-inline-num", num + 1);
			}
		});

		$(".ss-gridfield-add-new-item-inline").entwine({
			onclick: function() {
				// Create each template
				var gridfield = this.getGridField();
				$.each(this.data('template-names'), function(index, template) {
					console.log(template);
					gridfield.trigger("addnewiteminline", template);
				});
				return false;
			}
		});
	});
	
})(jQuery);
