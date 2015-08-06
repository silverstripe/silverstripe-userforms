/**
 * @file Manages the multi-step navigation.
 */
jQuery(function ($) {

	/**
	 * @func UserForm
	 * @constructor
	 * @param object element
	 * @return object - The UserForm instance.
	 * @desc The form
	 */
	function UserForm(element) {
		var self = this;

		this.$el = element instanceof jQuery ? element : $(element);
		this.steps = [];

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

		return this;
	}

	/**
	 * @func UserForm.addStep
	 * @param object step - An instance of FormStep.
	 * @desc Adds a step to the UserForm.
	 */
	UserForm.prototype.addStep = function (step) {
		// Make sure we're dealing with a form step.
		if (!step instanceof FormStep) {
			return;
		}

		this.steps.push(step);
	};

	/**
	 * @func UserForm.setCurrentStep
	 * @param object step - An instance of FormStep.
	 * @desc Sets the step the user is currently on.
	 */
	UserForm.prototype.setCurrentStep = function (step) {
		// Make sure we're dealing with a form step.
		if (!step instanceof FormStep) {
			return;
		}

		this.currentStep = step;
		this.currentStep.show();
	};

	/**
	 * @func UserForm.jumpToStep
	 * @param number stepNumber
	 * @desc Jumps to a specific form step.
	 */
	UserForm.prototype.jumpToStep = function (stepNumber) {
		var targetStep = this.steps[stepNumber];

		// Make sure the target step exists.
		if (targetStep === void 0) {
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
	 * @func FormStep
	 * @constructor
	 * @param object element
	 * @return object - The FormStep instance.
	 * @desc Creates a form step.
	 */
	function FormStep(element) {
		var self = this;

		this.$el = element instanceof jQuery ? element : $(element);

		// Bind the step navigation event listeners.
		this.$el.find('.step-button-prev').on('click', function (e) {
			e.preventDefault();
			self.$el.trigger('userform.step.prev');
		});
		this.$el.find('.step-button-next').on('click', function (e) {
			e.preventDefault();
			self.$el.trigger('userform.step.next');
		});

		this.hide();

		return this;
	}

	/**
	 * @func FormStep.show
	 * @desc Show the form step. Looks after aria attributes too.
	 */
	FormStep.prototype.show = function () {
		this.$el.attr('aria-hidden', false).show();
	};

	/**
	 * @func FormStep.hide
	 * @desc Hide the form step. Looks after aria attributes too.
	 */
	FormStep.prototype.hide = function () {
		this.$el.attr('aria-hidden', true).hide();
	};

	/**
	 * @func ProgressBar
	 * @constructor
	 * @param object element
	 * @return object - The Progress bar instance.
	 * @desc Creates a progress bar.
	 */
	function ProgressBar(element) {
		var self = this;

		this.$el = element instanceof jQuery ? element : $(element);
		this.$buttons = this.$el.find('.step-button-jump');

		// Update the progress bar when 'step' buttons are clicked.
		this.$buttons.each(function (i, stepButton) {
			$(stepButton).on('click', function (e) {
				var newStepNumber = parseInt($(this).text(), 10);

				e.preventDefault();

				self.update(newStepNumber);
				self.$el.trigger('userform.progress.changestep', [newStepNumber]);
			});
		});

		// Update the progress bar when 'prev' and 'next' buttons are clicked.
		$('.userform').on('userform.form.changestep', function (e, newStep) {
			self.update(newStep + 1);
		});

		return this;
	}

	/**
	 * @func ProgressBar.update
	 * @param number newStep
	 * @desc Update the progress element to show a new step.
	 */
	ProgressBar.prototype.update = function (newStep) {
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
			var $item = $(element).parent();

			if (parseInt($(element).text(), 10) === newStep) {
				$item.addClass('current');
				return;
			}

			$item.removeClass('current');
		});

		// Update the width of the progress bar.
		this.$el.find('.progress-bar').width(newStep / this.$buttons.length * 100 + '%');
	};

	/**
	 * @func main
	 * @desc Bootstraps the front-end.
	 */
	function main() {
		var userform = new UserForm($('.userform')),
			progressBar = new ProgressBar($('#userform-progress'));

		// Display all the things that are hidden when JavaScript is disabled.
		$.each(['#userform-progress', '.step-navigation'], function (i, selector) {
			$(selector).attr('aria-hidden', false).show();
		});

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

		// Make sure the form doesn't expire on the user. Pings every 3 mins.
		setInterval(function () {
			$.ajax({ url: 'UserDefinedForm_Controller/ping' });
		}, 180 * 1000);
	}

	main();
});
