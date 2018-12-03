@local @local_metadata @metadatacontext @metadatacontext_cohort
Feature: Enable cohort context plugin
  In order to use metadata for cohorts
  As an admin
  I need to enable metadata for cohorts

  @javascript
  Scenario: Enable metadata for cohorts
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
    And I set the field "id_s_metadatacontext_cohort_metadataenabled" to "1"
    And I press "Save changes"
    Then the field "s_metadatacontext_cohort_metadataenabled" matches value "1"

    And I navigate to "Users" in site administration
    Then I should see "Cohort metadata"
    And I navigate to "Users > Cohort metadata" in site administration
    Then I should see "Cohort metadata"
    And I should see "Create a new profile field:"
    And I should see "Create a new profile category"
    And I set the field "datatype" to "text"
    Then I should see "Creating a new 'Text input' profile field"
    And I set the field "id_shortname" to "managerid"
    And I set the field "id_name" to "Cohort Manager ID"
    And I press "Save changes"
    Then I should see "Cohort metadata"
    And I should see "Cohort Manager ID"

    And I navigate to "Users > Accounts > Cohorts" in site administration
    And I press "Blocks editing on"
    And I follow "Add new cohort"
    Then I should see "Add new cohort"
    And I set the field "id_name" to "Cohort One"
    And I press "Save changes"
    Then I should see "System cohorts"
    And I should see "Cohort One"
    And I click on "Edit" "link" in the "//table[@id='cohorts']//tr[1]//td[6]" "xpath_element"
    And I add the "Administration" block
    And I should see "Edit cohort"
    And I should see "Cohort One metadata"
    And I follow "Cohort One metadata"
    Then I should see "Cohort Manager ID"
    And I set the field "id_local_metadata_field_managerid" to "MANAGER001"
    And I press "Save changes"
    And I should see "Metadata saved"
    And I navigate to " Users > Accounts > Cohorts" in site administration
    And I should see "Cohort One"
    And I click on "Edit" "link" in the "//table[@id='cohorts']//tr[1]//td[6]" "xpath_element"
    And I should see "Edit cohort"
    And I follow "Cohort One metadata"
    Then I should see "Cohort Manager ID"
    And the field "id_local_metadata_field_managerid" matches value "MANAGER001"