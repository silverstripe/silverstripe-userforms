/**
 * Javascript required to power the user defined forms.
 * 
 * Rewritten from the prototype FieldEditor and constantly
 * being refactored to be be less specific on the UDF dom.
 */
(function($) {
	$(document).ready(function() {
		/**
		 * Namespace
		 */
		var userforms = userforms || {};
		
		/**
		 * Messages from UserForms are translatable using i18n.
		 */
		userforms.messages = {
			CONFIRM_DELETE_ALL_SUBMISSIONS: 'All submissions will be permanently removed. Continue?',
			ERROR_CREATING_FIELD: 'Error creating field',
			ADDING_FIELD: 'Adding new field',
			ADDED_FIELD: 'Added new field',
			HIDE_OPTIONS: 'Hide options',
			SHOW_OPTIONS: 'Show options',
			ADDING_OPTION: 'Adding option',
			ADDED_OPTION: 'Added option',
			ERROR_CREATING_OPTION: 'Error creating option',
			REMOVED_OPTION: 'Removed option',
			ADDING_RULE: 'Adding rule'
		};
		
		/**
		 * Returns a given translatable string from a passed key. Keys
		 * should be all caps without any spaces.
		 */
		userforms.message = function() {
			en = arguments[1] || userforms.messages[arguments[0]];
			
			return ss.i18n._t("UserForms."+ arguments[0], en);
		};
		
		/**
		 * Update the sortable properties of the form as a function
		 * since the application will need to refresh the UI dynamically based
		 * on a number of factors including when the user adds a page or
		 * swaps between pages
		 *
		 */
		userforms.update = function() {
			$("#Fields_fields").sortable({
				handle: '.fieldHandler',
				cursor: 'pointer',
				items: 'li.EditableFormField',
				placeholder: 'removed-form-field',
				opacity: 0.6,
				revert: 'true',
				change : function (event, ui) {
					$("#Fields_fields").sortable('refreshPositions');
				},
				update : function (event, ui) {
					var sort = 1;

					$("li.EditableFormField").each(function() {
						$(this).find(".sortHidden").val(sort++);
					});
				}
			});

			$(".editableOptions").sortable({
				handle: '.handle',
				cursor:'pointer',
				items: 'li',
				placeholder: 'removed-form-field',
					opacity: 0.6,
				revert: true,
				change : function (event, ui) {
					$(this).sortable('refreshPositions');
				},
				update : function (event, ui) {
					var sort = 1;
					$(".editableOptions li").each(function() {
						$(this).find(".sortOptionHidden").val(sort++);
					});
				}
			});
		};
		
		userforms.appendToURL = function(url, pathsegmenttobeadded) {
			var parts = url.match(/([^\?#]*)?(\?[^#]*)?(#.*)?/);
			for(var i in parts) if(!parts[i]) parts[i] = '';
			return parts[1] + pathsegmenttobeadded + parts[2] + parts[3];
		}

		/**
		 * Workaround for not refreshing the sort.
		 * 
		 * TODO: better solution would to not fire this on every hover but needs to
		 *		ensure it doesn't have edge cases. The sledge hammer approach.
		 */
		$(".fieldHandler, .handle").live('hover', function() {
			userforms.update();
		});
		
		/**
		 * Kick off the UserForms UI
		 */
		userforms.update();
		
		
		$.entwine('udf', function($){
			
			/*-------------------- FIELD EDITOR ----------------------- */
			
			/**
			 * Create a new instance of a field in the current form 
			 * area. the type information should all be on this object
			 */
			$('div.FieldEditor .MenuHolder .action').entwine({
				onclick: function(e) {
					var form = $("#Form_EditForm"),
						length = $(".FieldInfo").length + 1, 
						fieldType = $(this).siblings("select").val(),
						formData = form.serialize()+'NewID='+ length +"&Type="+ fieldType, 
						fieldEditor = $(this).closest('.FieldEditor');

					e.preventDefault();

					if($("#Fields").hasClass('readonly') || !fieldType) {
						return;
					}
					
					
					// Due to some very weird behaviout of jquery.metadata, the url have to be double quoted
					var addURL = fieldEditor.attr('data-add-url').substr(1, fieldEditor.attr('data-add-url').length-2);

					$.ajax({
						headers: {"X-Pjax" : 'Partial'},
						type: "POST",
						url: addURL,
						data: formData, 
						success: function(data) {
							$('#Fields_fields').append(data);

							statusMessage(userforms.message('ADDED_FIELD'));
							
							var name = $("#Fields_fields li.EditableFormField:last").attr("id").split(' ');

							$("#Fields_fields select.fieldOption").append("<option value='"+ name[2] +"'>New "+ name[2] + "</option>");
							$("#Fields_fields").sortable('refresh');
						},
						error: function(e) {
							alert(ss.i18n._t('GRIDFIELD.ERRORINTRANSACTION'));
						}
					});
				}
			});
			
			/**
			 * Delete a field from the user defined form
			 */
			$(".EditableFormField .delete").entwine({
				onclick: function(e) {
					e.preventDefault();
					
					var text = $(this).parents("li").find(".fieldInfo .text").val();
					
					// Remove all the rules with relate to this field
					$("#Fields_fields .customRules select.fieldOption option").each(function(i, element) {
						if($(element).text() === text) {
							// check to see if this is selected. If it is then just remove the whole rule
							if($(element).parent('select.customRuleField').val() === $(element).val()) {
								$(element).parents('li.customRule').remove();
							} else {
								// otherwise remove the option
								$(element).remove();	
							}
						}
					});
					$(this).parents(".EditableFormField").slideUp(function(){$(this).remove()})
				}
			});
			
			/** 
			 * Upon renaming a field we should go through and rename all the
			 * fields in the select fields to use this new field title. We can
			 * just worry about the title text - don't mess around with the keys
			 */
			$('.EditableFormField .fieldInfo .text').entwine({
				onchange: function(e) {
					var value = $(this).val();
					var name = $(this).parents("li").attr("id").split(' ');
					$("#Fields_fields select.fieldOption option").each(function(i, domElement) {
						if($(domElement).val() === name[2]) {
							$(domElement).text(value);	
						}
					});
				}
			});
			
			/**
			 * Show the more options popdown. Or hide it if we currently have it open
			 */
			$(".EditableFormField .moreOptions").entwine({
				onclick: function(e) {
					e.preventDefault();
					
					var parent = $(this).parents(".EditableFormField");
					if(!parent) {
						return;
					}
					
					var extraOptions = parent.children(".extraOptions");
					if(!extraOptions) {
						return;
					}
					
					if(extraOptions.hasClass('hidden')) {
						$(this).addClass("showing");
						$(this).html('Hide options');
						extraOptions.removeClass('hidden');
					} else {
						$(this).removeClass("showing");
						$(this).html('Show options');
						extraOptions.addClass('hidden');	
					}
				}
			});
			
			/**
			 * Add a suboption to a radio field or to a dropdown box for example
			 */
			$(".EditableFormField .addableOption").entwine({
				onclick: function(e) {
					e.preventDefault();

					// Give the user some feedback
					statusMessage(userforms.message('ADDING_OPTION'));

					// variables
					var options = $(this).parent("li");
					var action = userforms.appendToURL($("#Form_EditForm").attr("action"), '/field/Fields/addoptionfield');
					var parent = $(this).attr("rel");
					var securityID = ($("input[name=SecurityID]").length > 0) ? $("input[name=SecurityID]").first().attr("value") : '';

					// send ajax request to the page
					$.ajax({
						type: "GET",
						url: action,
						data: 'Parent='+ parent +'&SecurityID='+securityID,
						// create a new field
						success: function(msg){
							options.before(msg);
							statusMessage(userforms.message('ADDED_OPTION'));
						},

						// error creating new field
						error: function(request, text, error) {
							statusMessage(userforms.message('ERROR_CREATING_OPTION'));
						} 
					});

				}
			});
			
			/**
			 * Delete a suboption such as an dropdown option or a 
			 * checkbox field
			 */
			$(".EditableFormField .deleteOption").entwine({
				onclick: function(e) {
					e.preventDefault();
					
					// pass the deleted status onto the element
					$(this).parents("li:first").find("[type=text]:first").attr("value", "field-node-deleted");
					$(this).parents("li:first").hide();

					// Give the user some feedback
					statusMessage(userforms.message('REMOVED_OPTION'));
				}
			});
			
			/**
			 * Custom Rules Interface
			 * 
			 * Hides the input text field if the conditionOption is 'IsBlank' or 'IsNotBlank'
			 */
			$("select.conditionOption").entwine({
				onchange: function() {
					var valueInput = $(this).siblings(".ruleValue");
					if($(this).val() && $(this).val() !== "IsBlank" && $(this).val() !== "IsNotBlank") {
						valueInput.removeClass("hidden");
					} else {
						valueInput.addClass("hidden");
					}
				}
			});

			/**
			 * Delete a custom rule
			 */
			$(".customRules .deleteCondition").entwine({
				onclick: function(e) {
					e.preventDefault();
					$(this).parent("li").fadeOut().remove();
				}
			});

			/**
			 * Adding a custom rule to a given form
			 */
			$(".customRules .addCondition").entwine({
				onclick: function(e) {
					e.preventDefault();
					
					// Give the user some feedback
					statusMessage(userforms.message('ADDING_RULE'));

					// get the fields li which to duplicate
					var currentRules = $(this).parent("li").parent("ul");
					var defaultRule = currentRules.children("li.hidden:first");
					var newRule = defaultRule.clone();

					newRule.children(".customRuleField").each(function(i, domElement) {
						var currentName = domElement.name.split("][");
						currentName[3] = currentName[2];
						currentName[2] = currentRules.children().size() + 1;
						domElement.name = currentName.join("][");
					});

					// remove hidden tag
					newRule.removeClass("hidden");

					// update the fields dropdown
					var optionChildren = newRule.children("select.fieldOption");
					optionChildren.empty();

					$("#Fields_fields li.EditableFormField").each(function () {
						var name = $(this).attr("id").split(' ');
						var option = $("<option></option>")
							.attr('value', name[2])
							.text($(this).find(".text").val());
						optionChildren
							.append(option);
					});

					// append to the list
					currentRules.append(newRule);
				}
			});
		});
	});
})(jQuery);