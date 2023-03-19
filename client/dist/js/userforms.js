/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// identity function for calling harmony imports with the correct context
/******/ 	__webpack_require__.i = function(value) { return value; };
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, {
/******/ 				configurable: false,
/******/ 				enumerable: true,
/******/ 				get: getter
/******/ 			});
/******/ 		}
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "";
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = "./client/src/bundles/bundle.js");
/******/ })
/************************************************************************/
/******/ ({

/***/ "./client/src/bundles/UserForms.js":
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

var _i18n = __webpack_require__(0);

var _i18n2 = _interopRequireDefault(_i18n);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

document.addEventListener("DOMContentLoaded", function () {

  var forms = document.querySelectorAll('form.userform');
  var _iteratorNormalCompletion = true;
  var _didIteratorError = false;
  var _iteratorError = undefined;

  try {
    for (var _iterator = forms[Symbol.iterator](), _step; !(_iteratorNormalCompletion = (_step = _iterator.next()).done); _iteratorNormalCompletion = true) {
      var form = _step.value;

      var userForm = new UserForm(form);
      userForm.init();
    }
  } catch (err) {
    _didIteratorError = true;
    _iteratorError = err;
  } finally {
    try {
      if (!_iteratorNormalCompletion && _iterator.return) {
        _iterator.return();
      }
    } finally {
      if (_didIteratorError) {
        throw _iteratorError;
      }
    }
  }
});

var ProgressBar = function () {
  function ProgressBar(dom, userForm) {
    _classCallCheck(this, ProgressBar);

    this.dom = dom;
    this.userForm = userForm;
    this.progressTitle = this.userForm.dom.querySelector('.progress-title');
    this.buttons = this.dom.querySelectorAll('.step-button-jump');
    this.currentStepNumber = this.dom.querySelector('.current-step-number');
    this.init();
  }

  _createClass(ProgressBar, [{
    key: 'init',
    value: function init() {
      var _this = this;

      this.dom.style.display = 'initial';
      var buttons = this.buttons;

      var _loop = function _loop(button) {
        button.addEventListener('click', function (e) {
          e.preventDefault();
          var stepNumber = parseInt(button.getAttribute('data-step'), 10);
          _this.userForm.jumpToStep(stepNumber);
          return false;
        });
      };

      var _iteratorNormalCompletion2 = true;
      var _didIteratorError2 = false;
      var _iteratorError2 = undefined;

      try {
        for (var _iterator2 = buttons[Symbol.iterator](), _step2; !(_iteratorNormalCompletion2 = (_step2 = _iterator2.next()).done); _iteratorNormalCompletion2 = true) {
          var button = _step2.value;

          _loop(button);
        }
      } catch (err) {
        _didIteratorError2 = true;
        _iteratorError2 = err;
      } finally {
        try {
          if (!_iteratorNormalCompletion2 && _iterator2.return) {
            _iterator2.return();
          }
        } finally {
          if (_didIteratorError2) {
            throw _iteratorError2;
          }
        }
      }

      this.userForm.dom.addEventListener('userform.form.changestep', function (e) {
        _this.update(e.detail.stepId);
      });

      this.update(0);
    }
  }, {
    key: 'isVisible',
    value: function isVisible(element) {
      return !(element.style.display !== 'none' && element.style.visibility !== 'hidden' && element.classList.contains('hide'));
    }
  }, {
    key: 'update',
    value: function update(stepId) {
      var stepNumber = this.userForm.getCurrentStepID() + 1;
      var newStep = this.userForm.getStep(stepId);
      var newStepElement = newStep.step;
      var barWidth = stepId / (this.buttons.length - 1) * 100;

      this.currentStepNumber.innerText = stepNumber;

      var _iteratorNormalCompletion3 = true;
      var _didIteratorError3 = false;
      var _iteratorError3 = undefined;

      try {
        for (var _iterator3 = this.dom.querySelectorAll('[aria-valuenow]')[Symbol.iterator](), _step3; !(_iteratorNormalCompletion3 = (_step3 = _iterator3.next()).done); _iteratorNormalCompletion3 = true) {
          var e = _step3.value;

          e.setAttribute('aria-valuenow', stepNumber);
        }
      } catch (err) {
        _didIteratorError3 = true;
        _iteratorError3 = err;
      } finally {
        try {
          if (!_iteratorNormalCompletion3 && _iterator3.return) {
            _iterator3.return();
          }
        } finally {
          if (_didIteratorError3) {
            throw _iteratorError3;
          }
        }
      }

      var _iteratorNormalCompletion4 = true;
      var _didIteratorError4 = false;
      var _iteratorError4 = undefined;

      try {
        for (var _iterator4 = this.buttons[Symbol.iterator](), _step4; !(_iteratorNormalCompletion4 = (_step4 = _iterator4.next()).done); _iteratorNormalCompletion4 = true) {
          var button = _step4.value;

          var parent = button.parentNode;
          if (parseInt(button.getAttribute('data-step'), 10) === stepNumber && this.isVisible(button)) {
            parent.classList.add('current');
            parent.classList.add('viewed');

            button.disabled = false;
            break;
          }
          parent.classList.remove('current');
        }
      } catch (err) {
        _didIteratorError4 = true;
        _iteratorError4 = err;
      } finally {
        try {
          if (!_iteratorNormalCompletion4 && _iterator4.return) {
            _iterator4.return();
          }
        } finally {
          if (_didIteratorError4) {
            throw _iteratorError4;
          }
        }
      }

      this.progressTitle.innerText = newStepElement.getAttribute('data-title');

      barWidth = barWidth ? barWidth + '%' : '';
      this.dom.querySelector('.progress-bar').style.width = barWidth;
    }
  }]);

  return ProgressBar;
}();

var FormStep = function () {
  function FormStep(step, userForm) {
    _classCallCheck(this, FormStep);

    this.step = step;
    this.userForm = userForm;
    this.viewed = false;
    this.buttonHolder = null;
    this.id = 0;

    this.init();
  }

  _createClass(FormStep, [{
    key: 'init',
    value: function init() {
      var _this2 = this;

      var id = this.getHTMLId();
      this.buttonHolder = document.querySelector('.step-button-wrapper[data-for=\'' + id + '\']');
      ['userform.field.hide', 'userform.field.show'].forEach(function (action) {
        _this2.buttonHolder.addEventListener(action, function () {
          _this2.userForm.dom.trigger('userform.form.conditionalstep');
        });
      });
    }
  }, {
    key: 'setId',
    value: function setId(id) {
      this.id = id;
    }
  }, {
    key: 'getHTMLId',
    value: function getHTMLId() {
      return this.step.getAttribute('id');
    }
  }, {
    key: 'show',
    value: function show() {
      this.step.setAttribute('aria-hidden', false);
      this.step.classList.remove('hide');
      this.step.classList.add('viewed');
      this.viewed = true;
    }
  }, {
    key: 'hide',
    value: function hide() {
      this.step.setAttribute('aria-hidden', true);
      this.step.classList.add('hide');
    }
  }, {
    key: 'conditionallyHidden',
    value: function conditionallyHidden() {
      var button = this.buttonHolder.querySelector('button');
      return !(button.style.display !== 'none' && button.visibility !== 'hidden' && button.classList.contains('hide'));
    }
  }]);

  return FormStep;
}();

var FormActions = function () {
  function FormActions(dom, userForm) {
    _classCallCheck(this, FormActions);

    this.dom = dom;
    this.userForm = userForm;
    this.prevButton = dom.querySelector('.step-button-prev');
    this.nextButton = dom.querySelector('.step-button-next');

    this.init();
  }

  _createClass(FormActions, [{
    key: 'init',
    value: function init() {
      var _this3 = this;

      this.prevButton.addEventListener('click', function (e) {
        e.preventDefault();

        window.triggerDispatchEvent(_this3.userForm.dom, 'userform.action.prev');
      });
      this.nextButton.addEventListener('click', function (e) {
        e.preventDefault();

        window.triggerDispatchEvent(_this3.userForm.dom, 'userform.action.next');
      });

      this.update();

      this.userForm.dom.addEventListener('userform.form.changestep', function () {
        _this3.update();
      });

      this.userForm.dom.addEventListener('userform.form.conditionalstep', function () {
        _this3.update();
      });
    }
  }, {
    key: 'update',
    value: function update() {
      var numberOfSteps = this.userForm.getNumberOfSteps();
      var stepID = this.userForm.getCurrentStepID();
      var i = null;
      var lastStep = null;
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

          break;
        }
      }
    }
  }]);

  return FormActions;
}();

var UserForm = function () {
  function UserForm(form) {
    _classCallCheck(this, UserForm);

    this.dom = form;
    this.CONSTANTS = {};
    this.steps = [];
    this.progressBar = null;
    this.actions = null;
    this.currentStep = null;

    this.CONSTANTS.ENABLE_LIVE_VALIDATION = this.dom.getAttribute('livevalidation') !== undefined;
    this.CONSTANTS.DISPLAY_ERROR_MESSAGES_AT_TOP = this.dom.getAttribute('toperrors') !== undefined;
  }

  _createClass(UserForm, [{
    key: 'init',
    value: function init() {
      this.initialiseFormSteps();
    }
  }, {
    key: 'initialiseFormSteps',
    value: function initialiseFormSteps() {
      var _this4 = this;

      var steps = this.dom.querySelectorAll('.form-step');
      var _iteratorNormalCompletion5 = true;
      var _didIteratorError5 = false;
      var _iteratorError5 = undefined;

      try {
        for (var _iterator5 = steps[Symbol.iterator](), _step5; !(_iteratorNormalCompletion5 = (_step5 = _iterator5.next()).done); _iteratorNormalCompletion5 = true) {
          var stepDom = _step5.value;

          var step = new FormStep(stepDom, this);
          step.hide();
          this.addStep(step);
        }
      } catch (err) {
        _didIteratorError5 = true;
        _iteratorError5 = err;
      } finally {
        try {
          if (!_iteratorNormalCompletion5 && _iterator5.return) {
            _iterator5.return();
          }
        } finally {
          if (_didIteratorError5) {
            throw _iteratorError5;
          }
        }
      }

      this.setCurrentStep(this.steps[0]);

      var progressBarDom = this.dom.querySelector('.userform-progress');
      if (progressBarDom) {
        this.progressBar = new ProgressBar(progressBarDom, this);
      }

      var stepNavigation = this.dom.querySelector('.step-navigation');
      if (stepNavigation) {
        this.formActions = new FormActions(stepNavigation, this);
        this.formActions.update();
      }

      this.setUpPing();

      this.dom.addEventListener('userform.action.next', function () {
        _this4.nextStep();
      });

      this.dom.addEventListener('userform.action.prev', function () {
        _this4.prevStep();
      });
    }
  }, {
    key: 'setCurrentStep',
    value: function setCurrentStep(step) {
      if (!(step instanceof FormStep)) {
        return;
      }
      this.currentStep = step;
      this.currentStep.show();
    }
  }, {
    key: 'addStep',
    value: function addStep(step) {
      if (!(step instanceof FormStep)) {
        return;
      }
      step.setId(this.steps.length);
      this.steps.push(step);
    }
  }, {
    key: 'getNumberOfSteps',
    value: function getNumberOfSteps() {
      return this.steps.length;
    }
  }, {
    key: 'getCurrentStepID',
    value: function getCurrentStepID() {
      return this.currentStep.id ? this.currentStep.id : 0;
    }
  }, {
    key: 'getStep',
    value: function getStep(index) {
      return this.steps[index];
    }
  }, {
    key: 'nextStep',
    value: function nextStep() {
      this.jumpToStep(this.steps.indexOf(this.currentStep) + 1, true);
    }
  }, {
    key: 'prevStep',
    value: function prevStep() {
      this.jumpToStep(this.steps.indexOf(this.currentStep) - 1, true);
    }
  }, {
    key: 'jumpToStep',
    value: function jumpToStep(stepNumber, direction) {
      var targetStep = this.steps[stepNumber];
      var isValid = false;
      var forward = direction === undefined ? true : direction;

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
  }, {
    key: 'setUpPing',
    value: function setUpPing() {
      window.setInterval(function () {
        fetch('UserDefinedFormController/ping');
      }, 180 * 1000);
    }
  }]);

  return UserForm;
}();

/***/ }),

/***/ "./client/src/bundles/bundle.js":
/***/ (function(module, exports, __webpack_require__) {

"use strict";


__webpack_require__("./client/src/bundles/UserForms.js");

/***/ }),

/***/ 0:
/***/ (function(module, exports) {

module.exports = i18n;

/***/ })

/******/ });
//# sourceMappingURL=userforms.js.map