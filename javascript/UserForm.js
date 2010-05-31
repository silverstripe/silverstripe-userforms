/**
 * Javascript required to power the user defined forms.
 * 
 * Rewritten and refactored from the prototype version FieldEditor. 
 */
(function($) {
	$(document).ready(function() {
		
		/*--------------------- SUBMISSIONS ------------------------ */
		
		/**
		 * Delete a given Submission from the form, or all submissions
		 * we let the href of the delete link to do all the work for us
		 */
		
		$("#FormSubmissions .deleteSubmission").click(function() {
			var deletedSubmission = $(this);
			$.post($(this).attr('href'), function(data) {
				deletedSubmission.parents('div.report').fadeOut();
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
			if($("#Fields").hasClass('readonly')) return false;
			
			// Give the user some feedback
			statusMessage(ss.i18n._t('UserForms.ADDINGNEWFIELD', 'Adding New Field'));
			
			// variables
			var action = $("#Form_EditForm").attr("action") + '/field/Fields/addfield';
			var length = $(".FieldInfo").length + 1;
			var securityID = ($("#SecurityID").length > 0) ? '&SecurityID='+$("#SecurityID").attr("value") : '';
			var type = $(this).siblings("select").val();
	
			//send ajax request to the page
			$.ajax({
				type: "GET",
				url: action,
				data: 'NewID='+ length +"&Type="+ type + securityID,
				
				// create a new field
				success: function(msg){
					$('#Fields_fields').append(msg);
					statusMessage(ss.i18n._t('UserForms.ADDEDNEWFIELD', 'Added New Field'));
					
					//update the internal lists
					var name = $("#Fields_fields li.EditableFormField:last").attr("id").split(' ');

					$("#Fields_fields select.fieldOption").append("<option value='"+ name[2] +"'>New "+ name[2] + "</option>");
				},
				
				// error creating new field
				error: function(request, text, error) {
					statusMessage(ss.i18n._t('UserForms.ERRORCREATINGFIELD', 'Error Creating Field'));
				} 
			});
			
			$("#Fields_fields").sortable("refresh");
	
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
				if($(domElement).val() == name[2]) {
					$(domElement).text(value);	
				}
			});
		})
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
						$(this).html(ss.i18n._t('UserForms.HIDEOPTIONS', 'Hide Options'));
						$(this).addClass("showing");
						extraOptions.removeClass('hidden').show();
					}
					else {
						$(this).html(ss.i18n._t('UserForms.SHOWOPTIONS', 'Show Options'));
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
			$("#Fields_fields .customRules select.fieldOption option").each(function(i, domElement) {
				if($(domElement).text() == text) {
					
					// check to see if this is selected. If it is then just remove the whole rule
					if($(domElement).parent('select.customRuleField').val() == $(domElement).val()) {
						$(domElement).parents('li.customRule').remove();
					}
					// otherwise remove the option
					else {
						$(domElement).remove();	
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
			statusMessage(ss.i18n._t('UserForms.ADDINGNEWFIELD', 'Adding New Option'));
			
			// variables
			var options = $(this).parent("li");
			var action = $("#Form_EditForm").attr("action") + '/field/Fields/addoptionfield';
			var parent = $(this).attr("rel");
			
			//send ajax request to the page
			$.ajax({
				type: "GET",
				url: action,
				data: 'Parent='+ parent,
				
				// create a new field
				success: function(msg){
					options.before(msg);
					statusMessage(ss.i18n._t('UserForms.ADDEDNEWFIELD', 'Added New Field'));
				},
				
				// error creating new field
				error: function(request, text, error) {
					statusMessage(ss.i18n._t('UserForms.ERRORCREATINGFIELD', 'Error Creating Field'));
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
			statusMessage(ss.i18n._t('UserForms.REMOVINGOPTION', 'Removed Option'));
			return false;
		});
		
		
		/**
		 * Sort Fields in the Field List
		 */
		$("#Fields_fields").sortable({ 
  	 		handle : '.fieldHandler',
			cursor: 'pointer',
			items: 'li.EditableFormField',
			placeholder: 'removed-form-field',
			opacity: 0.6,
			revert: true,
			change : function (event, ui) {
				$("#Fields_fields").sortable('refreshPositions');
			},
	    	update : function (event, ui) {
	      		// get all the fields
				var sort = 1;
				$("li.EditableFormField").each(function() {
					$(this).find(".sortHidden").val(sort++);
				});
	    	}
		});
		
		/**
		 * Sort Options in a Field List - Such as options in a 
		 * dropdown field.
		 */
		$(".editableOptions").sortable({
			handle : '.handle',
			cursor: 'pointer',
			items: 'li',
			placeholder: 'removed-form-field',
			opacity: 0.6,
			revert: true,
			change : function (event, ui) {
				$(this).sortable('refreshPositions');
			},
	    	update : function (event, ui) {
	      		// get all the fields
				var sort = 1;
				$(".editableOptions li").each(function() {
					$(this).find(".sortOptionHidden").val(sort++);
				});
	    	}
		});
		
		/**
		 * Custom Rules Interface
		 */
		$(".customRules .conditionOption").live('change', function(){
			var valueInput = $(this).siblings(".ruleValue");
			if($(this).val() != "" && $(this).val() != "IsBlank" && $(this).val() != "IsNotBlank") {
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
		$(".customRules .addCondition").click(function() {
			// Give the user some feedback
			statusMessage(ss.i18n._t('UserForms.ADDINGNEWRULE', 'Adding New Rule'));
			
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
			
			$(".editableOptions").sortable("refresh");
			
			return false;
		});
	});
})
(jQuery);