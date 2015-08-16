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

		// Listen for events triggered my form steps.
		this.$el.on('userform.step.prev', function (e) {
			self.prevStep();
		});
		this.$el.on('userform.step.next', function (e) {
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

			if(element.is(':radio') || element.parents('.checkboxset').length > 0) {
				error.insertAfter(element.closest('ul'));
			} else {
				error.insertAfter(element);
			}
		},
		// Callback for handling the actual submit when the form is valid.
		// Submission in the jQuery.validate sence is handled at step level.
		// So when the final step is submitted we have to also check all previous steps are valid.
		submitHandler: function (form, e) {
			var isValid = true;

			// Validate the final step.
			userform.steps[userform.steps.length - 1].valid = $(form).valid();

			// Check for invalid previous steps.
			$.each(userform.steps, function (i, step) {
				if (!step.valid) {
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
			var errorId = $(error).attr('id');

			error.remove();

			// Pass the field's ID with the event.
			userform.$el.trigger('userform.form.valid', [errorId.substr(0, errorId.indexOf('-error'))]);
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
	 * @desc Jumps to a specific form step.
	 */
	UserForm.prototype.jumpToStep = function (stepNumber) {
		var targetStep = this.steps[stepNumber],
			isValid = false;

		// Make sure the target step exists.
		if (targetStep === void 0) {
			return;
		}

		// Validate the form.
		// This well effectivly validate the current step and not the entire form.
		// This is because hidden fields are excluded from validation, and all fields
		// on all other steps, are currently hidden.
		isValid = this.$el.valid();

		// Set the 'valid' property on the current step.
		this.steps[stepNumber - 1 >= 0 ? stepNumber - 1 : 0].valid = isValid;

		// Users can navigate to step's they've already viewed even if the current step is invalid.
		if (isValid === false && targetStep.viewed === false) {
			return;
		}

		this.currentStep.hide();
		this.setCurrentStep(targetStep);

		this.$el.trigger('userform.form.changestep', [stepNumber]);
	};

	/**
	 * @func UserForm.nextStep
	 * @desc Advances the form to the next step.
	 */
	UserForm.prototype.nextStep = function () {
		this.jumpToStep(this.steps.indexOf(this.currentStep) + 1);
	};

	/**
	 * @func UserForm.prevStep
	 * @desc Goes back one step (not bound to browser history).
	 */
	UserForm.prototype.prevStep = function () {
		this.jumpToStep(this.steps.indexOf(this.currentStep) - 1);
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

		// Has the step been viewed by the user?
		this.viewed = false;

		// Is the form step valid?
		// This value is used on form submission, which fails, if any of the steps are invalid.
		this.valid = false;

		// The internal id of the step. Used for getting the step from the UserForm.steps array.
		this.id = null;

		this.hide();

		// Bind the step navigation event listeners.
		this.$el.find('.step-button-prev').on('click', function (e) {
			e.preventDefault();
			self.$el.trigger('userform.step.prev');
		});
		this.$el.find('.step-button-next').on('click', function (e) {
			e.preventDefault();
			self.$el.trigger('userform.step.next');
		});

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

		return this;
	}

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
				self.$el.trigger('userform.progress.changestep', [parseInt($(this).text(), 10)]);
			});
		});

		// Update the progress bar when 'prev' and 'next' buttons are clicked.
		userform.$el.on('userform.form.changestep', function (e, newStep) {
			self.update(newStep + 1);
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

		this.update(1);

		return this;
	}

	/**
	 * @func ProgressBar.update
	 * @param {number} newStep
	 * @desc Update the progress element to show a new step.
	 */
	ProgressBar.prototype.update = function (newStep) {
		var $newStepElement = $($('.form-step')[newStep - 1]);

		// Update elements that contain the current step number.
		this.$el.find('.current-step-number').each(function (i, element) {
			$(element).text(newStep);
		});

		// Update aria attributes.
		this.$el.find('[aria-valuenow]').each(function (i, element) {
			$(element).attr('aria-valuenow', newStep);
		});

		// Update the CSS classes on step buttons.
		this.$buttons.each(function (i, element) {
			var $element = $(element),
				$item = $element.parent();

			if (parseInt($element.text(), 10) === newStep) {
				$item.addClass('current viewed');
				$element.removeAttr('disabled');

				return;
			}

			$item.removeClass('current');
		});

		// Update the progress bar's title with the new step's title.
		this.$el.find('.progress-title').text($newStepElement.data('title'));

		// Update the width of the progress bar.
		this.$el.find('.progress-bar').width((newStep - 1) / (this.$buttons.length - 1) * 100 + '%');
	};

	/**
	 * @func main
	 * @desc Bootstraps the front-end.
	 */
	function main() {
		var progressBar = null,
			$userform = $('.userform');

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
		progressBar = new ProgressBar($('#userform-progress'));

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

		// Hide the form-wide actions on multi-step forms.
		// Because JavaScript is enabled we'll use the actions contained
		// in the final step's navigation.
		if (userform.steps.length > 1) {
			userform.$el.children('.Actions').attr('aria-hidden', true).hide();
		}

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
	}

	main();
});
