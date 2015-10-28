/**
 * form builder behaviour.
 */

 (function($) {
	$.entwine('ss', function($) {
		var stickyHeaderInterval;

		$(".uf-field-editor tbody").entwine({
			onmatch: function() {
				var i, 
					thisLevel, 
					depth = 0,
					$buttonrow = $('.uf-field-editor .ss-gridfield-buttonrow').addClass('stickyButtons'),
					navHeight = $('.cms-content-header.north').height() + parseInt($('.stickyButtons').css('padding-top'), 10),
					fieldEditor = $('.uf-field-editor'),
					self = this;

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

				// Make sure gridfield buttons stick to top of page when user scrolls down
				stickyHeaderInterval = setInterval(function () {
					var offsetTop = fieldEditor.offset().top;
					$buttonrow.width(self.width());
					if (offsetTop > navHeight || offsetTop === 0) {	
						$buttonrow.removeClass('stickyButtons');
					} else {
						$buttonrow.addClass('stickyButtons');
					};
				}, 300);
			},
			onunmatch: function () {
				this._super();

				clearInterval(stickyHeaderInterval);
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

				this._super();
					
				// When the 'Add field' button is clicked set a one time listener.
				// When the GridField is reloaded focus on the newly added field.
				this.on('addnewinline', function () {
					self.one('reload', function () {
						//If fieldgroup, focus on the start marker
						var $newField = self.find('.ss-gridfield-item').last(), $groupEnd;
						if ($newField.attr('data-class') === 'EditableFieldGroupEnd') {
							$groupEnd = $newField;
							$groupEnd.prev().find('.col-Title input').focus();
							$newField = $groupEnd.add($groupEnd.prev());
							$groupEnd.css('visibility', 'hidden');
						} else {
							$newField.find('.col-Title input').focus();
						}

						// animate the row positioning (add the first class)
						if (document.createElement('div').style.animationName !== void 0) {
							$newField.addClass('newField');
						}

						// Once the animation has completed
						setTimeout(function () {
							$newField.removeClass('newField').addClass('flashBackground');
							$(".cms-content-fields").scrollTop($(".cms-content-fields")[0].scrollHeight);
							if($groupEnd) {
								$groupEnd.css('visibility', 'visible');
							}
						}, 500);
					});
				});
			},
			onummatch: function () {
				this._super();
			}
		});
	});
}(jQuery));
