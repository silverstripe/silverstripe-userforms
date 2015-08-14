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
	});
}(jQuery));
