@assets @retry
Feature: Next form step for userforms
  As a website user
  I want to click to the next form step button to see the next form step

  Background:
    Given a userform with a hidden form step "My userform"

  Scenario: Next step button does not navigate to hidden form steps
    When I go to "/my-userform"
      And I wait for 2 seconds
      And the ".progress-title" element should contain "EditableFormStep_01"
    When I click the ".step-button-next" element
    Then the ".progress-title" element should contain "EditableFormStep_03"
    When I click the ".step-button-prev" element
      And I fill in "abc" for "EditableTextField_01"
      And I click the ".step-button-next" element
    Then the ".progress-title" element should contain "EditableFormStep_02"
    # prevent the 'form has unsaved changes' alrt
    When I click the ".step-button-prev" element
      And I fill in "" for "EditableTextField_01"
