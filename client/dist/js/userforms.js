!function(e){function t(n){if(r[n])return r[n].exports;var i=r[n]={i:n,l:!1,exports:{}};return e[n].call(i.exports,i,i.exports,t),i.l=!0,i.exports}var r={};t.m=e,t.c=r,t.i=function(e){return e},t.d=function(e,r,n){t.o(e,r)||Object.defineProperty(e,r,{configurable:!1,enumerable:!0,get:n})},t.n=function(e){var r=e&&e.__esModule?function(){return e.default}:function(){return e};return t.d(r,"a",r),r},t.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},t.p="",t(t.s="./client/src/bundles/bundle.js")}({"./client/src/bundles/UserForms.js":function(e,t,r){"use strict";function n(e){return e&&e.__esModule?e:{default:e}}function i(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}function s(e){return"none"!==e.style.display&&"hidden"!==e.style.visibility&&!e.classList.contains("hide")}var u=function(){function e(e,t){for(var r=0;r<t.length;r++){var n=t[r];n.enumerable=n.enumerable||!1,n.configurable=!0,"value"in n&&(n.writable=!0),Object.defineProperty(e,n.key,n)}}return function(t,r,n){return r&&e(t.prototype,r),n&&e(t,n),t}}(),o=r("./node_modules/async-validator/dist-web/index.js"),a=n(o),l=r(0),c=n(l),f=function(){function e(t,r){i(this,e),this.dom=t,this.userForm=r,this.progressTitle=this.userForm.dom.querySelector(".progress-title"),this.buttons=this.dom.querySelectorAll(".step-button-jump"),this.currentStepNumber=this.dom.querySelector(".current-step-number"),this.init()}return u(e,[{key:"init",value:function(){var e=this;this.dom.style.display="initial",this.buttons.forEach(function(t){t.addEventListener("click",function(r){r.preventDefault();var n=parseInt(t.getAttribute("data-step"),10);return e.userForm.jumpToStep(n-1),!1})}),this.userForm.dom.addEventListener("userform.form.changestep",function(t){e.update(t.detail.stepId)}),this.update(0)}},{key:"update",value:function(e){var t=this.userForm.getCurrentStepID()+1,r=this.userForm.getStep(e),n=r.step,i=e/(this.buttons.length-1)*100;this.currentStepNumber.innerText=t,this.dom.querySelectorAll("[aria-valuenow]").forEach(function(e){e.setAttribute("aria-valuenow",t)}),this.buttons.forEach(function(e){var r=e,n=r.parentNode;parseInt(r.getAttribute("data-step"),10)===t&&s(r)&&(n.classList.add("current"),n.classList.add("viewed"),r.disabled=!1),n.classList.remove("current")}),this.progressTitle.innerText=n.getAttribute("data-title"),i=i?i+"%":"",this.dom.querySelector(".progress-bar").style.width=i}}]),e}(),d=function(){function e(t,r){i(this,e),this.step=t,this.userForm=r,this.viewed=!1,this.buttonHolder=null,this.id=0,this.init()}return u(e,[{key:"init",value:function(){var e=this,t=this.getHTMLId();this.buttonHolder=document.querySelector(".step-button-wrapper[data-for='"+t+"']"),["userform.field.hide","userform.field.show"].forEach(function(t){e.buttonHolder.addEventListener(t,function(){e.userForm.dom.trigger("userform.form.conditionalstep")})})}},{key:"setId",value:function(e){this.id=e}},{key:"getHTMLId",value:function(){return this.step.getAttribute("id")}},{key:"show",value:function(){this.step.setAttribute("aria-hidden",!1),this.step.classList.remove("hide"),this.step.classList.add("viewed"),this.viewed=!0}},{key:"hide",value:function(){this.step.setAttribute("aria-hidden",!0),this.step.classList.add("hide")}},{key:"conditionallyHidden",value:function(){var e=this.buttonHolder.querySelector("button");return!("none"!==e.style.display&&"hidden"!==e.visibility&&!e.classList.contains("hide"))}},{key:"getValidatorType",value:function(e){return"email"===e.getAttribute("type")?"email":"date"===e.getAttribute("type")?"date":e.classList.contains("numeric")||"numeric"===e.getAttribute("type")?"number":"string"}},{key:"getValidatorMessage",value:function(e){return e.getAttribute("data-msg-required")?e.getAttribute("data-msg-required"):this.getFieldLabel(e)+" is required"}},{key:"getHolderForField",value:function(e){return window.closest(e,".field")}},{key:"getFieldLabel",value:function(e){var t=this.getHolderForField(e);if(t){var r=t.querySelector("label.left, legend.left");if(r)return r.innerText}return e.getAttribute("name")}},{key:"getValidationsDescriptors",value:function(e){var t=this,r={};return this.step.querySelectorAll("input, textarea, select").forEach(function(n){if(s(n)&&(!e||e&&n.classList.contains("focused"))){var i=t.getFieldLabel(n),u=t.getHolderForField(n);r[n.getAttribute("name")]={title:i,type:t.getValidatorType(n),required:u.classList.contains("requiredField"),message:t.getValidatorMessage(n)};var o=n.getAttribute("data-rule-min"),a=n.getAttribute("data-rule-max");null===o&&null===a||(r[n.getAttribute("name")].asyncValidator=function(e,t){return new Promise(function(e,r){null!==o&&t<o?r(i+" cannot be less than "+o):null!==a&&t>a?r(i+" cannot be greater than "+a):e()})});var l=n.getAttribute("data-rule-minlength"),c=n.getAttribute("data-rule-maxlength");null===l&&null===c||(r[n.getAttribute("name")].asyncValidator=function(e,t){return new Promise(function(e,r){null!==l&&t.length<l?r(i+" cannot be shorter than "+l):null!==c&&t.length>c?r(i+" cannot be longer than "+c):e()})})}}),r}},{key:"validate",value:function(e){var t=this,r=this.getValidationsDescriptors(e);if(Object.keys(r).length){var n=new a.default(r),i=new FormData(this.userForm.dom),s={};return i.forEach(function(e,t){s[t]=e}),this.step.querySelectorAll('input[type="radio"],input[type="checkbox"]').forEach(function(e){var t=e.getAttribute("name");void 0===s[t]&&(s[t]="")}),new Promise(function(e,r){n.validate(s,function(n){n&&n.length?(t.displayErrorMessages(n),r(n)):(t.displayErrorMessages([]),e())})})}return new Promise(function(e){e()})}},{key:"enableLiveValidation",value:function(){var e=this;this.step.querySelectorAll("input, textarea, select").forEach(function(t){t.addEventListener("focusin",function(){t.classList.add("focused")}),t.addEventListener("change",function(){t.classList.add("dirty")}),t.addEventListener("focusout",function(){e.validate(!0).then(function(){}).catch(function(){})})})}},{key:"displayErrorMessages",value:function(e){var t=this,r=[];e.forEach(function(e){var n=t.userForm.dom.querySelector("#"+e.field);if(n){var i=n.querySelector("span.error");i||(i=document.createElement("span"),i.classList.add("error"),i.setAttribute("data-id",e.field)),r.push(e.field),i.innerHTML=e.message,n.append(i)}}),this.step.querySelectorAll("span.error").forEach(function(e){var t=e.getAttribute("data-id");-1===r.indexOf(t)&&e.remove()})}}]),e}(),p=function(){function e(t,r){i(this,e),this.dom=t,this.userForm=r,this.prevButton=t.querySelector(".step-button-prev"),this.nextButton=t.querySelector(".step-button-next"),this.init()}return u(e,[{key:"init",value:function(){var e=this;this.prevButton.addEventListener("click",function(t){t.preventDefault(),window.triggerDispatchEvent(e.userForm.dom,"userform.action.prev")}),this.nextButton.addEventListener("click",function(t){t.preventDefault(),window.triggerDispatchEvent(e.userForm.dom,"userform.action.next")}),this.update(),this.userForm.dom.addEventListener("userform.form.changestep",function(){e.update()}),this.userForm.dom.addEventListener("userform.form.conditionalstep",function(){e.update()})}},{key:"update",value:function(){var e=this.userForm.getNumberOfSteps(),t=this.userForm.getCurrentStepID(),r=null,n=null;for(r=e-1;r>=0;r--)if(n=this.userForm.getStep(r),!n.conditionallyHidden()){t>=r?this.nextButton.parentNode.classList.add("hide"):this.nextButton.parentNode.classList.remove("hide"),t>0&&t<=r?this.prevButton.parentNode.classList.remove("hide"):this.prevButton.parentNode.classList.add("hide"),t>=r?this.dom.querySelector(".btn-toolbar").classList.remove("hide"):this.dom.querySelector(".btn-toolbar").classList.add("hide");break}}}]),e}(),h=function(){function e(t){i(this,e),this.dom=t,this.CONSTANTS={},this.steps=[],this.progressBar=null,this.actions=null,this.currentStep=null,this.CONSTANTS.ENABLE_LIVE_VALIDATION=void 0!==this.dom.getAttribute("livevalidation"),this.CONSTANTS.DISPLAY_ERROR_MESSAGES_AT_TOP=void 0!==this.dom.getAttribute("toperrors"),this.CONSTANTS.ENABLE_ARE_YOU_SURE=void 0!==this.dom.getAttribute("enableareyousure")}return u(e,[{key:"init",value:function(){this.initialiseFormSteps(),this.CONSTANTS.ENABLE_ARE_YOU_SURE&&this.initAreYouSure()}},{key:"initialiseFormSteps",value:function(){var e=this;this.dom.querySelectorAll(".form-step").forEach(function(t){var r=new d(t,e);r.hide(),e.addStep(r),e.CONSTANTS.ENABLE_LIVE_VALIDATION&&r.enableLiveValidation()}),this.setCurrentStep(this.steps[0]);var t=this.dom.querySelector(".userform-progress");t&&(this.progressBar=new f(t,this));var r=this.dom.querySelector(".step-navigation");r&&(this.formActions=new p(r,this),this.formActions.update()),this.setUpPing(),this.dom.addEventListener("userform.action.next",function(){e.nextStep()}),this.dom.addEventListener("userform.action.prev",function(){e.prevStep()}),this.dom.addEventListener("submit",function(t){e.validateForm(t)})}},{key:"validateForm",value:function(e){var t=this;e.preventDefault(),this.currentStep.validate().then(function(e){e||t.dom.submit()}).catch(function(){})}},{key:"setCurrentStep",value:function(e){e instanceof d&&(this.currentStep=e,this.currentStep.show())}},{key:"addStep",value:function(e){e instanceof d&&(e.setId(this.steps.length),this.steps.push(e))}},{key:"getNumberOfSteps",value:function(){return this.steps.length}},{key:"getCurrentStepID",value:function(){return this.currentStep.id?this.currentStep.id:0}},{key:"getStep",value:function(e){return this.steps[e]}},{key:"nextStep",value:function(){var e=this;this.currentStep.validate().then(function(){e.jumpToStep(e.steps.indexOf(e.currentStep)+1,!0)}).catch(function(){})}},{key:"prevStep",value:function(){this.jumpToStep(this.steps.indexOf(this.currentStep)-1,!0)}},{key:"jumpToStep",value:function(e,t){var r=this.steps[e],n=void 0===t||t;if(void 0!==r){if(r.conditionallyHidden())return void(n?this.jumpToStep(e+1,t):this.jumpToStep(e-1,t));this.currentStep&&this.currentStep.hide(),this.setCurrentStep(r),window.triggerDispatchEvent(this.dom,"userform.form.changestep",{stepId:r.id})}}},{key:"setUpPing",value:function(){window.setInterval(function(){fetch("UserDefinedFormController/ping")},18e4)}},{key:"initAreYouSure",value:function(){var e=this;window.addEventListener("beforeunload",function(t){if(0===e.dom.querySelectorAll(".dirty").length)return!0;if(navigator.userAgent.toLowerCase().match(/msie|chrome/)){if(window.hasUserFormsPropted)return;window.hasUserFormsPropted=!0,window.setTimeout(function(){window.hasUserFormsPropted=!1},900)}t.preventDefault(),event.returnValue=c.default._t("UserForms.LEAVE_CONFIRMATION","You have unsaved changes!")})}}]),e}();document.addEventListener("DOMContentLoaded",function(){document.querySelectorAll("form.userform").forEach(function(e){new h(e).init()})})},"./client/src/bundles/bundle.js":function(e,t,r){"use strict";r("./client/src/bundles/UserForms.js")},"./node_modules/async-validator/dist-web/index.js":function(e,t,r){"use strict";Object.defineProperty(t,"__esModule",{value:!0}),function(e){function n(){return n=Object.assign?Object.assign.bind():function(e){for(var t=1;t<arguments.length;t++){var r=arguments[t];for(var n in r)Object.prototype.hasOwnProperty.call(r,n)&&(e[n]=r[n])}return e},n.apply(this,arguments)}function i(e,t){e.prototype=Object.create(t.prototype),e.prototype.constructor=e,u(e,t)}function s(e){return(s=Object.setPrototypeOf?Object.getPrototypeOf.bind():function(e){return e.__proto__||Object.getPrototypeOf(e)})(e)}function u(e,t){return(u=Object.setPrototypeOf?Object.setPrototypeOf.bind():function(e,t){return e.__proto__=t,e})(e,t)}function o(){if("undefined"==typeof Reflect||!Reflect.construct)return!1;if(Reflect.construct.sham)return!1;if("function"==typeof Proxy)return!0;try{return Boolean.prototype.valueOf.call(Reflect.construct(Boolean,[],function(){})),!0}catch(e){return!1}}function a(e,t,r){return a=o()?Reflect.construct.bind():function(e,t,r){var n=[null];n.push.apply(n,t);var i=Function.bind.apply(e,n),s=new i;return r&&u(s,r.prototype),s},a.apply(null,arguments)}function l(e){return-1!==Function.toString.call(e).indexOf("[native code]")}function c(e){var t="function"==typeof Map?new Map:void 0;return(c=function(e){function r(){return a(e,arguments,s(this).constructor)}if(null===e||!l(e))return e;if("function"!=typeof e)throw new TypeError("Super expression must either be null or a function");if(void 0!==t){if(t.has(e))return t.get(e);t.set(e,r)}return r.prototype=Object.create(e.prototype,{constructor:{value:r,enumerable:!1,writable:!0,configurable:!0}}),u(r,e)})(e)}function f(e){if(!e||!e.length)return null;var t={};return e.forEach(function(e){var r=e.field;t[r]=t[r]||[],t[r].push(e)}),t}function d(e){for(var t=arguments.length,r=new Array(t>1?t-1:0),n=1;n<t;n++)r[n-1]=arguments[n];var i=0,s=r.length;return"function"==typeof e?e.apply(null,r):"string"==typeof e?e.replace(E,function(e){if("%%"===e)return"%";if(i>=s)return e;switch(e){case"%s":return String(r[i++]);case"%d":return Number(r[i++]);case"%j":try{return JSON.stringify(r[i++])}catch(e){return"[Circular]"}break;default:return e}}):e}function p(e){return"string"===e||"url"===e||"hex"===e||"email"===e||"date"===e||"pattern"===e}function h(e,t){return void 0===e||null===e||!("array"!==t||!Array.isArray(e)||e.length)||!(!p(t)||"string"!=typeof e||e)}function m(e,t,r){function n(e){i.push.apply(i,e||[]),++s===u&&r(i)}var i=[],s=0,u=e.length;e.forEach(function(e){t(e,n)})}function v(e,t,r){function n(u){if(u&&u.length)return void r(u);var o=i;i+=1,o<s?t(e[o],n):r([])}var i=0,s=e.length;n([])}function y(e){var t=[];return Object.keys(e).forEach(function(r){t.push.apply(t,e[r]||[])}),t}function g(e,t,r,n,i){if(t.first){var s=new Promise(function(t,s){var u=function(e){return n(e),e.length?s(new x(e,f(e))):t(i)};v(y(e),r,u)});return s.catch(function(e){return e}),s}var u=!0===t.firstFields?Object.keys(e):t.firstFields||[],o=Object.keys(e),a=o.length,l=0,c=[],d=new Promise(function(t,s){var d=function(e){if(c.push.apply(c,e),++l===a)return n(c),c.length?s(new x(c,f(c))):t(i)};o.length||(n(c),t(i)),o.forEach(function(t){var n=e[t];-1!==u.indexOf(t)?v(n,r,d):m(n,r,d)})});return d.catch(function(e){return e}),d}function b(e){return!(!e||void 0===e.message)}function w(e,t){for(var r=e,n=0;n<t.length;n++){if(void 0==r)return r;r=r[t[n]]}return r}function F(e,t){return function(r){var n;return n=e.fullFields?w(t,e.fullFields):t[r.field||e.fullField],b(r)?(r.field=r.field||e.fullField,r.fieldValue=n,r):{message:"function"==typeof r?r():r,fieldValue:n,field:r.field||e.fullField}}}function q(e,t){if(t)for(var r in t)if(t.hasOwnProperty(r)){var i=t[r];"object"==typeof i&&"object"==typeof e[r]?e[r]=n({},e[r],i):e[r]=i}return e}function A(){return{default:"Validation error on field %s",required:"%s is required",enum:"%s must be one of %s",whitespace:"%s cannot be empty",date:{format:"%s date %s is invalid for format %s",parse:"%s date could not be parsed, %s is invalid ",invalid:"%s date %s is invalid"},types:{string:"%s is not a %s",method:"%s is not a %s (function)",array:"%s is not an %s",object:"%s is not an %s",number:"%s is not a %s",date:"%s is not a %s",boolean:"%s is not a %s",integer:"%s is not an %s",float:"%s is not a %s",regexp:"%s is not a valid %s",email:"%s is not a valid %s",url:"%s is not a valid %s",hex:"%s is not a valid %s"},string:{len:"%s must be exactly %s characters",min:"%s must be at least %s characters",max:"%s cannot be longer than %s characters",range:"%s must be between %s and %s characters"},number:{len:"%s must equal %s",min:"%s cannot be less than %s",max:"%s cannot be greater than %s",range:"%s must be between %s and %s"},array:{len:"%s must be exactly %s in length",min:"%s cannot be less than %s in length",max:"%s cannot be greater than %s in length",range:"%s must be between %s and %s in length"},pattern:{mismatch:"%s value %s does not match pattern %s"},clone:function(){var e=JSON.parse(JSON.stringify(this));return e.clone=this.clone,e}}}r.d(t,"default",function(){return te});var E=/%[sdj%]/g,S=function(){};void 0!==e&&r.i({NODE_ENV:"production"});var O,x=function(e){function t(t,r){var n;return n=e.call(this,"Async Validation Error")||this,n.errors=t,n.fields=r,n}return i(t,e),t}(c(Error)),L=function(e,t,r,n,i,s){!e.required||r.hasOwnProperty(e.field)&&!h(t,s||e.type)||n.push(d(i.messages.required,e.fullField))},T=function(e,t,r,n,i){(/^\s+$/.test(t)||""===t)&&n.push(d(i.messages.whitespace,e.fullField))},j=function(){if(O)return O;var e=function(e){return e&&e.includeBoundaries?"(?:(?<=\\s|^)(?=[a-fA-F\\d:])|(?<=[a-fA-F\\d:])(?=\\s|$))":""},t="(?:25[0-5]|2[0-4]\\d|1\\d\\d|[1-9]\\d|\\d)(?:\\.(?:25[0-5]|2[0-4]\\d|1\\d\\d|[1-9]\\d|\\d)){3}",r="[a-fA-F\\d]{1,4}",n=("\n(?:\n(?:"+r+":){7}(?:"+r+"|:)|                                    // 1:2:3:4:5:6:7::  1:2:3:4:5:6:7:8\n(?:"+r+":){6}(?:"+t+"|:"+r+"|:)|                             // 1:2:3:4:5:6::    1:2:3:4:5:6::8   1:2:3:4:5:6::8  1:2:3:4:5:6::1.2.3.4\n(?:"+r+":){5}(?::"+t+"|(?::"+r+"){1,2}|:)|                   // 1:2:3:4:5::      1:2:3:4:5::7:8   1:2:3:4:5::8    1:2:3:4:5::7:1.2.3.4\n(?:"+r+":){4}(?:(?::"+r+"){0,1}:"+t+"|(?::"+r+"){1,3}|:)| // 1:2:3:4::        1:2:3:4::6:7:8   1:2:3:4::8      1:2:3:4::6:7:1.2.3.4\n(?:"+r+":){3}(?:(?::"+r+"){0,2}:"+t+"|(?::"+r+"){1,4}|:)| // 1:2:3::          1:2:3::5:6:7:8   1:2:3::8        1:2:3::5:6:7:1.2.3.4\n(?:"+r+":){2}(?:(?::"+r+"){0,3}:"+t+"|(?::"+r+"){1,5}|:)| // 1:2::            1:2::4:5:6:7:8   1:2::8          1:2::4:5:6:7:1.2.3.4\n(?:"+r+":){1}(?:(?::"+r+"){0,4}:"+t+"|(?::"+r+"){1,6}|:)| // 1::              1::3:4:5:6:7:8   1::8            1::3:4:5:6:7:1.2.3.4\n(?::(?:(?::"+r+"){0,5}:"+t+"|(?::"+r+"){1,7}|:))             // ::2:3:4:5:6:7:8  ::2:3:4:5:6:7:8  ::8             ::1.2.3.4\n)(?:%[0-9a-zA-Z]{1,})?                                             // %eth0            %1\n").replace(/\s*\/\/.*$/gm,"").replace(/\n/g,"").trim(),i=new RegExp("(?:^"+t+"$)|(?:^"+n+"$)"),s=new RegExp("^"+t+"$"),u=new RegExp("^"+n+"$"),o=function(r){return r&&r.exact?i:new RegExp("(?:"+e(r)+t+e(r)+")|(?:"+e(r)+n+e(r)+")","g")};o.v4=function(r){return r&&r.exact?s:new RegExp(""+e(r)+t+e(r),"g")},o.v6=function(t){return t&&t.exact?u:new RegExp(""+e(t)+n+e(t),"g")};var a=o.v4().source,l=o.v6().source,c="(?:(?:(?:[a-z]+:)?//)|www\\.)(?:\\S+(?::\\S*)?@)?(?:localhost|"+a+"|"+l+'|(?:(?:[a-z\\u00a1-\\uffff0-9][-_]*)*[a-z\\u00a1-\\uffff0-9]+)(?:\\.(?:[a-z\\u00a1-\\uffff0-9]-*)*[a-z\\u00a1-\\uffff0-9]+)*(?:\\.(?:[a-z\\u00a1-\\uffff]{2,})))(?::\\d{2,5})?(?:[/?#][^\\s"]*)?';return O=new RegExp("(?:^"+c+"$)","i")},k={email:/^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+\.)+[a-zA-Z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]{2,}))$/,hex:/^#?([a-f0-9]{6}|[a-f0-9]{3})$/i},P={integer:function(e){return P.number(e)&&parseInt(e,10)===e},float:function(e){return P.number(e)&&!P.integer(e)},array:function(e){return Array.isArray(e)},regexp:function(e){if(e instanceof RegExp)return!0;try{return!!new RegExp(e)}catch(e){return!1}},date:function(e){return"function"==typeof e.getTime&&"function"==typeof e.getMonth&&"function"==typeof e.getYear&&!isNaN(e.getTime())},number:function(e){return!isNaN(e)&&"number"==typeof e},object:function(e){return"object"==typeof e&&!P.array(e)},method:function(e){return"function"==typeof e},email:function(e){return"string"==typeof e&&e.length<=320&&!!e.match(k.email)},url:function(e){return"string"==typeof e&&e.length<=2048&&!!e.match(j())},hex:function(e){return"string"==typeof e&&!!e.match(k.hex)}},_=function(e,t,r,n,i){if(e.required&&void 0===t)return void L(e,t,r,n,i);var s=["integer","float","array","regexp","object","method","email","number","date","url","hex"],u=e.type;s.indexOf(u)>-1?P[u](t)||n.push(d(i.messages.types[u],e.fullField,e.type)):u&&typeof t!==e.type&&n.push(d(i.messages.types[u],e.fullField,e.type))},N=function(e,t,r,n,i){var s="number"==typeof e.len,u="number"==typeof e.min,o="number"==typeof e.max,a=/[\uD800-\uDBFF][\uDC00-\uDFFF]/g,l=t,c=null,f="number"==typeof t,p="string"==typeof t,h=Array.isArray(t);if(f?c="number":p?c="string":h&&(c="array"),!c)return!1;h&&(l=t.length),p&&(l=t.replace(a,"_").length),s?l!==e.len&&n.push(d(i.messages[c].len,e.fullField,e.len)):u&&!o&&l<e.min?n.push(d(i.messages[c].min,e.fullField,e.min)):o&&!u&&l>e.max?n.push(d(i.messages[c].max,e.fullField,e.max)):u&&o&&(l<e.min||l>e.max)&&n.push(d(i.messages[c].range,e.fullField,e.min,e.max))},D=function(e,t,r,n,i){e.enum=Array.isArray(e.enum)?e.enum:[],-1===e.enum.indexOf(t)&&n.push(d(i.messages.enum,e.fullField,e.enum.join(", ")))},R=function(e,t,r,n,i){if(e.pattern)if(e.pattern instanceof RegExp)e.pattern.lastIndex=0,e.pattern.test(t)||n.push(d(i.messages.pattern.mismatch,e.fullField,t,e.pattern));else if("string"==typeof e.pattern){var s=new RegExp(e.pattern);s.test(t)||n.push(d(i.messages.pattern.mismatch,e.fullField,t,e.pattern))}},V={required:L,whitespace:T,type:_,range:N,enum:D,pattern:R},C=function(e,t,r,n,i){var s=[];if(e.required||!e.required&&n.hasOwnProperty(e.field)){if(h(t,"string")&&!e.required)return r();V.required(e,t,n,s,i,"string"),h(t,"string")||(V.type(e,t,n,s,i),V.range(e,t,n,s,i),V.pattern(e,t,n,s,i),!0===e.whitespace&&V.whitespace(e,t,n,s,i))}r(s)},I=function(e,t,r,n,i){var s=[];if(e.required||!e.required&&n.hasOwnProperty(e.field)){if(h(t)&&!e.required)return r();V.required(e,t,n,s,i),void 0!==t&&V.type(e,t,n,s,i)}r(s)},M=function(e,t,r,n,i){var s=[];if(e.required||!e.required&&n.hasOwnProperty(e.field)){if(""===t&&(t=void 0),h(t)&&!e.required)return r();V.required(e,t,n,s,i),void 0!==t&&(V.type(e,t,n,s,i),V.range(e,t,n,s,i))}r(s)},B=function(e,t,r,n,i){var s=[];if(e.required||!e.required&&n.hasOwnProperty(e.field)){if(h(t)&&!e.required)return r();V.required(e,t,n,s,i),void 0!==t&&V.type(e,t,n,s,i)}r(s)},U=function(e,t,r,n,i){var s=[];if(e.required||!e.required&&n.hasOwnProperty(e.field)){if(h(t)&&!e.required)return r();V.required(e,t,n,s,i),h(t)||V.type(e,t,n,s,i)}r(s)},H=function(e,t,r,n,i){var s=[];if(e.required||!e.required&&n.hasOwnProperty(e.field)){if(h(t)&&!e.required)return r();V.required(e,t,n,s,i),void 0!==t&&(V.type(e,t,n,s,i),V.range(e,t,n,s,i))}r(s)},$=function(e,t,r,n,i){var s=[];if(e.required||!e.required&&n.hasOwnProperty(e.field)){if(h(t)&&!e.required)return r();V.required(e,t,n,s,i),void 0!==t&&(V.type(e,t,n,s,i),V.range(e,t,n,s,i))}r(s)},z=function(e,t,r,n,i){var s=[];if(e.required||!e.required&&n.hasOwnProperty(e.field)){if((void 0===t||null===t)&&!e.required)return r();V.required(e,t,n,s,i,"array"),void 0!==t&&null!==t&&(V.type(e,t,n,s,i),V.range(e,t,n,s,i))}r(s)},Y=function(e,t,r,n,i){var s=[];if(e.required||!e.required&&n.hasOwnProperty(e.field)){if(h(t)&&!e.required)return r();V.required(e,t,n,s,i),void 0!==t&&V.type(e,t,n,s,i)}r(s)},J=function(e,t,r,n,i){var s=[];if(e.required||!e.required&&n.hasOwnProperty(e.field)){if(h(t)&&!e.required)return r();V.required(e,t,n,s,i),void 0!==t&&V.enum(e,t,n,s,i)}r(s)},Z=function(e,t,r,n,i){var s=[];if(e.required||!e.required&&n.hasOwnProperty(e.field)){if(h(t,"string")&&!e.required)return r();V.required(e,t,n,s,i),h(t,"string")||V.pattern(e,t,n,s,i)}r(s)},G=function(e,t,r,n,i){var s=[];if(e.required||!e.required&&n.hasOwnProperty(e.field)){if(h(t,"date")&&!e.required)return r();if(V.required(e,t,n,s,i),!h(t,"date")){var u;u=t instanceof Date?t:new Date(t),V.type(e,u,n,s,i),u&&V.range(e,u.getTime(),n,s,i)}}r(s)},W=function(e,t,r,n,i){var s=[],u=Array.isArray(t)?"array":typeof t;V.required(e,t,n,s,i,u),r(s)},K=function(e,t,r,n,i){var s=e.type,u=[];if(e.required||!e.required&&n.hasOwnProperty(e.field)){if(h(t,s)&&!e.required)return r();V.required(e,t,n,u,i,s),h(t,s)||V.type(e,t,n,u,i)}r(u)},Q=function(e,t,r,n,i){var s=[];if(e.required||!e.required&&n.hasOwnProperty(e.field)){if(h(t)&&!e.required)return r();V.required(e,t,n,s,i)}r(s)},X={string:C,method:I,number:M,boolean:B,regexp:U,integer:H,float:$,array:z,object:Y,enum:J,pattern:Z,date:G,url:K,hex:K,email:K,required:W,any:Q},ee=A(),te=function(){function e(e){this.rules=null,this._messages=ee,this.define(e)}var t=e.prototype;return t.define=function(e){var t=this;if(!e)throw new Error("Cannot configure a schema with no rules");if("object"!=typeof e||Array.isArray(e))throw new Error("Rules must be an object");this.rules={},Object.keys(e).forEach(function(r){var n=e[r];t.rules[r]=Array.isArray(n)?n:[n]})},t.messages=function(e){return e&&(this._messages=q(A(),e)),this._messages},t.validate=function(t,r,i){function s(e){for(var t=[],r={},n=0;n<e.length;n++)!function(e){if(Array.isArray(e)){var r;t=(r=t).concat.apply(r,e)}else t.push(e)}(e[n]);t.length?(r=f(t),l(t,r)):l(null,o)}var u=this;void 0===r&&(r={}),void 0===i&&(i=function(){});var o=t,a=r,l=i;if("function"==typeof a&&(l=a,a={}),!this.rules||0===Object.keys(this.rules).length)return l&&l(null,o),Promise.resolve(o);if(a.messages){var c=this.messages();c===ee&&(c=A()),q(c,a.messages),a.messages=c}else a.messages=this.messages();var p={};(a.keys||Object.keys(this.rules)).forEach(function(e){var r=u.rules[e],i=o[e];r.forEach(function(r){var s=r;"function"==typeof s.transform&&(o===t&&(o=n({},o)),i=o[e]=s.transform(i)),s="function"==typeof s?{validator:s}:n({},s),s.validator=u.getValidationMethod(s),s.validator&&(s.field=e,s.fullField=s.fullField||e,s.type=u.getType(s),p[e]=p[e]||[],p[e].push({rule:s,value:i,source:o,field:e}))})});var h={};return g(p,a,function(t,r){function i(e,t){return n({},t,{fullField:u.fullField+"."+e,fullFields:u.fullFields?[].concat(u.fullFields,[e]):[e]})}function s(s){void 0===s&&(s=[]);var c=Array.isArray(s)?s:[s];!a.suppressWarning&&c.length&&e.warning("async-validator:",c),c.length&&void 0!==u.message&&(c=[].concat(u.message));var f=c.map(F(u,o));if(a.first&&f.length)return h[u.field]=1,r(f);if(l){if(u.required&&!t.value)return void 0!==u.message?f=[].concat(u.message).map(F(u,o)):a.error&&(f=[a.error(u,d(a.messages.required,u.field))]),r(f);var p={};u.defaultField&&Object.keys(t.value).map(function(e){p[e]=u.defaultField}),p=n({},p,t.rule.fields);var m={};Object.keys(p).forEach(function(e){var t=p[e],r=Array.isArray(t)?t:[t];m[e]=r.map(i.bind(null,e))});var v=new e(m);v.messages(a.messages),t.rule.options&&(t.rule.options.messages=a.messages,t.rule.options.error=a.error),v.validate(t.value,t.rule.options||a,function(e){var t=[];f&&f.length&&t.push.apply(t,f),e&&e.length&&t.push.apply(t,e),r(t.length?t:null)})}else r(f)}var u=t.rule,l=!("object"!==u.type&&"array"!==u.type||"object"!=typeof u.fields&&"object"!=typeof u.defaultField);l=l&&(u.required||!u.required&&t.value),u.field=t.field;var c;if(u.asyncValidator)c=u.asyncValidator(u,t.value,s,t.source,a);else if(u.validator){try{c=u.validator(u,t.value,s,t.source,a)}catch(e){null==console.error||console.error(e),a.suppressValidatorError||setTimeout(function(){throw e},0),s(e.message)}!0===c?s():!1===c?s("function"==typeof u.message?u.message(u.fullField||u.field):u.message||(u.fullField||u.field)+" fails"):c instanceof Array?s(c):c instanceof Error&&s(c.message)}c&&c.then&&c.then(function(){return s()},function(e){return s(e)})},function(e){s(e)},o)},t.getType=function(e){if(void 0===e.type&&e.pattern instanceof RegExp&&(e.type="pattern"),"function"!=typeof e.validator&&e.type&&!X.hasOwnProperty(e.type))throw new Error(d("Unknown rule type %s",e.type));return e.type||"string"},t.getValidationMethod=function(e){if("function"==typeof e.validator)return e.validator;var t=Object.keys(e),r=t.indexOf("message");return-1!==r&&t.splice(r,1),1===t.length&&"required"===t[0]?X.required:X[this.getType(e)]||void 0},e}();te.register=function(e,t){if("function"!=typeof t)throw new Error("Cannot register a validator by type, validator is not a function");X[e]=t},te.warning=S,te.messages=ee,te.validators=X}.call(t,r("./node_modules/process/browser.js"))},"./node_modules/process/browser.js":function(e,t){function r(){throw new Error("setTimeout has not been defined")}function n(){throw new Error("clearTimeout has not been defined")}function i(e){if(c===setTimeout)return setTimeout(e,0);if((c===r||!c)&&setTimeout)return c=setTimeout,setTimeout(e,0);try{return c(e,0)}catch(t){try{return c.call(null,e,0)}catch(t){return c.call(this,e,0)}}}function s(e){if(f===clearTimeout)return clearTimeout(e);if((f===n||!f)&&clearTimeout)return f=clearTimeout,clearTimeout(e);try{return f(e)}catch(t){try{return f.call(null,e)}catch(t){return f.call(this,e)}}}function u(){m&&p&&(m=!1,p.length?h=p.concat(h):v=-1,h.length&&o())}function o(){if(!m){var e=i(u);m=!0;for(var t=h.length;t;){for(p=h,h=[];++v<t;)p&&p[v].run();v=-1,t=h.length}p=null,m=!1,s(e)}}function a(e,t){this.fun=e,this.array=t}function l(){}var c,f,d=e.exports={};!function(){try{c="function"==typeof setTimeout?setTimeout:r}catch(e){c=r}try{f="function"==typeof clearTimeout?clearTimeout:n}catch(e){f=n}}();var p,h=[],m=!1,v=-1;d.nextTick=function(e){var t=new Array(arguments.length-1);if(arguments.length>1)for(var r=1;r<arguments.length;r++)t[r-1]=arguments[r];h.push(new a(e,t)),1!==h.length||m||i(o)},a.prototype.run=function(){this.fun.apply(null,this.array)},d.title="browser",d.browser=!0,d.env={},d.argv=[],d.version="",d.versions={},d.on=l,d.addListener=l,d.once=l,d.off=l,d.removeListener=l,d.removeAllListeners=l,d.emit=l,d.prependListener=l,d.prependOnceListener=l,d.listeners=function(e){return[]},d.binding=function(e){throw new Error("process.binding is not supported")},d.cwd=function(){return"/"},d.chdir=function(e){throw new Error("process.chdir is not supported")},d.umask=function(){return 0}},0:function(e,t){e.exports=i18n}});