/**
 * Javascript required to power the user defined forms.
 * 
 * Rewritten and refactored from the prototype version FieldEditor. 
 *
 * @todo Upgrade to jQuery 1.3 so we can use live rather
 * 			then livequery
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
		
		$("div.FieldEditor ul.Menu li a").livequery('click',function() {
			
			// if this form is readonly...
			if($("#Fields").hasClass('readonly')) return false;
			
			// Give the user some feedback
			statusMessage(ss.i18n._t('UserForms.ADDINGNEWFIELD', 'Adding New Field'));
			
			// variables
			var action = $("#Form_EditForm").attr("action") + '/field/Fields/addfield';
			var length = $(".FieldInfo").length + 1;
			var securityID = ($("#SecurityID").length > 0) ? '&SecurityID='+$("#SecurityID").attr("value") : '';
			var type = $(this).attr("ID");
			
			//send ajax request to the page
			$.ajax({
				type: "GET",
				url: action,
				data: 'NewID='+ length +"&Type="+ type + securityID,
				
				// create a new field
				success: function(msg){
					$('#Fields_fields').append(msg);
					statusMessage(ss.i18n._t('UserForms.ADDEDNEWFIELD', 'Added New Field'));
				},
				
				// error creating new field
				error: function(request, text, error) {
					statusMessage(ss.i18n._t('UserForms.ERRORCREATINGFIELD', 'Error Creating Field'));
				} 
			});
		});
		
		/**
		 * Show the more options popdown. Or hide it if we 
		 * currently have it open
		 */
		$(".EditableFormField .moreOptions").livequery('click',function() {
			
			var parentID = $(this).parents(".EditableFormField");
			if(parentID) {
				var extraOptions = parentID.children(".extraOptions");
				if(extraOptions) {
					if(extraOptions.hasClass('hidden')) {
						$(this).html("Hide More Options");
						$(this).addClass("showing");
						extraOptions.removeClass('hidden').show();
					}
					else {
						$(this).html("More Options");
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
		$(".EditableFormField .delete").livequery('click', function() {
			$(this).parents(".EditableFormField").remove();
			return false;
		});
		
		/**
		 * Add a suboption to a radio field or to a dropdown box 
		 * for example
		 */
		$(".EditableFormField .addableOption").livequery('click', function() {
			
			// Give the user some feedback
			statusMessage(ss.i18n._t('UserForms.ADDINGNEWFIELD', 'Adding New Option'));
			
			// variables
			var options = $(this).parent("li");
			var action = $("#Form_EditForm").attr("action") + '/field/Fields/addoptionfield';
			var parent = $(this).attr("rel");
			var text = $(this).parents("li").children(".text").val();
			
			// clear input
			$(this).parents("li").children(".text").val("");
			
			//send ajax request to the page
			$.ajax({
				type: "GET",
				url: action,
				data: 'Parent='+ parent +"&Text="+ text,
				
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
		$(".EditableFormField .deleteOption").livequery('click', function() {
			// pass the deleted status onto the element
			$(this).parents("li").children("[type=text]").attr("value", "field-node-deleted");
			$(this).parents("li").hide();
			
			// Give the user some feedback
			statusMessage(ss.i18n._t('UserForms.REMOVINGOPTION', 'Removed Option'));
			return false;
		});
		
		
		/**
		 * Sort Fields in the Field List
		 */
		$("#Fields_fields").livequery(function() {
			$(this).sortable({ 
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
		});
		
		/**
		 * Sort Options in a Field List - Such as options in a 
		 * dropdown field.
		 */
		$(".editableOptions").livequery(function() {
			$(this).sortable({
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
		});
	});
})
(jQuery);