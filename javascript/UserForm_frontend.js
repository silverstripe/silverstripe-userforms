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
		this.$el.on('userform.progress.jump', function (e, stepNumber) {
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
		this.$el = element instanceof jQuery ? element : $(element);

		// Bind the step navigation event listeners.
		this.$el.find('.step-button-prev').on('click', function (e) {
			e.preventDefault();
			$(this).closest('.userform').trigger('userform.step.prev');
		});
		this.$el.find('.step-button-next').on('click', function (e) {
			e.preventDefault();
			$(this).closest('.userform').trigger('userform.step.next');
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
		this.$el = element instanceof jQuery ? element : $(element);
		this.buttons = [];

		// Trigger events when the user clicks step buttons.
		this.$el.find('.step-button-jump').each(function (i, stepButton) {
			$(stepButton).on('click', function (e) {
				e.preventDefault();
				$('.userform').trigger('userform.progress.jump', [$(this).text()]);
			});
		});

		return this;
	}

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
