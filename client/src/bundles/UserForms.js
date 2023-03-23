/**
 * @file Manages the multi-step navigation.
 */
import Schema from 'async-validator';
import i18n from 'i18n';

const DIRTY_CLASS = 'dirty';
const FOCUSED_CLASS = 'focused';

function isVisible(element) {
  return element.style.display !== 'none'
    && element.style.visibility !== 'hidden'
    && !element.classList.contains('hide');
}

class ProgressBar {
  constructor(dom, userForm) {
    this.dom = dom;
    this.userForm = userForm;
    this.progressTitle = this.userForm.dom.querySelector('.progress-title');
    this.buttons = this.dom.querySelectorAll('.step-button-jump');
    this.currentStepNumber = this.dom.querySelector('.current-step-number');
    this.init();
  }

  init() {
    this.dom.style.display = 'initial';
    const buttons = this.buttons;
    buttons.forEach((button) => {
      button.addEventListener('click', (e) => {
        e.preventDefault();
        const stepNumber = parseInt(button.getAttribute('data-step'), 10);
        this.userForm.jumpToStep(stepNumber - 1);
        return false;
      });
    });
    this.userForm.dom.addEventListener('userform.form.changestep', (e) => {
      this.update(e.detail.stepId);
    });
    this.update(0);
  }

  update(stepId) {
    const stepNumber = this.userForm.getCurrentStepID() + 1;
    const newStep = this.userForm.getStep(stepId);
    const newStepElement = newStep.step;
    let barWidth = (stepId / (this.buttons.length - 1)) * 100;

    this.currentStepNumber.innerText = stepNumber;

    this.dom.querySelectorAll('[aria-valuenow]').forEach((e) => {
      e.setAttribute('aria-valuenow', stepNumber);
    });

    this.buttons.forEach((button) => {
      const btn = button;
      const parent = btn.parentNode;
      if (parseInt(btn.getAttribute('data-step'), 10) === stepNumber
        && isVisible(btn)) {
        parent.classList.add('current');
        parent.classList.add('viewed');

        btn.disabled = false;
      }
      parent.classList.remove('current');
    });

    this.progressTitle.innerText = newStepElement.getAttribute('data-title');

    // Update the width of the progress bar.
    barWidth = barWidth ? `${barWidth}%` : '';
    this.dom.querySelector('.progress-bar').style.width = barWidth;
  }
}

class FormStep {
  constructor(step, userForm) {
    this.step = step;
    this.userForm = userForm;
    this.viewed = false;
    this.buttonHolder = null;
    this.id = 0;

    this.init();
  }

  init() {
    const id = this.getHTMLId();
    this.buttonHolder = document.querySelector(`.step-button-wrapper[data-for='${id}']`);
    ['userform.field.hide', 'userform.field.show'].forEach((action) => {
      this.buttonHolder.addEventListener(action, () => {
        this.userForm.dom.trigger('userform.form.conditionalstep');
      });
    });
  }

  setId(id) {
    this.id = id;
  }

  getHTMLId() {
    return this.step.getAttribute('id');
  }

  show() {
    this.step.setAttribute('aria-hidden', false);
    this.step.classList.remove('hide');
    this.step.classList.add('viewed');
    this.viewed = true;
  }

  hide() {
    this.step.setAttribute('aria-hidden', true);
    this.step.classList.add('hide');
  }

  conditionallyHidden() {
    const button = this.buttonHolder.querySelector('button');
    return !(button.style.display !== 'none' && button.visibility !== 'hidden' && !button.classList.contains('hide'));
  }


  getValidatorType(input) {
    if (input.getAttribute('type') === 'email') {
      return 'email';
    }
    if (input.getAttribute('type') === 'date') {
      return 'date';
    }
    if (input.classList.contains('numeric') || input.getAttribute('type') === 'numeric') {
      return 'number';
    }
    return 'string';
  }

  getValidatorMessage(input) {
    if (input.getAttribute('data-msg-required')) {
      return input.getAttribute('data-msg-required');
    }
    return `${this.getFieldLabel(input)} is required`;
  }

  getHolderForField(input) {
    return window.closest(input, '.field');
  }

  getFieldLabel(input) {
    const holder = this.getHolderForField(input);
    if (holder) {
      const label = holder.querySelector('label.left, legend.left');
      if (label) {
        return label.innerText;
      }
    }
    return input.getAttribute('name');
  }

  getValidationsDescriptors(onlyDirty) {
    const descriptors = {};
    const fields = this.step.querySelectorAll('input, textarea, select');

    fields.forEach((field) => {
      if (isVisible(field) && (!onlyDirty || (onlyDirty && field.classList.contains(FOCUSED_CLASS)))) {
        const label = this.getFieldLabel(field);
        const holder = this.getHolderForField(field);

        descriptors[field.getAttribute('name')] = {
          title: label,
          type: this.getValidatorType(field),
          required: holder.classList.contains('requiredField'),
          message: this.getValidatorMessage(field)
        };

        const min = field.getAttribute('data-rule-min');
        const max = field.getAttribute('data-rule-max');
        if (min !== null || max !== null) {
          descriptors[field.getAttribute('name')].asyncValidator = function numericValidator(rule, value) {
            return new Promise((resolve, reject) => {
              if (min !== null && value < min) {
                reject(`${label} cannot be less than ${min}`);
              } else if (max !== null && value > max) {
                reject(`${label} cannot be greater than ${max}`);
              } else {
                resolve();
              }
            });
          };
        }

        const minL = field.getAttribute('data-rule-minlength');
        const maxL = field.getAttribute('data-rule-maxlength');
        if (minL !== null || maxL !== null) {
          descriptors[field.getAttribute('name')].asyncValidator = function lengthValidator(rule, value) {
            return new Promise((resolve, reject) => {
              if (minL !== null && value.length < minL) {
                reject(`${label} cannot be shorter than ${minL}`);
              } else if (maxL !== null && value.length > maxL) {
                reject(`${label} cannot be longer than ${maxL}`);
              } else {
                resolve();
              }
            });
          };
        }
      }
    });

    return descriptors;
  }

  validate(onlyDirty) {
    const descriptors = this.getValidationsDescriptors(onlyDirty);
    if (Object.keys(descriptors).length) {
      const validator = new Schema(descriptors);

      const formData = new FormData(this.userForm.dom);
      const data = {};
      formData.forEach((value, key) => {
        data[key] = value;
      });

      // now check for unselected checkboxes and radio buttons
      const selectableFields = this.step.querySelectorAll('input[type="radio"],input[type="checkbox"]');
      selectableFields.forEach((selectableField) => {
        const fieldName = selectableField.getAttribute('name');
        if (typeof data[fieldName] === 'undefined') {
          data[fieldName] = '';
        }
      });

      const promise = new Promise((resolve, reject) => {
        validator.validate(data, (errors) => {
          if (errors && errors.length) {
            this.displayErrorMessages(errors);
            reject(errors);
          } else {
            this.displayErrorMessages([]);
            resolve();
          }
        });
      });
      return promise;
    }

    const promise = new Promise((resolve) => {
      resolve();
    });
    return promise;
  }

  enableLiveValidation() {
    const fields = this.step.querySelectorAll('input, textarea, select');
    fields.forEach((field) => {
      field.addEventListener('focusin', () => {
        field.classList.add(FOCUSED_CLASS);
      });

      field.addEventListener('change', () => {
        field.classList.add(DIRTY_CLASS);
      });

      field.addEventListener('focusout', () => {
        this.validate(true).then(() => {
        }).catch(() => {
        });
      });
    });
  }

  displayErrorMessages(errors) {
    const errorIds = [];

    errors.forEach((error) => {
      const fieldHolder = this.userForm.dom.querySelector(`#${error.field}`);
      if (fieldHolder) {
        let errorLabel = fieldHolder.querySelector('span.error');
        if (!errorLabel) {
          errorLabel = document.createElement('span');
          errorLabel.classList.add('error');
          errorLabel.setAttribute('data-id', error.field);
        }
        errorIds.push(error.field);
        errorLabel.innerHTML = error.message;
        fieldHolder.append(errorLabel);
      }
    });

    // remove any thats not required
    const messages = this.step.querySelectorAll('span.error');

    messages.forEach((mesasge) => {
      const id = mesasge.getAttribute('data-id');
      if (errorIds.indexOf(id) === -1) {
        mesasge.remove();
      }
    });
  }
}

class FormActions {
  constructor(dom, userForm) {
    this.dom = dom;
    this.userForm = userForm;
    this.prevButton = dom.querySelector('.step-button-prev');
    this.nextButton = dom.querySelector('.step-button-next');

    this.init();
  }

  init() {
    this.prevButton.addEventListener('click', (e) => {
      e.preventDefault();
      // scrollUpFx();
      window.triggerDispatchEvent(this.userForm.dom, 'userform.action.prev');
    });
    this.nextButton.addEventListener('click', (e) => {
      e.preventDefault();
      // scrollUpFx();
      window.triggerDispatchEvent(this.userForm.dom, 'userform.action.next');
    });

    this.update();

    this.userForm.dom.addEventListener('userform.form.changestep', () => {
      this.update();
    });

    this.userForm.dom.addEventListener('userform.form.conditionalstep', () => {
      this.update();
    });
  }

  update() {
    const numberOfSteps = this.userForm.getNumberOfSteps();
    const stepId = this.userForm.getCurrentStepID();
    let i = null;
    let lastStep = null;
    for (i = numberOfSteps - 1; i >= 0; i--) {
      lastStep = this.userForm.getStep(i);
      if (!lastStep.conditionallyHidden()) {
        if (stepId >= i) {
          this.nextButton.parentNode.classList.add('hide');
        } else {
          this.nextButton.parentNode.classList.remove('hide');
        }

        if (stepId > 0 && stepId <= i) {
          this.prevButton.parentNode.classList.remove('hide');
        } else {
          this.prevButton.parentNode.classList.add('hide');
        }

        if (stepId >= i) {
          this.dom.querySelector('.btn-toolbar').classList.remove('hide');
        } else {
          this.dom.querySelector('.btn-toolbar').classList.add('hide');
        }

        break;
      }
    }
  }
}

class UserForm {
  constructor(form) {
    this.dom = form;
    this.CONSTANTS = {}; // Settings that come from the CMS.
    this.steps = [];
    this.progressBar = null;
    this.actions = null;
    this.currentStep = null;

    this.CONSTANTS.ENABLE_LIVE_VALIDATION = this.dom.getAttribute('livevalidation') !== undefined;
    this.CONSTANTS.DISPLAY_ERROR_MESSAGES_AT_TOP = this.dom.getAttribute('toperrors') !== undefined;
    this.CONSTANTS.ENABLE_ARE_YOU_SURE = this.dom.getAttribute('enableareyousure') !== undefined;
  }

  init() {
    this.initialiseFormSteps();

    if (this.CONSTANTS.ENABLE_ARE_YOU_SURE) {
      this.initAreYouSure();
    }
  }

  initialiseFormSteps() {
    const steps = this.dom.querySelectorAll('.form-step');

    steps.forEach((stepDom) => {
      const step = new FormStep(stepDom, this);
      step.hide();
      this.addStep(step);
      if (this.CONSTANTS.ENABLE_LIVE_VALIDATION) {
        step.enableLiveValidation();
      }
    });

    this.setCurrentStep(this.steps[0]);

    const progressBarDom = this.dom.querySelector('.userform-progress');
    if (progressBarDom) {
      this.progressBar = new ProgressBar(progressBarDom, this);
    }

    const stepNavigation = this.dom.querySelector('.step-navigation');
    if (stepNavigation) {
      this.formActions = new FormActions(stepNavigation, this);
      this.formActions.update();
    }

    this.setUpPing();

    this.dom.addEventListener('userform.action.next', () => {
      this.nextStep();
    });

    this.dom.addEventListener('userform.action.prev', () => {
      this.prevStep();
    });

    this.dom.addEventListener('submit', (e) => {
      this.validateForm(e);
    });
  }


  validateForm(e) {
    e.preventDefault();
    this.currentStep.validate()
      .then((errors) => {
        if (!errors) {
          this.dom.submit();
        }
      })
      .catch(() => {});
  }

  setCurrentStep(step) {
    // Make sure we're dealing with a form step.
    if (!(step instanceof FormStep)) {
      return;
    }
    this.currentStep = step;
    this.currentStep.show();
  }

  addStep(step) {
    if (!(step instanceof FormStep)) {
      return;
    }
    step.setId(this.steps.length);
    this.steps.push(step);
  }

  getNumberOfSteps() {
    return this.steps.length;
  }

  getCurrentStepID() {
    return this.currentStep.id ? this.currentStep.id : 0;
  }

  getStep(index) {
    return this.steps[index];
  }

  nextStep() {
    this.currentStep.validate().then(() => {
      this.jumpToStep(this.steps.indexOf(this.currentStep) + 1, true);
    }).catch(() => {});
  }

  prevStep() {
    this.jumpToStep(this.steps.indexOf(this.currentStep) - 1, true);
  }

  jumpToStep(stepNumber, direction) {
    const targetStep = this.steps[stepNumber];
    const forward = direction === undefined ? true : direction;

    if (targetStep === undefined) {
      return;
    }

    if (targetStep.conditionallyHidden()) {
      if (forward) {
        this.jumpToStep(stepNumber + 1, direction);
      } else {
        this.jumpToStep(stepNumber - 1, direction);
      }
      return;
    }

    if (this.currentStep) {
      this.currentStep.hide();
    }

    this.setCurrentStep(targetStep);

    window.triggerDispatchEvent(this.dom, 'userform.form.changestep', {
      stepId: targetStep.id
    });
  }

  setUpPing() {
    // Make sure the form doesn't expire on the user. Pings every 3 mins.
    window.setInterval(() => {
      fetch('UserDefinedFormController/ping');
    }, 180 * 1000);
  }

  initAreYouSure() {
    window.addEventListener('beforeunload', (e) => {
      const dirtyFields = this.dom.querySelectorAll(`.${DIRTY_CLASS}`);
      if (dirtyFields.length === 0) {
        return true;
      }
      if (navigator.userAgent.toLowerCase().match(/msie|chrome/)) {
        if (window.hasUserFormsPropted) {
          return;
        }
        window.hasUserFormsPropted = true;
        window.setTimeout(function() {window.hasUserFormsPropted = false;}, 900);
      }
      e.preventDefault();
      event.returnValue = i18n._t('UserForms.LEAVE_CONFIRMATION', 'You have unsaved changes!');
    });
  }
}


document.addEventListener('DOMContentLoaded', () => {
  const forms = document.querySelectorAll('form.userform');
  forms.forEach((form) => {
    const userForm = new UserForm(form);
    userForm.init();
  });
});
