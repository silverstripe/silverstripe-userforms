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
				// Get custom class from button
				var template = this.data('template');
				this.getGridField().trigger("addnewiteminline", template);
				return false;
			}
		});

		$(".ss-gridfield-delete-inline").entwine({
			onclick: function() {
				var msg = ss.i18n._t("GridFieldExtensions.CONFIRMDEL", "Are you sure you want to delete this?");

				if(confirm(msg)) {
					this.parents("tr").remove();
				}

				return false;
			}
		});
	});
	
})(jQuery);
