/* global i18n, jQuery */

jQuery(document).ready(($) => {
  // Settings that come from the CMS.
  const CONSTANTS = {};

  // Common functions that extend multiple classes.
  const commonMixin = {
    /**
     * @func show
     * @desc Show the form step. Looks after aria attributes too.
     */
    show() {
      this.$el.attr('aria-hidden', false).show();
    },
    /**
     * @func hide
     * @desc Hide the form step. Looks after aria attributes too.
     */
    hide() {
      this.$el.attr('aria-hidden', true).hide();
    },
  };

  /**
   * @func ErrorContainer
   * @constructor
   * @param {object} element - The error container element.
   * @return {object} - The ErrorContainer instance.
   * @desc Creates an error container. Used to display step error messages at the top.
   */
  function ErrorContainer(element) {
    this.$el = element instanceof $ ? element : $(element);

    // Set the error container's heading.
    this.$el.find('h4').text(i18n._t('UserForms.ERROR_CONTAINER_HEADER',
      'Please correct the following errors and try again:'));

    return this;
  }

  /**
   * @func hasErrors
   * @return boolean
   * @desc Checks if the error container has any error messages.
   */
  ErrorContainer.prototype.hasErrors = function hasErrors() {
    return this.$el.find('.error-list').children().length > 0;
  };

  /**
   * @func removeErrorMessage
   * @desc Removes an error message from the error container.
   */
  ErrorContainer.prototype.removeErrorMessage = function removeErrorMessage(fieldId) {
    this.$el.find(`#${fieldId}-top-error`).remove();

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
  ErrorContainer.prototype.addStepLink = function addStepLink(step) {
    const userform = this.$el.closest('.userform').data('inst');
    const itemID = `${step.$el.attr('id')}-error-link`;
    let $itemElement = this.$el.find(`#${itemID}`);
    const stepID = step.$el.attr('id');
    const stepTitle = step.$el.data('title');

    // If the item already exists we don't need to do anything.
    if ($itemElement.length) {
      return;
    }

    $itemElement = $(`<li id="${itemID}"><a href="#${stepID}">${stepTitle}</a></li>`);

    $itemElement.on('click', (e) => {
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
  ErrorContainer.prototype.removeStepLink = function removeStepLink(fieldId) {
    const stepID = $(`#${fieldId}`).closest('.form-step').attr('id');

    this.$el.find(`#${stepID}-error-link`).remove();

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
  ErrorContainer.prototype.updateErrorMessage = function updateErrorMessage($input, message) {
    const inputID = $input.attr('id');
    let anchor = `#${inputID}`;
    const elementID = `${inputID}-top-error`;
    let messageElement = $(`#${elementID}`);
    let describedBy = $input.attr('aria-describedby');

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
      $input.closest('.field[id]').each(() => {
        const anchorID = $(this).attr('id');

        if (!anchorID) {
          return;
        }

        anchor = `#${anchorID}`;
      });

      // Add a new error message
      messageElement = $('<li><a></a></li>');
      messageElement
        .attr('id', elementID)
        .find('a')
        .attr('href', location.pathname + location.search + anchor)
        .html(message);

      this.$el.find('ul').append(messageElement);

      // Link back to original input via aria
      // Respect existing non-error aria-describedby
      if (!describedBy) {
        describedBy = elementID;
      } else if (!describedBy.match(new RegExp(`\\b${elementID}\\b`))) {
        // Add to end of list if not already present
        describedBy += ` ${elementID}`;
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
    const self = this;

    this.$el = element instanceof $ ? element : $(element);

    const userform = this.$el.closest('.userform').data('inst');

    // Find button for this step
    this.$elButton = $(`.step-button-wrapper[data-for='${this.$el.prop('id')}']`);

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
      userform.$el.on('userform.form.error', (e, validator) => {
        // The step only cares about errors if it's currently visible.
        if (!self.$el.is(':visible')) {
          return;
        }

        // Add or update each error in the list.
        $.each(validator.errorList, (i, error) => {
          self.errorContainer.updateErrorMessage($(error.element), error.message);
        });
      });

      // Listen for fields becoming valid
      userform.$el.on('userform.form.valid', (e, fieldId) => {
        self.errorContainer.removeErrorMessage(fieldId);
      });
    }

    // Ensure that page visibilty updates the step navigation
    this
      .$elButton
      .on('userform.field.hide userform.field.show', () => {
        userform.$el.trigger('userform.form.conditionalstep');
      });

    return this;
  }

  /**
   * Determine if this step is conditionally disabled
   *
   * @returns {Boolean}
   */
  // Because the element itself could be visible but 0 height, so check visibility of button
  FormStep.prototype.conditionallyHidden = function conditionallyHidden() {
    return !this.$elButton.find('button').is(':visible');
  };

  /**
   * @func ProgressBar
   * @constructor
   * @param {object} element
   * @return {object} - The Progress bar instance.
   * @desc Creates a progress bar.
   */
  function ProgressBar(element) {
    const self = this;

    this.$el = element instanceof $ ? element : $(element);
    this.$buttons = this.$el.find('.step-button-jump');
    this.$jsAlign = this.$el.find('.js-align');
    const userform = this.$el.closest('.userform').data('inst');

    // Update the progress bar when 'step' buttons are clicked.
    this.$buttons.each((i, stepButton) => {
      $(stepButton).on('click', (e) => {
        e.preventDefault();
        const stepNumber = parseInt($(e.target).data('step'), 10);
        self.$el.trigger('userform.progress.changestep', stepNumber);
      });
    });

    // Update the progress bar when 'prev' and 'next' buttons are clicked.
    userform.$el.on('userform.form.changestep', (e, stepID) => {
      self.update(stepID);
    });

    // Listen for steps being conditionally shown / hidden by display rules.
    // We need to update step related UI like the number of step buttons
    // and any text that shows the total number of steps.
    userform.$el.on('userform.form.conditionalstep', () => {
      // Update the step numbers on the buttons.
      const $visibleButtons = self.$buttons.filter(':visible');

      $visibleButtons.each((i, button) => {
        $(button).text(i + 1);
      });

      // Update the actual progress bar.
      self.$el.find('.progress-bar').attr('aria-valuemax', $visibleButtons.length);

      // Update any text that uses the total number of steps.
      self.$el.find('.total-step-number').text($visibleButtons.length);
    });

    // Spaces out the steps below progress bar evenly
    this.$jsAlign.each((index, button) => {
      const $button = $(button);
      const leftPercent = (100 / (self.$jsAlign.length - 1)) * index;
      const leftPercentCssValue = `${leftPercent}%`;
      const buttonOffset = -1 * ($button.innerWidth() / 2);

      $button.css({
        left: leftPercentCssValue,
        marginLeft: buttonOffset,
      });

      // First and last buttons are kept within userform-progress container
      if (index === self.$jsAlign.length - 1) {
        $button.css({ marginLeft: buttonOffset * 2 });
      } else if (index === 0) {
        $button.css({ marginLeft: 0 });
      }
    });

    return this;
  }

  /**
   * @func ProgressBar.update
   * @param {number} stepID - Zero based index of the new step.
   * @desc Update the progress element to show a new step.
   */
  ProgressBar.prototype.update = function update(stepID) {
    const $newStepElement = $(this.$el.parent('.userform').find('.form-step')[stepID]);
    let stepNumber = 0;
    let barWidth = (stepID / (this.$buttons.length - 1)) * 100;

    // Set the current step number.
    this.$buttons.each((i, button) => {
      if (i > stepID) {
        // Break the loop
        return false;
      }

      if ($(button).is(':visible')) {
        stepNumber += 1;
      }
      return true;
    });

    // Update elements that contain the current step number.
    this.$el.find('.current-step-number').each((i, element) => {
      $(element).text(stepNumber);
    });

    // Update aria attributes.
    this.$el.find('[aria-valuenow]').each((i, element) => {
      $(element).attr('aria-valuenow', stepNumber);
    });

    // Update the CSS classes on step buttons.
    this.$buttons.each((i, element) => {
      const $element = $(element);
      const $item = $element.parent();

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
    barWidth = barWidth ? `${barWidth}%` : '';
    this.$el.find('.progress-bar').width(barWidth);
  };

  /**
   * @func FormActions
   * @constructor
   * @param {object} element
   * @desc Creates the navigation and actions (Prev, Next, Submit buttons).
   */
  function FormActions(element) {
    const self = this;

    this.$el = element instanceof $ ? element : $(element);
    const $elFormItself = this.$el.closest('.userform');

    this.userformInstance = $elFormItself.data('inst');

    this.$prevButton = this.$el.find('.step-button-prev');
    this.$nextButton = this.$el.find('.step-button-next');

    // Show the buttons.
    this.$prevButton.parent().attr('aria-hidden', false).show();
    this.$nextButton.parent().attr('aria-hidden', false).show();

    // Scroll up to the next page...
    const scrollUpFx = function () {
      const scrollTop = $elFormItself.offset();
      $('html, body').animate({ scrollTop: scrollTop.top }, 'slow');
    };

    // Bind the step navigation event listeners.
    this.$prevButton.on('click', (e) => {
      e.preventDefault();
      scrollUpFx();
      self.$el.trigger('userform.action.prev');
    });
    this.$nextButton.on('click', (e) => {
      e.preventDefault();
      scrollUpFx();
      self.$el.trigger('userform.action.next');
    });

    // Listen for changes to the current form step, or conditional pages,
    // so we can show hide buttons appropriately.
    this.userformInstance.$el.on('userform.form.changestep userform.form.conditionalstep', () => {
      self.update();
    });

    return this;
  }

  /**
   * @func FormActions.update
   * @param {number} stepID - Zero based ID of the current step.
   * @desc Updates the form actions element to reflect the current state of the page.
   */
  FormActions.prototype.update = function update() {
    const numberOfSteps = this.userformInstance.steps.length;
    const stepID = this.userformInstance.currentStep ? this.userformInstance.currentStep.id : 0;
    let i = null;
    let lastStep = null;

    // Update the "Prev" button.
    this.$el.find('.step-button-prev')[stepID === 0 ? 'hide' : 'show']();

    // Find last step, skipping hidden ones
    for (i = numberOfSteps - 1; i >= 0; i--) {
      lastStep = this.userformInstance.steps[i];

      // Skip if step is hidden
      if (!lastStep.conditionallyHidden()) {
        // Update the "Next" button.
        this.$el.find('.step-button-next')[stepID >= i ? 'hide' : 'show']();

        // Update the "Actions".
        this.$el.find('.btn-toolbar')[stepID >= i ? 'show' : 'hide']();

        // Stop processing last step
        break;
      }
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
    const self = this;

    this.$el = element instanceof $ ? element : $(element);
    this.steps = [];

    // Add an error container which displays a list of invalid steps on form submission.
    this.errorContainer = new ErrorContainer(this.$el.children('.error-container'));

    // Listen for events triggered by form steps.
    this.$el.on('userform.action.prev', () => {
      self.prevStep();
    });
    this.$el.on('userform.action.next', () => {
      self.nextStep();
    });

    // Listen for events triggered by the progress bar.
    this.$el.find('.userform-progress').on('userform.progress.changestep', (e, stepNumber) => {
      self.jumpToStep(stepNumber - 1);
    });

    // When a field becomes valid, remove errors from the error container.
    this.$el.on('userform.form.valid', (e, fieldId) => {
      self.errorContainer.removeStepLink(fieldId);
    });

    this.$el.validate(this.validationOptions);

    // Ensure checkbox groups are validated correctly
    this.$el.find('.optionset.requiredField input').each((a, field) => {
      $(field).rules('add', {
        required: true,
      });
    });

    return this;
  }

  /*
   * Default options for step validation. These get extended in main().
   */
  UserForm.prototype.validationOptions = {
    ignore: ':hidden,ul',
    errorClass: 'error',
    errorElement: 'span',
    errorPlacement: (error, element) => {
      error.addClass('message');

      if (element.is(':radio') || element.parents('.checkboxset').length > 0) {
        error.appendTo(element.closest('.middleColumn, .field'));
      } else if (element.parents('.checkbox').length > 0) {
        error.appendTo(element.closest('.field'));
      } else {
        error.insertAfter(element);
      }
    },
    invalidHandler: (event, validator) => {
      // setTimeout 0 so it runs after errorPlacement
      setTimeout(() => {
        validator.currentElements.filter('.error').first().focus();
      }, 0);
    },
    // Callback for handling the actual submit when the form is valid.
    // Submission in the jQuery.validate sence is handled at step level.
    // So when the final step is submitted we have to also check all previous steps are valid.
    submitHandler: (form) => {
      let isValid = true;
      const userform = $(form).closest('.userform').data('inst');

      // Validate the current step
      if (userform.currentStep) {
        userform.currentStep.valid = $(form).valid();
      }

      // Check for invalid previous steps.
      $.each(userform.steps, (i, step) => {
        if (!step.valid && !step.conditionallyHidden()) {
          isValid = false;
          userform.errorContainer.addStepLink(step);
        }
      });

      if (isValid) {
        // Remove required attributes on hidden fields
        const hiddenInputs = $(form).find('.field.requiredField.hide input');
        if (hiddenInputs.length > 0) {
          hiddenInputs.removeAttr('required aria-required data-rule-required').valid();
        }

        // When using the "are you sure?" plugin, ensure the form immediately submits.
        $(form).removeClass('dirty');

        form.submit();
        userform.$el.trigger('userform.form.submit');
      } else {
        userform.errorContainer.show();
      }
    },
    // When a field becomes valid.
    success: (error) => {
      const userform = $(error).closest('.userform').data('inst');
      const errorId = $(error).attr('id');
      const fieldId = errorId.substr(0, errorId.indexOf('-error')).replace(/[\\[\\]]/, '');

      // Remove square brackets since jQuery.validate.js uses idOrName,
      // which breaks further on when using a selector that end with
      // square brackets.

      error.remove();

      // Pass the field's ID with the event
      userform.$el.trigger('userform.form.valid', [fieldId]);
    },
  };

  /**
   * @func UserForm.addStep
   * @param {object} step - An instance of FormStep.
   * @desc Adds a step to the UserForm.
   */
  UserForm.prototype.addStep = function addStep(step) {
    // Make sure we're dealing with a form step.
    if (!(step instanceof FormStep)) {
      return;
    }

    // eslint-disable-next-line no-param-reassign
    step.id = this.steps.length;

    this.steps.push(step);
  };

  /**
   * @func UserForm.setCurrentStep
   * @param {object} step - An instance of FormStep.
   * @desc Sets the step the user is currently on.
   */
  UserForm.prototype.setCurrentStep = function setCurrentStep(step) {
    // Make sure we're dealing with a form step.
    if (!(step instanceof FormStep)) {
      return;
    }

    this.currentStep = step;
    this.currentStep.show();

    // Record the user has viewed the step.
    this.currentStep.viewed = true;
    this.currentStep.$el.addClass('viewed');
  };

  /**
   * @func UserForm.jumpToStep
   * @param {number} stepNumber
   * @param {boolean} [direction] - Defaults to forward (true).
   * @desc Jumps to a specific form step.
   */
  UserForm.prototype.jumpToStep = function jumpToStep(stepNumber, direction) {
    const targetStep = this.steps[stepNumber];
    let isValid = false;
    const forward = direction === undefined ? true : direction;

    // Make sure the target step exists.
    if (targetStep === undefined) {
      return;
    }

    // Make sure the step we're trying to set as current is not
    // hidden by custom display rules. If it is then jump to the next step.
    if (targetStep.conditionallyHidden()) {
      if (forward) {
        this.jumpToStep(stepNumber + 1, direction);
      } else {
        this.jumpToStep(stepNumber - 1, direction);
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
  UserForm.prototype.nextStep = function nextStep() {
    this.jumpToStep(this.steps.indexOf(this.currentStep) + 1, true);
  };

  /**
   * @func UserForm.prevStep
   * @desc Goes back one step (not bound to browser history).
   */
  UserForm.prototype.prevStep = function prevStep() {
    this.jumpToStep(this.steps.indexOf(this.currentStep) - 1, false);
  };

  /**
   * @func main
   * @desc Bootstraps the front-end.
   */
  function main(index, userformElement) {
    const $userform = $(userformElement);

    // If there's no userform, do nothing.
    if ($userform.length === 0) {
      return;
    }

    CONSTANTS.ENABLE_LIVE_VALIDATION = $userform.data('livevalidation') !== undefined;
    CONSTANTS.DISPLAY_ERROR_MESSAGES_AT_TOP = $userform.data('toperrors') !== undefined;

    // Extend the default validation options with conditional options
    // that are set by the user in the CMS.
    if (CONSTANTS.ENABLE_LIVE_VALIDATION === false) {
      $.extend(UserForm.prototype.validationOptions, {
        onfocusout: false,
      });
    }

    if (CONSTANTS.DISPLAY_ERROR_MESSAGES_AT_TOP) {
      $.extend(UserForm.prototype.validationOptions, {
        // Callback for custom code when an invalid form / step is submitted.
        invalidHandler: (event, validator) => {
          $userform.trigger('userform.form.error', [validator]);
        },
        onfocusout: false,
      });
    }

    // Display all the things that are hidden when JavaScript is disabled.
    $userform.find('.userform-progress, .step-navigation').attr('aria-hidden', false).show();

    // Extend classes with common functionality.
    $.extend(FormStep.prototype, commonMixin);
    $.extend(ErrorContainer.prototype, commonMixin);

    const userform = new UserForm($userform);
    $userform.data('inst', userform);

    // Conditionally hide field labels and use HTML5 placeholder instead.
    if (CONSTANTS.HIDE_FIELD_LABELS) {
      $userform.find('label.left').each(() => {
        const $label = $(this);

        $(`[name="${$label.attr('for')}"]`).attr('placeholder', $label.text());
        $label.remove();
      });
    }

    // Initialise the form steps.
    userform.$el.find('.form-step').each((i, element) => {
      const step = new FormStep(element);

      userform.addStep(step);
    });

    userform.setCurrentStep(userform.steps[0]);

    // Initialise actions and progressbar
    const $progressEl = $userform.find('.userform-progress');
    if ($progressEl.length) {
      const progressBar = new ProgressBar($progressEl);
      progressBar.update(0);
    }

    const $formActionsEl = $userform.find('.step-navigation');
    if ($formActionsEl.length) {
      const formActions = new FormActions($formActionsEl);
      formActions.update();
    }

    // Enable jQuery UI datepickers
    $(document).on('click', 'input.text[data-showcalendar]', () => {
      const $element = $(this);

      $element.ssDatepicker();

      if ($element.data('datepicker')) {
        $element.datepicker('show');
      }
    });

    // Make sure the form doesn't expire on the user. Pings every 3 mins.
    setInterval(() => {
      $.ajax({ url: 'UserDefinedFormController/ping' });
    }, 180 * 1000);

    // Bind a confirmation message when navigating away from a partially completed form.
    if (typeof $userform.areYouSure !== 'undefined') {
      $userform.areYouSure({
        message: i18n._t('UserForms.LEAVE_CONFIRMATION', 'You have unsaved changes!'),
      });
    }
  }

  $('.userform').each(main);
});
