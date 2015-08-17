/**
 * form builder behaviour.
 */

 (function($) {
	$.entwine('ss', function($) {
		$(".uf-field-editor tbody").entwine({
			onmatch: function() {
				var i, thisLevel, depth = 0;
				this._super();
				
				// Loop through all rows and set necessary styles
				this.find('.ss-gridfield-item').each(function() {
					switch($(this).data('class')) {
						case 'EditableFormStep': {
							depth = 0;
							return;
						}
						case 'EditableFieldGroup': {
							thisLevel = ++depth;
							break;
						}
						case 'EditableFieldGroupEnd': {
							thisLevel = depth--;
							break;
						}
						default: {
							thisLevel = depth;
						}
					}
					
					$(this).toggleClass('inFieldGroup', thisLevel > 0);
					for(i = 1; i <= 5; i++) {
						$(this).toggleClass('inFieldGroup-level-'+i, thisLevel >= i);
					}
				});
			},
			onunmatch: function () {
				this._super();
			}
		});

		// When new fields are added..
		$('.uf-field-editor .ss-gridfield-buttonrow .action').entwine({
			onclick: function (e) {
				this._super(e);

				this.trigger('addnewinline');
			}
		});

		$('.uf-field-editor').entwine({
			onmatch: function () {
				var self = this;

				// When the 'Add field' button is clicked set a one time listener.
				// When the GridField is reloaded focus on the newly added field.
				this.on('addnewinline', function () {
					self.one('reload', function () {
						//If fieldgroup, focus on the start marker
						if ($('.uf-field-editor .ss-gridfield-item').last().attr('data-class') === 'EditableFieldGroupEnd') {
							$('.uf-field-editor .ss-gridfield-item').last().prev().find('.col-Title input').focus();
						} else {
							$('.uf-field-editor .ss-gridfield-item:last-child .col-Title input').focus();
						}
					});
				});
			}
		});
	});
}(jQuery));
