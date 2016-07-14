/**
 * @file Manages the multi-step navigation.
 */
jQuery(function ($) {

	// A reference to the UserForm instance.
	var userform = null;

	// Settings that come from the CMS.
	var CONSTANTS = {};

	// Common functions that extend multiple classes.
	var commonMixin = {
		/**
		 * @func show
		 * @desc Show the form step. Looks after aria attributes too.
		 */
		show: function () {
			this.$el.attr('aria-hidden', false).show();
		},
		/**
		 * @func hide
		 * @desc Hide the form step. Looks after aria attributes too.
		 */
		hide: function () {
			this.$el.attr('aria-hidden', true).hide();
		}
	};

	/**
	 * @func UserForm
	 * @constructor
	 * @param {object} element
	 * @return {object} - The UserForm instance.
	 * @desc The form
	 */
	function UserForm(element) {
		var self = this;

		this.$el = element instanceof jQuery ? element : $(element);
		this.steps = [];

		// Add an error container which displays a list of invalid steps on form submission.
		this.errorContainer = new ErrorContainer(this.$el.children('.error-container'));

		// Listen for events triggered by form steps.
		this.$el.on('userform.action.prev', function (e) {
			self.prevStep();
		});
		this.$el.on('userform.action.next', function (e) {
			self.nextStep();
		});

		// Listen for events triggered by the progress bar.
		$('#userform-progress').on('userform.progress.changestep', function (e, stepNumber) {
			self.jumpToStep(stepNumber - 1);
		});

		// When a field becomes valid, remove errors from the error container.
		this.$el.on('userform.form.valid', function (e, fieldId) {
			self.errorContainer.removeStepLink(fieldId);
		});

		this.$el.validate(this.validationOptions);

		// Ensure checkbox groups are validated correctly
		$('.optionset.requiredField input').each(function() {
		    $(this).rules('add', {
		        required: true
		    });
		});

		return this;
	}

	/*
	 * Default options for step validation. These get extended in main().
	 */
	UserForm.prototype.validationOptions = {
		ignore: ':hidden',
		errorClass: 'error',
		errorElement: 'span',
		errorPlacement: function (error, element) {
			error.addClass('message');

			if (element.is(':radio') || element.parents('.checkboxset').length > 0) {
				error.insertAfter(element.closest('ul'));
			} else if (element.parents('.checkbox').length > 0) {
				error.insertAfter(element.next('label'));
			} else {
				error.insertAfter(element);
			}
		},
		invalidHandler: function (event, validator) {
			//setTimeout 0 so it runs after errorPlacement
			setTimeout(function () {
				validator.currentElements.filter('.error').first().focus();
			}, 0);
		},
		// Callback for handling the actual submit when the form is valid.
		// Submission in the jQuery.validate sence is handled at step level.
		// So when the final step is submitted we have to also check all previous steps are valid.
		submitHandler: function (form, e) {
			var isValid = true;

			// validate the current step
			if(userform.currentStep) {
				userform.currentStep.valid = $(form).valid();
			}

			// Check for invalid previous steps.
			$.each(userform.steps, function (i, step) {
				if (!step.valid && !step.conditionallyHidden()) {
					isValid = false;
					userform.errorContainer.addStepLink(step);
				}
			});

			if (isValid) {
				form.submit();
			} else {
				userform.errorContainer.show();
			}
		},
		// When a field becomes valid.
		success: function (error) {
			var errorId = $(error).attr('id'),
				fieldId = errorId.substr(0, errorId.indexOf('-error')).replace(/[\\[\\]]/, '');

			// Remove square brackets since jQuery.validate.js uses idOrName,
			// which breaks further on when using a selector that end with
			// square brackets.

			error.remove();

			// Pass the field's ID with the event.
			userform.$el.trigger('userform.form.valid', [fieldId]);
		}
	};

	/**
	 * @func UserForm.addStep
	 * @param {object} step - An instance of FormStep.
	 * @desc Adds a step to the UserForm.
	 */
	UserForm.prototype.addStep = function (step) {
		// Make sure we're dealing with a form step.
		if (!step instanceof FormStep) {
			return;
		}

		step.id = this.steps.length;

		this.steps.push(step);
	};

	/**
	 * @func UserForm.setCurrentStep
	 * @param {object} step - An instance of FormStep.
	 * @desc Sets the step the user is currently on.
	 */
	UserForm.prototype.setCurrentStep = function (step) {
		// Make sure we're dealing with a form step.
		if (!(step instanceof FormStep)) {
			return;
		}

		this.currentStep = step;
		this.currentStep.show();

		// Record the user has viewed the step.
		step.viewed = true;
		step.$el.addClass('viewed');
	};

	/**
	 * @func UserForm.jumpToStep
	 * @param {number} stepNumber
	 * @param {boolean} [direction] - Defaults to forward (true).
	 * @desc Jumps to a specific form step.
	 */
	UserForm.prototype.jumpToStep = function (stepNumber, direction) {
		var targetStep = this.steps[stepNumber],
			isValid = false,
			forward = direction === void 0 ? true : direction;

		// Make sure the target step exists.
		if (targetStep === void 0) {
			return;
		}

		// Make sure the step we're trying to set as current is not
		// hidden by custom display rules. If it is then jump to the next step.
		if (targetStep.conditionallyHidden()) {
			if (forward) {
				this.jumpToStep(stepNumber + 1);
			} else {
				this.jumpToStep(stepNumber - 1);
			}

			return;
		}

		// Validate the form.
		// This well effectivly validate the current step and not the entire form.
		// This is because hidden fields are excluded from validation, and all fields
		// on all other steps, are currently hidden.
		isValid = this.$el.valid();

		// Set the 'valid' property on the current step.
		this.currentStep.valid = isValid;

		// Users can navigate to step's they've already viewed even if the current step is invalid.
		if (isValid === false && targetStep.viewed === false) {
			return;
		}

		this.currentStep.hide();
		this.setCurrentStep(targetStep);

		this.$el.trigger('userform.form.changestep', [targetStep.id]);
	};

	/**
	 * @func UserForm.nextStep
	 * @desc Advances the form to the next step.
	 */
	UserForm.prototype.nextStep = function () {
		this.jumpToStep(this.steps.indexOf(this.currentStep) + 1, true);
	};

	/**
	 * @func UserForm.prevStep
	 * @desc Goes back one step (not bound to browser history).
	 */
	UserForm.prototype.prevStep = function () {
		this.jumpToStep(this.steps.indexOf(this.currentStep) - 1, false);
	};

	/**
	 * @func ErrorContainer
	 * @constructor
	 * @param {object} element - The error container element.
	 * @return {object} - The ErrorContainer instance.
	 * @desc Creates an error container. Used to display step error messages at the top.
	 */
	function ErrorContainer(element) {
		this.$el = element instanceof jQuery ? element : $(element);

		// Set the error container's heading.
		this.$el.find('h4').text(ss.i18n._t('UserForms.ERROR_CONTAINER_HEADER', 'Please correct the following errors and try again:'));

		return this;
	}

	/**
	 * @func hasErrors
	 * @return boolean
	 * @desc Checks if the error container has any error messages.
	 */
	ErrorContainer.prototype.hasErrors = function () {
		return this.$el.find('.error-list').children().length > 0;
	};

	/**
	 * @func removeErrorMessage
	 * @desc Removes an error message from the error container.
	 */
	ErrorContainer.prototype.removeErrorMessage = function (fieldId) {
		this.$el.find('#' + fieldId + '-top-error').remove();

		// If there are no more error then hide the container.
		if (!this.hasErrors()) {
			this.hide();
		}
	};

	/**
	 * @func addStepLink
	 * @param {object} step - FormStep instance.
	 * @desc Adds a link to a form step as an error message.
	 */
	ErrorContainer.prototype.addStepLink = function (step) {
		var self = this,
			itemID = step.$el.attr('id') + '-error-link',
			$itemElement = this.$el.find('#' + itemID),
			stepID = step.$el.attr('id'),
			stepTitle = step.$el.data('title');

		// If the item already exists we don't need to do anything.
		if ($itemElement.length) {
			return;
		}

		$itemElement = $('<li id="' + itemID + '"><a href="#' + stepID + '">' + stepTitle + '</a></li>');

		$itemElement.on('click', function (e) {
			e.preventDefault();
			userform.jumpToStep(step.id);
		});

		this.$el.find('.error-list').append($itemElement);
	};

	/**
	 * @func removeStepLink
	 * @param {object} step - FormStep instance.
	 * @desc Removes a step link from the error container.
	 */
	ErrorContainer.prototype.removeStepLink = function (fieldId) {
		var stepID = $('#' + fieldId).closest('.form-step').attr('id');

		this.$el.find('#' + stepID + '-error-link').remove();

		// Hide the error container if we've just removed the last error.
		if (this.$el.find('.error-list').is(':empty')) {
			this.hide();
		}
	};

	/**
	 * @func ErrorContainer.updateErrorMessage
	 * @param {object} $input - The jQuery input object which contains the field to validate.
	 * @param {object} message - The error message to display (html escaped).
	 * @desc Update an error message (displayed at the top of the form).
	 */
	ErrorContainer.prototype.updateErrorMessage = function ($input, message) {
		var inputID = $input.attr('id'),
			anchor = '#' + inputID,
			elementID = inputID + '-top-error',
			messageElement = $('#' + elementID),
			describedBy = $input.attr('aria-describedby');

		// The 'message' param will be an empty string if the field is valid.
		if (!message) {
			// Style issues as fixed if they already exist
			messageElement.addClass('fixed');
			return;
		}

		messageElement.removeClass('fixed');

		this.show();

		if (messageElement.length === 1) {
			// Update the existing error message.
			messageElement.show().find('a').html(message);
		} else {
			// Generate better link to field
			$input.closest('.field[id]').each(function(){
				anchor = '#' + $(this).attr('id');
			});

			// Add a new error message
			messageElement = $('<li><a></a></li>');
			messageElement
				.attr('id', elementID)
				.find('a')
					.attr('href', location.pathname + location.search + anchor)
					.html(message);

			this.$el.find('ul').append(messageElement);

			// link back to original input via aria
			// Respect existing non-error aria-describedby
			if (!describedBy) {
				describedBy = elementID;
			} else if (!describedBy.match(new RegExp('\\b' + elementID + '\\b'))) {
				// Add to end of list if not already present
				describedBy += " " + elementID;
			}

			$input.attr('aria-describedby', describedBy);
		}
	};

	/**
	 * @func FormStep
	 * @constructor
	 * @param {object} element
	 * @return {object} - The FormStep instance.
	 * @desc Creates a form step.
	 */
	function FormStep(element) {
		var self = this;

		this.$el = element instanceof jQuery ? element : $(element);
		
		// Find button for this step
		this.$elButton = $(".step-button-wrapper[data-for='" + this.$el.prop('id') + "']");
		
		// Has the step been viewed by the user?
		this.viewed = false;

		// Is the form step valid?
		// This value is used on form submission, which fails, if any of the steps are invalid.
		this.valid = false;

		// The internal id of the step. Used for getting the step from the UserForm.steps array.
		this.id = null;

		this.hide();

		if (CONSTANTS.DISPLAY_ERROR_MESSAGES_AT_TOP) {
			this.errorContainer = new ErrorContainer(this.$el.find('.error-container'));

			// Listen for errors on the UserForm.
			userform.$el.on('userform.form.error', function (e, validator) {
				// The step only cares about errors if it's currently visible.
				if (!self.$el.is(':visible')) {
					return;
				}

				// Add or update each error in the list.
				$.each(validator.errorList, function (i, error) {
					self.errorContainer.updateErrorMessage($(error.element), error.message);
				});
			});

			// Listen for fields becoming valid
			userform.$el.on('userform.form.valid', function (e, fieldId) {
				self.errorContainer.removeErrorMessage(fieldId);
			});
		}

		// Ensure that page visibilty updates the step navigation
		this
			.$elButton
			.on('userform.field.hide userform.field.show', function(){
				userform.$el.trigger('userform.form.conditionalstep');
			});

		return this;
	}
	
	/**
	 * Determine if this step is conditionally disabled
	 * 
	 * @returns {Boolean}
	 */
	FormStep.prototype.conditionallyHidden = function(){
		// Because the element itself could be visible but 0 height, so check visibility of button
		return ! this
			.$elButton
			.find('button')
			.is(':visible');
	};

	/**
	 * @func ProgressBar
	 * @constructor
	 * @param {object} element
	 * @return {object} - The Progress bar instance.
	 * @desc Creates a progress bar.
	 */
	function ProgressBar(element) {
		var self = this;

		this.$el = element instanceof jQuery ? element : $(element);
		this.$buttons = this.$el.find('.step-button-jump');
		this.$jsAlign = this.$el.find('.js-align');

		// Update the progress bar when 'step' buttons are clicked.
		this.$buttons.each(function (i, stepButton) {
			$(stepButton).on('click', function (e) {
				e.preventDefault();
				self.$el.trigger('userform.progress.changestep', [parseInt($(this).data('step'), 10)]);
			});
		});

		// Update the progress bar when 'prev' and 'next' buttons are clicked.
		userform.$el.on('userform.form.changestep', function (e, stepID) {
			self.update(stepID);
		});

		// Listen for steps being conditionally shown / hidden by display rules.
		// We need to update step related UI like the number of step buttons
		// and any text that shows the total number of steps.
		userform.$el.on('userform.form.conditionalstep', function () {
			// Update the step numbers on the buttons.
			var $visibleButtons = self.$buttons.filter(':visible');

			$visibleButtons.each(function (i, button) {
				$(button).text(i + 1);
			});

			// Update the actual progress bar.
			self.$el.find('.progress-bar').attr('aria-valuemax', $visibleButtons.length);

			// Update any text that uses the total number of steps.
			self.$el.find('.total-step-number').text($visibleButtons.length);
		});

		// Spaces out the steps below progress bar evenly
		this.$jsAlign.each(function (index, button) {
			var $button = $(button),
				leftPercent = (100 / (self.$jsAlign.length - 1) * index + '%'),
				buttonOffset = -1 * ($button.innerWidth() / 2);

			$button.css({left: leftPercent, marginLeft: buttonOffset});
			
			// First and last buttons are kept within userform-progress container
			if (index === self.$jsAlign.length - 1) {
				$button.css({marginLeft: buttonOffset * 2});
			} else if (index === 0) {
				$button.css({marginLeft: 0});
			}
		});

		this.update(0);

		return this;
	}

	/**
	 * @func ProgressBar.update
	 * @param {number} stepID - Zero based index of the new step.
	 * @desc Update the progress element to show a new step.
	 */
	ProgressBar.prototype.update = function (stepID) {
		var $newStepElement = $($('.form-step')[stepID]),
			stepNumber = 0,
			barWidth = stepID / (this.$buttons.length - 1) * 100;

		// Set the current step number.
		this.$buttons.each(function (i, button) {
			if (i > stepID) {
				return false; // break the loop
			}

			if ($(button).is(':visible')) {
				stepNumber += 1;
			}
		});

		// Update elements that contain the current step number.
		this.$el.find('.current-step-number').each(function (i, element) {
			$(element).text(stepNumber);
		});

		// Update aria attributes.
		this.$el.find('[aria-valuenow]').each(function (i, element) {
			$(element).attr('aria-valuenow', stepNumber);
		});

		// Update the CSS classes on step buttons.
		this.$buttons.each(function (i, element) {
			var $element = $(element),
				$item = $element.parent();

			if (parseInt($element.data('step'), 10) === stepNumber && $element.is(':visible')) {
				$item.addClass('current viewed');
				$element.removeAttr('disabled');

				return;
			}

			$item.removeClass('current');
		});

		// Update the progress bar's title with the new step's title.
		this.$el.siblings('.progress-title').text($newStepElement.data('title'));

		// Update the width of the progress bar.
		barWidth = barWidth ? barWidth + '%' : '';
		this.$el.find('.progress-bar').width(barWidth);
	};

	/**
	 * @func FormActions
	 * @constructor
	 * @param {object} element
	 * @desc Creates the navigation and actions (Prev, Next, Submit buttons).
	 */
	function FormActions (element) {
		var self = this;

		this.$el = element instanceof jQuery ? element : $(element);

		this.$prevButton = this.$el.find('.step-button-prev');
		this.$nextButton = this.$el.find('.step-button-next');

		// Show the buttons.
		this.$prevButton.parent().attr('aria-hidden', false).show();
		this.$nextButton.parent().attr('aria-hidden', false).show();

		// Bind the step navigation event listeners.
		this.$prevButton.on('click', function (e) {
			e.preventDefault();
			self.$el.trigger('userform.action.prev');
		});
		this.$nextButton.on('click', function (e) {
			e.preventDefault();
			self.$el.trigger('userform.action.next');
		});

		// Listen for changes to the current form step, or conditional pages,
		// so we can show hide buttons appropriatly.
		userform.$el.on('userform.form.changestep userform.form.conditionalstep', function () {
			self.update();
		});

		this.update();

		return this;
	}

	/**
	 * @func FormAcrions.update
	 * @param {number} stepID - Zero based ID of the current step.
	 * @desc Updates the form actions element to reflect the current state of the page.
	 */
	FormActions.prototype.update = function () {
		var numberOfSteps = userform.steps.length,
			stepID = userform.currentStep ? userform.currentStep.id : 0,
			i, lastStep;

		// Update the "Prev" button.
		this.$el.find('.step-button-prev')[stepID === 0 ? 'hide' : 'show']();
		
		// Find last step, skipping hidden ones
		for(i = numberOfSteps - 1; i >= 0; i--) {
			lastStep = userform.steps[i];
			
			// Skip if step is hidden
			if(lastStep.conditionallyHidden()) {
				continue;
			}

			// Update the "Next" button.
			this.$el.find('.step-button-next')[stepID >= i ? 'hide' : 'show']();

			// Update the "Actions".
			this.$el.find('.Actions')[stepID >= i ? 'show' : 'hide']();
			
			// Stop processing last step
			break;
		}
	};

	/**
	 * @func main
	 * @desc Bootstraps the front-end.
	 */
	function main() {
		var progressBar = null,
			formActions = null,
			$userform = $('.userform');

		// If there's no userform, do nothing.
		if ($userform.length === 0) {
			return;
		}

		CONSTANTS.ENABLE_LIVE_VALIDATION = $userform.data('livevalidation') !== void 0;
		CONSTANTS.DISPLAY_ERROR_MESSAGES_AT_TOP = $userform.data('toperrors') !== void 0;
		CONSTANTS.HIDE_FIELD_LABELS = $userform.data('hidefieldlabels') !== void 0;

		// Extend the default validation options with conditional options
		// that are set by the user in the CMS.
		if (CONSTANTS.ENABLE_LIVE_VALIDATION === false) {
			$.extend(UserForm.prototype.validationOptions, {
				onfocusout: false
			});
		}

		if (CONSTANTS.DISPLAY_ERROR_MESSAGES_AT_TOP) {
			$.extend(UserForm.prototype.validationOptions, {
				// Callback for custom code when an invalid form / step is submitted.
				invalidHandler: function (event, validator) {
					$userform.trigger('userform.form.error', [validator]);
				},
				onfocusout: false
			});
		}

		// Display all the things that are hidden when JavaScript is disabled.
		$('.userform-progress, .step-navigation').attr('aria-hidden', false).show();

		// Extend classes with common functionality.
		$.extend(FormStep.prototype, commonMixin);
		$.extend(ErrorContainer.prototype, commonMixin);

		userform = new UserForm($userform);

		// Conditionally hide field labels and use HTML5 placeholder instead.
		if (CONSTANTS.HIDE_FIELD_LABELS) {
			$userform.find('label.left').each(function () {
				var $label = $(this);

				$('[name="' + $label.attr('for') + '"]').attr('placeholder', $label.text());
				$label.remove();
			});
		}

		// Initialise the form steps.
		userform.$el.find('.form-step').each(function (i, element) {
			var step = new FormStep(element);

			userform.addStep(step);
		});

		userform.setCurrentStep(userform.steps[0]);
		
		// Initialise actions and progressbar
		progressBar = new ProgressBar($('#userform-progress'));
		formActions = new FormActions($('#step-navigation'));

		// Enable jQuery UI datepickers
		$(document).on('click', 'input.text[data-showcalendar]', function() {
			var $element = $(this);

			$element.ssDatepicker();

			if($element.data('datepicker')) {
				$element.datepicker('show');
			}
		});

		// Make sure the form doesn't expire on the user. Pings every 3 mins.
		setInterval(function () {
			$.ajax({ url: 'UserDefinedForm_Controller/ping' });
		}, 180 * 1000);

		// Bind a confirmation message when navigating away from a partially completed form.
		var form = $('form.userform');
		if(typeof form.areYouSure != 'undefined') {
			form.areYouSure({
				message: ss.i18n._t('UserForms.LEAVE_CONFIRMATION', 'You have unsaved changes!')
			});
		}
	}

	main();
});
