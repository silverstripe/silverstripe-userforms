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
/******/ 	return __webpack_require__(__webpack_require__.s = 2);
/******/ })
/************************************************************************/
/******/ ([
/* 0 */
/***/ (function(module, exports) {



window.jQuery.entwine('ss', function ($) {
  var stickyHeaderInterval = null;

  $('.uf-field-editor .ss-gridfield-items').entwine({
    onmatch: function onmatch() {
      var i = 0;
      var thisLevel = 0;
      var depth = 0;
      var $buttonrow = $('.uf-field-editor .ss-gridfield-buttonrow').addClass('sticky-buttons');
      var navHeight = $('.cms-content-header.north').first().height() + parseInt($('.sticky-buttons').css('padding-top'), 10);
      var fieldEditor = $('.uf-field-editor');

      this._super();

      this.find('.ss-gridfield-item').each(function (i, el) {
        switch ($(el).data('class')) {
          case 'SilverStripe\\UserForms\\Model\\EditableFormField\\EditableFormStep':
            {
              depth = 0;
              return;
            }
          case 'SilverStripe\\UserForms\\Model\\EditableFormField\\EditableFieldGroup':
            {
              thisLevel = ++depth;
              break;
            }
          case 'SilverStripe\\UserForms\\Model\\EditableFormField\\EditableFieldGroupEnd':
            {
              thisLevel = depth--;
              break;
            }
          default:
            {
              thisLevel = depth;
            }
        }

        $(el).toggleClass('infieldgroup', thisLevel > 0);
        for (i = 1; i <= 5; i++) {
          $(el).toggleClass('infieldgroup-level-' + i, thisLevel >= i);
        }
      });

      stickyHeaderInterval = setInterval(function () {
        var offsetTop = fieldEditor.offset().top;
        $buttonrow.width('100%');
        if (offsetTop > navHeight || offsetTop === 0) {
          $buttonrow.removeClass('sticky-buttons');
        } else {
          $buttonrow.addClass('sticky-buttons');
        }
      }, 300);
    },
    onunmatch: function onunmatch() {
      this._super();

      clearInterval(stickyHeaderInterval);
    }
  });

  $('.uf-field-editor .ss-gridfield-buttonrow .action').entwine({
    onclick: function onclick(e) {
      this._super(e);

      this.trigger('addnewinline');
    }
  });

  $('.uf-field-editor').entwine({
    onmatch: function onmatch() {
      var _this = this;

      this._super();

      this.on('addnewinline', function () {
        _this.one('reload', function () {
          var $newField = _this.find('.ss-gridfield-item').last();
          var $groupEnd = null;
          if ($newField.attr('data-class') === 'SilverStripe\\UserForms\\Model\\EditableFormField\\EditableFieldGroupEnd') {
            $groupEnd = $newField;
            $groupEnd.prev().find('.col-Title input').focus();
            $newField = $groupEnd.add($groupEnd.prev());
            $groupEnd.css('visibility', 'hidden');
          } else {
            $newField.find('.col-Title input').focus();
          }

          $newField.addClass('flashBackground');
          $('.cms-content-fields').scrollTop($('.cms-content-fields')[0].scrollHeight);
          if ($groupEnd) {
            $groupEnd.css('visibility', 'visible');
          }
        });
      });
    },
    onummatch: function onummatch() {
      this._super();
    }
  });
});

/***/ }),
/* 1 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_jquery__ = __webpack_require__(3);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_jquery___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0_jquery__);
var _this = this;



__WEBPACK_IMPORTED_MODULE_0_jquery___default.a.entwine('ss', function () {
  var recipient = {
    updateFormatSpecificFields: function updateFormatSpecificFields() {
      var sendPlainChecked = __WEBPACK_IMPORTED_MODULE_0_jquery___default()('input[name="SendPlain"]').is(':checked');

      __WEBPACK_IMPORTED_MODULE_0_jquery___default()('.field.toggle-html-only')[sendPlainChecked ? 'hide' : 'show']();
      __WEBPACK_IMPORTED_MODULE_0_jquery___default()('.field.toggle-plain-only')[sendPlainChecked ? 'show' : 'hide']();
    }
  };

  __WEBPACK_IMPORTED_MODULE_0_jquery___default()('#Form_ItemEditForm .EmailRecipientForm').entwine({
    onmatch: function onmatch() {
      recipient.updateFormatSpecificFields();
    },

    onunmatch: function onunmatch() {
      _this._super();
    }
  });

  __WEBPACK_IMPORTED_MODULE_0_jquery___default()('#Form_ItemEditForm .EmailRecipientForm input[name="SendPlain"]').entwine({
    onchange: function onchange() {
      recipient.updateFormatSpecificFields();
    }
  });
});

/***/ }),
/* 2 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_bundles_FieldEditor_js__ = __webpack_require__(0);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_bundles_FieldEditor_js___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0_bundles_FieldEditor_js__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_bundles_Recipient_js__ = __webpack_require__(1);




/***/ }),
/* 3 */
/***/ (function(module, exports) {

module.exports = jQuery;

/***/ })
/******/ ]);
//# sourceMappingURL=userforms-cms.js.map