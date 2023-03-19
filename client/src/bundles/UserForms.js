/**
 * @file Manages the multi-step navigation.
 */

import i18n from 'i18n';

document.addEventListener("DOMContentLoaded", () => {

  const forms = document.querySelectorAll('form.userform');
  for (const form of forms) {
    const userForm = new UserForm(form);
    userForm.init();
  }

});


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
    for (const button of buttons) {
      button.addEventListener('click', (e) => {
        e.preventDefault();
        const stepNumber = parseInt(button.getAttribute('data-step'), 10);
        this.userForm.jumpToStep(stepNumber);
        return false;
      });
    }

    this.userForm.dom.addEventListener('userform.form.changestep', (e) => {
      this.update(e.detail.stepId);
    });

    this.update(0)

  }

  isVisible(element) {
    return !(element.style.display !== 'none' && element.style.visibility !== 'hidden' && element.classList.contains('hide'))
  }

  update(stepId) {
    let stepNumber = this.userForm.getCurrentStepID() + 1;
    let newStep = this.userForm.getStep(stepId);
    let newStepElement = newStep.step;
    let barWidth = (stepId / (this.buttons.length - 1)) * 100;

    this.currentStepNumber.innerText = stepNumber;

    for (const e of this.dom.querySelectorAll('[aria-valuenow]')) {
      e.setAttribute('aria-valuenow', stepNumber);
    }

    for (const button of this.buttons) {
      const parent = button.parentNode;
      if (parseInt(button.getAttribute('data-step'), 10) === stepNumber
          && this.isVisible(button)) {
        parent.classList.add('current');
        parent.classList.add('viewed');

        button.disabled = false; // .removeAttribute('disabled');
        break;
      }
      parent.classList.remove('current');

    }


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
        this.userForm.dom.trigger('userform.form.conditionalstep')
      });
    })
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
    return !(button.style.display !== 'none' && button.visibility !== 'hidden' && button.classList.contains('hide'))
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
    const stepID = this.userForm.getCurrentStepID();
    let i = null;
    let lastStep = null;
    for (i = numberOfSteps - 1; i >= 0; i--) {
      lastStep = this.userForm.getStep(i);
      if (!lastStep.conditionallyHidden()) {
        if (stepID >= i) {
          this.nextButton.parentNode.classList.add('hide');
          this.prevButton.parentNode.classList.remove('hide');
        } else {
          this.nextButton.parentNode.classList.remove('hide');
          this.prevButton.parentNode.classList.add('hide');
        }

        if (stepID >= i) {
          this.dom.querySelector('.btn-toolbar').classList.remove('hide');
        } else {
          this.dom.querySelector('.btn-toolbar').classList.add('hide');
        }

        // this.userForm.dom.querySelector('.btn-toolbar');
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
  }

  init() {
    this.initialiseFormSteps();
  }

  initialiseFormSteps() {
    const steps = this.dom.querySelectorAll('.form-step');
    for (const stepDom of steps) {
      const step = new FormStep(stepDom, this);
      step.hide();
      this.addStep(step);
    }

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
    })

    this.dom.addEventListener('userform.action.prev', () => {
      this.prevStep();
    })

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
    this.jumpToStep(this.steps.indexOf(this.currentStep) + 1, true);
  }

  prevStep() {
    this.jumpToStep(this.steps.indexOf(this.currentStep) - 1, true);
  }

  jumpToStep(stepNumber, direction)
  {
    const targetStep = this.steps[stepNumber];
    let isValid = false;
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

}

