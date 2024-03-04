Feature: Userforms
  As a website user
  I want to user userforms

  Background:
    Given the "group" "EDITOR" has permissions "Access to 'Pages' section" and "Access to 'Files' section" and "FILE_EDIT_ALL"
    # Explicitly create an admin group with the default administrators code for UserDefinedFormAdmin
    And the "group" "ADMIN group" has permissions "Full administrative rights"
    And a "group" "ADMIN group" has the "Code" "administrators"

  Scenario: Operate userforms
    Given I am logged in as a member of "EDITOR" group
    When I go to "/admin/pages"
    And I press the "Add new" button
    And I select the "User Defined Form" radio button
    And I press the "Create" button
    And I fill in "Page name" with "My userform"
    And I press the "Save" button

    When I click the "Form Fields" CMS tab

    # Create drop down field
    And I press the "Add Field" button
    And I fill in "Form_Fields_GridFieldEditableColumns_2_Title" with "My dropdown"
    And I select "Dropdown Field" from "Form_Fields_GridFieldEditableColumns_2_ClassName"
    And I press the "Save" button
    And I click on the ".ss-gridfield-item[data-id='2'] .edit-link" element
    And I click the "Options" CMS tab
    And I press the "Add" button
    And I fill in "Options[GridFieldAddNewInlineButton][1][Title]" with "My option 1"
    And I fill in "Options[GridFieldAddNewInlineButton][1][Value]" with "1"
    And I check "Options_GridFieldAddNewInlineButton_o_num_Default"
    And I press the "Add" button
    And I fill in "Options[GridFieldAddNewInlineButton][2][Title]" with "My option 2"
    And I fill in "Options[GridFieldAddNewInlineButton][2][Value]" with "2"
    And I press the "Save" button
    And I follow "My userform"
    And I click the "Form Fields" CMS tab

    # Create textfields
    And I press the "Add Field" button
    And I press the "Add Field" button
    When I fill in "Form_Fields_GridFieldEditableColumns_3_Title" with "My textfield 1"
    When I fill in "Form_Fields_GridFieldEditableColumns_4_Title" with "My textfield 2"
    And I press the "Save" button
    When I press the "Add Page Break" button
    And I press the "Add Field" button
    And I press the "Add Field" button
    And I fill in "Form_Fields_GridFieldEditableColumns_5_Title" with "Second Page"
    When I fill in "Form_Fields_GridFieldEditableColumns_6_Title" with "My textfield 3"
    And I fill in "Form_Fields_GridFieldEditableColumns_7_Title" with "My upload field"
    # Weird behat limitation where the only the select field on the first row is selectable
    And I drag the ".ss-gridfield-item[data-id='7'] .handle" element to the ".ss-gridfield-item[data-id='2'] .handle" element
    And I wait for 1 seconds
    # Click save on the file upload modal to use the default "Form-submissions" folder
    And I select "File Upload Field" from the "Form_Fields_GridFieldEditableColumns_7_ClassName" field
    And I press the "Save and continue" button
    And I wait for 2 seconds
    And I press the "Publish" button
    And I wait for 5 seconds

    # Edit My textfield 3
    When I click on the ".ss-gridfield-item[data-id='6'] .edit-link" element
    And I click the "Validation" CMS tab
    And I check "Is this field Required?"
    And I press the "Save" button
    And I follow "My userform"
    And I click the "Form Fields" CMS tab

    # Drag and drop my text field 2 to Page Two
    Then I drag the ".ss-gridfield-item[data-id='4'] .handle" element to the ".ss-gridfield-item[data-id='6'] .handle" element
    And I wait for 1 seconds
    And I press the "Publish" button
    And I dismiss all toasts

    # Add email recipient with custom text and custom rules
    When I click the "Recipients" CMS tab
    And I press the "Add Email Recipient" button
    And I fill in "Type subject" with "New userform submission"
    And I fill in "Type to address" with "to@example.com"
    And I fill in "Send email from" with "from@example.com"
    And I press the "Create" button
    And I click the "Custom Rules" CMS tab
    And I press the "Add" button
    And I select "My textfield 2" from the "CustomRules[GridFieldAddNewInlineButton][1][ConditionFieldID]" field with javascript
    And I select "Equals" from "CustomRules[GridFieldAddNewInlineButton][1][ConditionOption]"
    And I fill in "CustomRules[GridFieldAddNewInlineButton][1][ConditionValue]" with "do send"
    And I click the "Email Content" CMS tab
    And I fill in "<p>Custom body</p>" for the "EmailBodyHtml" HTML field
    And I press the "Save" button

    # Preview HTML email
    When I preview the email
    Then the rendered HTML should contain "<p>Custom body</p>"

    # Logout
    Given I am not logged in

    # View frontend as anonymous user
    When I go to "/my-userform"
    Then I should see "Page 1 of 2"
    Then I should see "First Page"
    And I should not see "Second Page"
    And I should see "My upload field"
    And I should see "My dropdown"
    And I should see "My textfield 1"
    And I should not see "My textfield 2"
    And I should not see "My textfield 3"

    # Pressing '2' buton should do nothing at this stage
    When I press the "2" button
    Then I should see "First Page"

    When I press the "Next" button
    Then I should see "Page 2 of 2"
    Then I should not see "First Page"
    And I should see "Second Page"
    And I should not see "My upload field"
    And I should not see "My dropdown"
    And I should not see "My textfield 1"
    And I should see "My textfield 2"
    And I should see "My textfield 3"

    When I press the "1" button
    Then I should see "First Page"
    When I press the "2" button
    Then I should see "Second Page"
    When I press the "Prev" button
    Then I should see "First Page"

    When I attach the file "testfile.txt" to the "input.file" field
    And I fill in "My textfield 1" with "My value 1"
    And I press the "Next" button
    And I fill in "My textfield 2" with "do not send"
    And I press the "Submit" button
    Then I should see "'My textfield 3' is required"

    When I fill in "My textfield 3" with "My value 3"
    And I press the "Submit" button
    Then I should see "Thanks, we've received your submission."
    And there should not be an email to "to@example.com" titled "New userform submission"

    # Do again this time with sending email because it did pass custom rule
    When I go to "/my-userform"
    And I press the "Next" button
    And I fill in "My textfield 2" with "do send"
    And I fill in "My textfield 3" with "lorem ipsum"
    And I press the "Submit" button
    Then there should be an email to "to@example.com" titled "New userform submission"
    And the email should contain "<p>Custom body</p>"

    # View submission in backend
    When I am logged in with "ADMIN" permissions
    When I go to "/admin/pages"
    And I follow "My userform"
    And I click the "Submissions" CMS tab
    Then I should see a ".ss-gridfield-item .col-ID" element

    # View uploaded file in backend
    When I go to "/admin/assets"
    # We don't have access to asset-admin FeatureContext here, so using CSS selectors instead
    # Go to the Form-submissions folder, which will be the first/only folder
    And I click on the ".gallery__folders .gallery-item__title" element
    # Assert uploaded file, there will only be one file
    Then I should see a ".gallery__files .gallery-item__title" element
    And the rendered HTML should contain "testfile"
    # Assert is protected file
    And I should see a ".gallery__files .gallery-item__thumbnail .font-icon-user-lock" element
    # Assert has form submission icon
    And I should see a ".gallery__files .gallery-item__thumbnail .font-icon-address-card" element

    # Duplicate userform
    When I go to "/admin/pages"
    And I right click on "My userform" in the tree
    And I hover on "Duplicate" in the context menu
    And I click on "This page only" in the context menu
    When I click the "Form Fields" CMS tab
    Then the rendered HTML should contain "My dropdown"
    And the rendered HTML should contain "My textfield 3"

    When I click on the ".ss-gridfield-item[data-id='10'] .edit-link" element
    And I click the "Options" CMS tab
    Then the rendered HTML should contain "My option 1"
    And the rendered HTML should contain "My option 2"

    When I follow "My userform"
    And I click the "Recipients" CMS tab

    # This is a bug, recipient isn't duplicating
    # Then the rendered HTML should contain "to@example.com"
    # When I click the "Custom Rules" CMS tab
    # Then the rendered HTML should contain "do send"
