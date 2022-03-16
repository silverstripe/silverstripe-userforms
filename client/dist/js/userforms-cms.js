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
/******/ 	return __webpack_require__(__webpack_require__.s = "./client/src/bundles/bundle-cms.js");
/******/ })
/************************************************************************/
/******/ ({

/***/ "./client/src/bundles/ConfirmFolder.js":
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; };

var _i18n = __webpack_require__(5);

var _i18n2 = _interopRequireDefault(_i18n);

var _jquery = __webpack_require__(0);

var _jquery2 = _interopRequireDefault(_jquery);

var _react = __webpack_require__(3);

var _react2 = _interopRequireDefault(_react);

var _reactDom = __webpack_require__(4);

var _reactDom2 = _interopRequireDefault(_reactDom);

var _Injector = __webpack_require__(1);

var _url = __webpack_require__(2);

var _url2 = _interopRequireDefault(_url);

var _qs = __webpack_require__(6);

var _qs2 = _interopRequireDefault(_qs);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

var FormBuilderModal = (0, _Injector.loadComponent)('FormBuilderModal');

_jquery2.default.entwine('ss', function ($) {
  $('#Form_EditForm_Fields').entwine({
    onmatch: function onmatch() {
      var _this = this;

      this._super();

      this.on('addnewinline', function () {
        _this.one('reload', function () {
          var newField = _this.find('.ss-gridfield-item').last();
          newField.find('.col-ClassName select').attr('data-folderconfirmed', 0);
        });
      });
    }
  });

  function toggleVisibility(check, show, hide) {
    if (check) {
      $(show).show();
      $(hide).hide();
    } else {
      $(hide).show();
      $(show).hide();
    }
  }

  $('#Form_ConfirmFolderForm_FolderOptions-new').entwine({
    onmatch: function onmatch() {
      $('#Form_ConfirmFolderForm_CreateFolder_Holder').detach().appendTo($('#Form_ConfirmFolderForm_FolderOptions-new').parent().parent());
      toggleVisibility($(this).prop('checked'), '#Form_ConfirmFolderForm_CreateFolder_Holder', '#Form_ConfirmFolderForm_FolderID_Holder');
    },
    onchange: function onchange() {
      toggleVisibility($(this).prop('checked'), '#Form_ConfirmFolderForm_CreateFolder_Holder', '#Form_ConfirmFolderForm_FolderID_Holder');
    }
  });

  $('#Form_ConfirmFolderForm_FolderOptions-existing').entwine({
    onmatch: function onmatch() {
      $('#Form_ConfirmFolderForm_FolderID_Holder').detach().appendTo($('#Form_ConfirmFolderForm_FolderOptions-existing').parent().parent());
      toggleVisibility($(this).prop('checked'), '#Form_ConfirmFolderForm_FolderID_Holder', '#Form_ConfirmFolderForm_CreateFolder_Holder');
    },
    onchange: function onchange() {
      toggleVisibility($(this).prop('checked'), '#Form_ConfirmFolderForm_FolderID_Holder', '#Form_ConfirmFolderForm_CreateFolder_Holder');
    }
  });

  $('#Form_ConfirmFolderForm_FolderID_Holder .treedropdownfield.is-open,#Form_ItemEditForm_FolderID .treedropdownfield.is-open').entwine({
    onunmatch: function onunmatch() {
      var _this2 = this;

      var adminUrl = window.location.pathname.split('/')[1];
      var parsedURL = _url2.default.parse(adminUrl + '/user-forms/getfoldergrouppermissions');
      var parsedQs = _qs2.default.parse(parsedURL.query);
      parsedQs.FolderID = $(this).find('input[name=FolderID]').val();
      var fetchURL = _url2.default.format(_extends({}, parsedURL, { search: _qs2.default.stringify(parsedQs) }));

      return fetch(fetchURL, {
        credentials: 'same-origin'
      }).then(function (response) {
        return response.json();
      }).then(function (response) {
        $(_this2).siblings('.form__field-description').html(response);
        $(_this2).parent().siblings('.form__field-description').html(response);
        return response;
      }).catch(function (error) {
        _jquery2.default.noticeAdd({ text: error.message, stay: false, type: 'error' });
      });
    }
  });

  $(".uf-field-editor .ss-gridfield-items .dropdown.editable-column-field.form-group--no-label:not([data-folderconfirmed='1'])").entwine({
    onchange: function onchange() {
      if (this.get(0).value !== 'SilverStripe\\UserForms\\Model\\EditableFormField\\EditableFileField') {
        return;
      }

      if ($(".uf-field-editor .ss-gridfield-items .dropdown.editable-column-field.form-group--no-label[data-folderconfirmed='1']").length) {
        return;
      }

      var dialog = $('#confirm-folder__dialog-wrapper');

      if (dialog.length) {
        dialog.remove();
      }

      dialog = $('<div id="confirm-folder__dialog-wrapper" />');
      var id = $(this).closest('tr').data('id');
      dialog.data('id', id);
      $('body').append(dialog);

      dialog.open();
    }
  });

  $('#confirm-folder__dialog-wrapper').entwine({
    onunmatch: function onunmatch() {
      this._clearModal();
    },
    open: function open() {
      this._renderModal(true);
    },
    close: function close(noRevert) {
      if (!noRevert) {
        var id = $('#confirm-folder__dialog-wrapper').data('id');
        var select = $('.ss-gridfield-item[data-id=\'' + id + '\'] .dropdown.editable-column-field.form-group--no-label[data-folderconfirmed=\'0\']');
        select.val('SilverStripe\\UserForms\\Model\\EditableFormField\\EditableTextField');
      }

      this._renderModal(false);
    },
    _renderModal: function _renderModal(isOpen) {
      var _this3 = this;

      var handleHide = function handleHide() {
        return _this3._handleHideModal.apply(_this3, arguments);
      };
      var handleSubmit = function handleSubmit() {
        return _this3._handleSubmitModal.apply(_this3, arguments);
      };
      var title = _i18n2.default._t('UserForms.FILE_CONFIRMATION_TITLE', 'Select file upload folder');
      var editableFileFieldID = $(this).data('id');

      var adminUrl = window.location.pathname.split('/')[1];
      var parsedURL = _url2.default.parse(adminUrl + '/user-forms/confirmfolderformschema');
      var parsedQs = _qs2.default.parse(parsedURL.query);
      parsedQs.ID = editableFileFieldID;
      var schemaUrl = _url2.default.format(_extends({}, parsedURL, { search: _qs2.default.stringify(parsedQs) }));

      _reactDom2.default.render(_react2.default.createElement(FormBuilderModal, {
        title: title,
        isOpen: isOpen,
        onSubmit: handleSubmit,
        onClosed: handleHide,
        schemaUrl: schemaUrl,
        bodyClassName: 'modal__dialog',
        className: 'confirm-folder-modal',
        responseClassBad: 'modal__response modal__response--error',
        responseClassGood: 'modal__response modal__response--good',
        identifier: 'UserForms.ConfirmFolder'
      }), this[0]);
    },
    _clearModal: function _clearModal() {
      _reactDom2.default.unmountComponentAtNode(this[0]);
    },
    _handleHideModal: function _handleHideModal() {
      return this.close();
    },
    _handleSubmitModal: function _handleSubmitModal(data, action, submitFn) {
      var _this4 = this;

      return submitFn().then(function () {
        _jquery2.default.noticeAdd({ text: _i18n2.default._t('UserForms.FILE_CONFIRMATION_CONFIRMATION', 'Folder confirmed successfully.'), stay: false, type: 'success' });
        _this4.close(true);
        $('[name=action_doSave], [name=action_save]').click();
      }).catch(function (error) {
        _jquery2.default.noticeAdd({ text: error.message, stay: false, type: 'error' });
      });
    }
  });

  $('#Form_ConfirmFolderForm_action_cancel').entwine({
    onclick: function onclick() {
      $('#confirm-folder__dialog-wrapper').close();
    }
  });
});

/***/ }),

/***/ "./client/src/bundles/FieldEditor.js":
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _jquery = __webpack_require__(0);

var _jquery2 = _interopRequireDefault(_jquery);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

_jquery2.default.entwine('ss', function ($) {
  var stickyHeaderInterval = null;

  $('.uf-field-editor .ss-gridfield-items').entwine({
    onmatch: function onmatch() {
      var thisLevel = 0;
      var depth = 0;
      var $buttonrow = $('.uf-field-editor .ss-gridfield-buttonrow').addClass('sticky-buttons');
      var navHeight = $('.cms-content-header.north').first().height() + parseInt($('.sticky-buttons').css('padding-top'), 10);
      var fieldEditor = $('.uf-field-editor');

      this._super();

      this.find('.ss-gridfield-item').each(function (index, el) {
        switch ($(el).data('class')) {
          case 'SilverStripe\\UserForms\\Model\\EditableFormField\\EditableFormStep':
            {
              depth = 0;
              return;
            }
          case 'SilverStripe\\UserForms\\Model\\EditableFormField\\EditableFieldGroup':
            {
              depth += 1;
              thisLevel = depth;
              break;
            }
          case 'SilverStripe\\UserForms\\Model\\EditableFormField\\EditableFieldGroupEnd':
            {
              thisLevel = depth;
              depth -= 1;
              break;
            }
          default:
            {
              thisLevel = depth;
            }
        }

        $(el).toggleClass('infieldgroup', thisLevel > 0);
        for (var i = 1; i <= 5; i++) {
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
          var fqcn = 'SilverStripe\\UserForms\\Model\\EditableFormField\\EditableFieldGroupEnd';
          if ($newField.attr('data-class') === fqcn) {
            $groupEnd = $newField;
            $groupEnd.prev().find('.col-Title input').focus();
            $newField = $groupEnd.add($groupEnd.prev());
            $groupEnd.css('visibility', 'hidden');
          } else {
            $newField.find('.col-Title input').focus();
          }

          $newField.addClass('flashBackground');
          var $contenFields = $('.cms-content-fields');
          if ($contenFields.length > 0) {
            $contenFields.scrollTop($contenFields[0].scrollHeight);
          }
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

/***/ "./client/src/bundles/Recipient.js":
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _jquery = __webpack_require__(0);

var _jquery2 = _interopRequireDefault(_jquery);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

_jquery2.default.entwine('ss', function ($) {
  var recipient = {
    updateFormatSpecificFields: function updateFormatSpecificFields() {
      var sendPlainChecked = $('input[name="SendPlain"]').is(':checked');

      $('.field.toggle-html-only')[sendPlainChecked ? 'hide' : 'show']();
      $('.field.toggle-plain-only')[sendPlainChecked ? 'show' : 'hide']();
    }
  };

  $('#Form_ItemEditForm .EmailRecipientForm').entwine({
    onmatch: function onmatch() {
      recipient.updateFormatSpecificFields();
    },

    onunmatch: function onunmatch() {
      undefined._super();
    }
  });

  $('#Form_ItemEditForm .EmailRecipientForm input[name="SendPlain"]').entwine({
    onchange: function onchange() {
      recipient.updateFormatSpecificFields();
    }
  });
});

/***/ }),

/***/ "./client/src/bundles/bundle-cms.js":
/***/ (function(module, exports, __webpack_require__) {

"use strict";


__webpack_require__("./client/src/bundles/FieldEditor.js");

__webpack_require__("./client/src/bundles/ConfirmFolder.js");

__webpack_require__("./client/src/bundles/Recipient.js");

/***/ }),

/***/ 0:
/***/ (function(module, exports) {

module.exports = jQuery;

/***/ }),

/***/ 1:
/***/ (function(module, exports) {

module.exports = Injector;

/***/ }),

/***/ 2:
/***/ (function(module, exports) {

module.exports = NodeUrl;

/***/ }),

/***/ 3:
/***/ (function(module, exports) {

module.exports = React;

/***/ }),

/***/ 4:
/***/ (function(module, exports) {

module.exports = ReactDom;

/***/ }),

/***/ 5:
/***/ (function(module, exports) {

module.exports = i18n;

/***/ }),

/***/ 6:
/***/ (function(module, exports) {

module.exports = qs;

/***/ })

/******/ });
//# sourceMappingURL=userforms-cms.js.map