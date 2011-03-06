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
		
		/**
		 * Workaround for not refreshing the sort
		 */
		$(".fieldHandler").live('hover', function() {
			userforms.update();
		});
		
		/**
		 * Kick off the UserForms UI
		 */
		userforms.update();
		
		
		/*--------------------- SUBMISSIONS ------------------------ */
		
		/**
		 * Delete a given Submission from the form
		 */
		$("#userforms-submissions .deleteSubmission").live('click', function(event) {
			event.preventDefault();
			
			var deletedSubmission = $(this);
			$.post($(this).attr('href'), function(data) {
				deletedSubmission.parents('div.userform-submission').fadeOut();
			});

			return false;
		});

		/**
		 * Delete all submissions and fade them out if successful
		 */
		$("#userforms-submissions .deleteAllSubmissions").live('click', function(event) {
			event.preventDefault();

			if(!confirm(userforms.message('CONFIRM_DELETE_ALL_SUBMISSIONS'))) {
				return false;
			}

			var self = this;
			$.post($(this).attr('href'), function(data) {
				$(self).parents('#userforms-submissions').children().fadeOut();
			});

			return false;
		});
		
		/*-------------------- FIELD EDITOR ----------------------- */
		
		/**
		 * Create a new instance of a field in the current form 
		 * area. the type information should all be on this object
		 */
		
		$("div.FieldEditor .MenuHolder .action").live('click',function() {

			// if this form is readonly...
			if($("#Fields").hasClass('readonly')) {
				return false;
			}
			
			// Give the user some feedback
			statusMessage(userforms.message('ADDING_FIELD'));
			
			// variables
			var action = $("#Form_EditForm").attr("action") + '/field/Fields/addfield';
			var length = $(".FieldInfo").length + 1;
			var securityID = ($("#SecurityID").length > 0) ? '&SecurityID='+$("#SecurityID").attr("value") : '';
			var type = $(this).siblings("select").val();
	
			// send ajax request to the page
			$.ajax({
				type: "GET",
				url: action,
				data: 'NewID='+ length +"&Type="+ type + securityID,
				
				// create a new field
				success: function(msg){
					$('#Fields_fields').append(msg);
					statusMessage(userforms.message('ADDED_FIELD'));
					
					// update the internal lists
					var name = $("#Fields_fields li.EditableFormField:last").attr("id").split(' ');

					$("#Fields_fields select.fieldOption").append("<option value='"+ name[2] +"'>New "+ name[2] + "</option>");
				},
				
				// error creating new field
				error: function(request, text, error) {
					statusMessage(userforms.message('ERROR_CREATING_FIELD'));
				} 
			});
			
			$("#Fields_fields").sortable('refresh');
	
			return false;
		});
		
		/** 
		 * Upon renaming a field we should go through and rename all the
		 * fields in the select fields to use this new field title. We can
		 * just worry about the title text - don't mess around with the keys
		 */
		$('.EditableFormField .fieldInfo .text').live('change', function() {
			var value = $(this).val();
			var name = $(this).parents("li").attr("id").split(' ');
			
			$("#Fields_fields select.fieldOption option").each(function(i, domElement) {
				if($(domElement).val() === name[2]) {
					$(domElement).text(value);	
				}
			});
		});
		
		/**
		 * Show the more options popdown. Or hide it if we 
		 * currently have it open
		 */
		$(".EditableFormField .moreOptions").live('click',function() {
			var parentID = $(this).parents(".EditableFormField");
			if(parentID) {
				var extraOptions = parentID.children(".extraOptions");
				if(extraOptions) {
					if(extraOptions.hasClass('hidden')) {
						$(this).html(userforms.message('HIDE_OPTIONS'));
						$(this).addClass("showing");
						extraOptions.removeClass('hidden').show();
					}
					else {
						$(this).html(userforms.message('SHOW_OPTIONS'));
						$(this).removeClass("showing");
						extraOptions.addClass('hidden').hide();	
					}
				}
			}
			return false;
		});
		
		/**
		 * Delete a field from the user defined form
		 */
		$(".EditableFormField .delete").live('click', function() {
			// remove all the rules with relate to this field
			var text = $(this).parents("li").find(".fieldInfo .text").val();
			
			$("#Fields_fields .customRules select.fieldOption option").each(function(i, ele) {
				if($(ele).text() === text) {
					
					// check to see if this is selected. If it is then just remove the whole rule
					if($(ele).parent('select.customRuleField').val() === $(ele).val()) {
						$(ele).parents('li.customRule').remove();
					}
					else {
						// otherwise remove the option
						$(ele).remove();	
					}
				}
			});
			
			$(this).parents(".EditableFormField").remove();
			
			return false;
		});
		
		/**
		 * Add a suboption to a radio field or to a dropdown box 
		 * for example
		 */
		$(".EditableFormField .addableOption").live('click', function() {
			
			// Give the user some feedback
			statusMessage(userforms.message('ADDING_OPTION'));
			
			// variables
			var options = $(this).parent("li");
			var action = $("#Form_EditForm").attr("action") + '/field/Fields/addoptionfield';
			var parent = $(this).attr("rel");
			
			// send ajax request to the page
			$.ajax({
				type: "GET",
				url: action,
				data: 'Parent='+ parent,
				
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
			
			return false;
		});
		
		/**
		 * Delete a suboption such as an dropdown option or a 
		 * checkbox field
		 */
		$(".EditableFormField .deleteOption").live('click', function() {
			// pass the deleted status onto the element
			$(this).parent("li").children("[type=text]").attr("value", "field-node-deleted");
			$(this).parent("li").hide();
			
			// Give the user some feedback
			statusMessage(userforms.message('REMOVED_OPTION'));
			return false;
		});

		/**
		 * Custom Rules Interface
		 */
		$("body").delegate("select.conditionOption", 'change', function() {
			var valueInput = $(this).siblings(".ruleValue");
			
			if($(this).val() && $(this).val() !== "IsBlank" && $(this).val() !== "IsNotBlank") {
				valueInput.removeClass("hidden");
			}
			else {
				valueInput.addClass("hidden");
			}
		});
		
		/**
		 * Delete a custom rule
		 */
		$(".customRules .deleteCondition").live('click', function() {
			$(this).parent("li").fadeOut().remove();
			
			return false;
		});
		
		/**
		 * Adding a custom rule to a given form
		 */
		$(".customRules .addCondition").live('click', function() {
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
			newRule.children("select.fieldOption").empty();

			$("#Fields_fields li.EditableFormField").each(function (i, domElement) {
				var name = $(domElement).attr("id").split(' ');
				newRule.children("select.fieldOption").append("<option value='"+ name[2] + "'>"+ $(domElement).find(".text").val() + "</option>");
			});
			
			// append to the list
			currentRules.append(newRule);
			
			return false;
		});
	});
})(jQuery);