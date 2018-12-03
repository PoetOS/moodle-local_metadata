@local @local_metadata @metadatacontext @metadatacontext_user
Feature: Enable user context plugin
  In order to use metadata for users
  As an admin
  I need to enable metadata for users

  @javascript
  Scenario: Enable metadata for users
    Given the following "users" exist:
      | username | firstname | lastname | email |
      | teacher1 | Teacher | 1 | teacher1@example.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1 | 0 |
    And the following "course enrolments" exist:
      | user | course | role |
      | teacher1 | C1 | editingteacher |
    And I log in as "admin"
    And I navigate to "Plugins > Local plugins > Metadata" in site administration
    And I set the field "id_s_metadatacontext_user_metadataenabled" to "1"
    And I press "Save changes"
    Then the field "s_metadatacontext_user_metadataenabled" matches value "1"

    And I navigate to "Users" in site administration
    Then I should see "User metadata"
    And I navigate to "Users > User metadata" in site administration
    Then I should see "User metadata"
    And I should see "Create a new profile field:"
    And I should see "Create a new profile category"
    And I set the field "datatype" to "checkbox"
    Then I should see "Creating a new 'Checkbox' profile field"
    And I set the field "id_shortname" to "policyacknowledge"
    And I set the field "id_name" to "I accept the site policy"
    And I press "Save changes"
    Then I should see "User metadata"
    And I should see "I accept the site policy"

    And I navigate to "Users > Accounts > Browse list of users" in site administration
    And I follow "Teacher 1"
    Then I should see "Metadata"
    And I should see "I accept the site policy"
    And "input[name=local_metadata_field_policyacknowledge]:not([checked=checked])" "css_element" should exist
    And I follow "Preferences" in the user menu
    Then I should see "User metadata"
    And I follow "User metadata"
    And I set the field "id_local_metadata_field_policyacknowledge" to "checked"
    And I press "Save changes"
    Then I should see "Metadata saved"
    And I follow "Profile" in the user menu
    Then "input[name=local_metadata_field_policyacknowledge][checked=checked]" "css_element" should exist
