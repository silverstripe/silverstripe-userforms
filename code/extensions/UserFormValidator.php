<?php


class UserFormValidator extends RequiredFields {
	public function php($data) {
		if(!parent::php($data)) {
			return false;
		}

		// Skip unsaved records
		if(empty($data['ID']) || !is_numeric($data['ID'])) {
			return true;
		}

		$fields = EditableFormField::get()->filter('ParentID', $data['ID'])->sort('"Sort" ASC');

		// Current nesting
		$stack = array();
		foreach($fields as $field) {
			if($field instanceof EditableFormStep) {
				// Page at top level, or after another page is ok
				if(empty($stack) || (count($stack) === 1 && $stack[0] instanceof EditableFormStep)) {
					$stack = array($field);
					continue;
				}

				$this->validationError(
					'FormFields',
					_t(
						"UserFormValidator.UNEXPECTED_BREAK",
						"Unexpected page break '{name}' inside nested field '{group}'",
						array(
							'name' => $field->CMSTitle,
							'group' => end($stack)->CMSTitle
						)
					),
					'error'
				);
				return false;
			}

			// Validate no pages
			if(empty($stack)) {
				$this->validationError(
					'FormFields',
					_t(
						"UserFormValidator.NO_PAGE",
						"Field '{name}' found before any pages",
						array(
							'name' => $field->CMSTitle
						)
					),
					'error'
				);
				return false;
			}

			// Nest field group
			if($field instanceof EditableFieldGroup) {
				$stack[] = $field;
				continue;
			}

			// Unnest field group
			if($field instanceof EditableFieldGroupEnd) {
				$top = end($stack);

				// Check that the top is a group at all
				if(!$top instanceof EditableFieldGroup) {
					$this->validationError(
						'FormFields',
						_t(
							"UserFormValidator.UNEXPECTED_GROUP_END",
							"'{name}' found without a matching group",
							array(
								'name' => $field->CMSTitle
							)
						),
						'error'
					);
					return false;
				}

				// Check that the top is the right group
				if($top->EndID != $field->ID) {
					$this->validationError(
						'FormFields',
						_t(
							"UserFormValidator.WRONG_GROUP_END",
							"'{name}' found closes the wrong group '{group}'",
							array(
								'name' => $field->CMSTitle,
								'group' => $top->CMSTitle
							)
						),
						'error'
					);
					return false;
				}

				// Unnest group
				array_pop($stack);
			}
		}
		
		return true;
	}
}
