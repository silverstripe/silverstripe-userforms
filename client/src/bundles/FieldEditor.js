/**
 * form builder behaviour.
 */

import jQuery from 'jquery';

jQuery.entwine('ss', ($) => {
  $('.uf-field-editor .ss-gridfield-items').entwine({
    onmatch() {
      let thisLevel = 0;
      let depth = 0;
      this._super();

      // Loop through all rows and set necessary styles
      this.find('.ss-gridfield-item').each((index, el) => {
        switch ($(el).data('class')) {
          case 'SilverStripe\\UserForms\\Model\\EditableFormField\\EditableFormStep': {
            depth = 0;
            return;
          }
          case 'SilverStripe\\UserForms\\Model\\EditableFormField\\EditableFieldGroup': {
            depth += 1;
            thisLevel = depth;
            break;
          }
          case 'SilverStripe\\UserForms\\Model\\EditableFormField\\EditableFieldGroupEnd': {
            thisLevel = depth;
            depth -= 1;
            break;
          }
          default: {
            thisLevel = depth;
          }
        }

        $(el).toggleClass('infieldgroup', thisLevel > 0);
        for (let i = 1; i <= 5; i++) {
          $(el).toggleClass(`infieldgroup-level-${i}`, thisLevel >= i);
        }
      });
    },
    onunmatch() {
      this._super();
    },
  });

  // When new fields are added.
  $('.uf-field-editor .ss-gridfield-buttonrow .action').entwine({
    onclick(e) {
      this._super(e);

      this.trigger('addnewinline');
    },
  });

  $('.uf-field-editor').entwine({
    onmatch() {
      this._super();

      // When the 'Add field' button is clicked set a one time listener.
      // When the GridField is reloaded focus on the newly added field.
      this.on('addnewinline', () => {
        this.one('reload', () => {
          // If fieldgroup, focus on the start marker
          let $newField = this.find('.ss-gridfield-item').last();
          let $groupEnd = null;
          const fqcn = 'SilverStripe\\UserForms\\Model\\EditableFormField\\EditableFieldGroupEnd';
          if ($newField.attr('data-class') === fqcn) {
            $groupEnd = $newField;
            $groupEnd.prev().find('.col-Title input').focus();
            $newField = $groupEnd.add($groupEnd.prev());
            $groupEnd.css('visibility', 'hidden');
          } else {
            $newField.find('.col-Title input').focus();
          }

          $newField.addClass('flashBackground');
          const $contenFields = $('.cms-content-fields');
          if ($contenFields.length > 0) {
            $contenFields.scrollTop($contenFields[0].scrollHeight);
          }
          if ($groupEnd) {
            $groupEnd.css('visibility', 'visible');
          }
        });
      });
    },
    onummatch() {
      this._super();
    },
  });
});
