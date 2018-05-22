@local @local_metadata @metadatacontext @metadatacontext_module
Feature: Enable module context plugin
  In order to use metadata for modules
  As an admin
  I need to enable metadata for modules

  @javascript
  Scenario: Enable metadata for modules
    Given the following "users" exist:
      | username | firstname | lastname | email |
      | teacher1 | Teacher | 1 | teacher1@example.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1 | 0 |
    And the following "activities" exist:
      | activity   | name                   | intro                         | course | idnumber     | groupmode |
      | forum      | Standard forum name    | Standard forum description    | C1     | forum1       | 0         |
    And the following "course enrolments" exist:
      | user | course | role |
      | teacher1 | C1 | editingteacher |
    And I log in as "admin"
    And I navigate to "Settings" node in "Site administration > Plugins > Local plugins > Metadata"
    And I set the field "id_s_metadatacontext_module_metadataenabled" to "1"
    And I press "Save changes"
    Then the field "s_metadatacontext_module_metadataenabled" matches value "1"

    And I navigate to "Plugins" node in "Site administration"
    Then I should see "Module metadata"
    And I navigate to "Module metadata" node in "Site administration > Plugins"
    Then I should see "Module metadata"
    And I should see "Create a new profile field:"
    And I should see "Create a new profile category"
    And I set the field "datatype" to "menu"
    Then I should see "Creating a new 'Dropdown menu' profile field"
    And I set the field "id_shortname" to "subjectmatter"
    And I set the field "id_name" to "Subject matter"
    And I set the field "id_param1" to multiline:
    """
    Languages
    Arts
    Sciences
    Mathematics
    History
    Social Studies
    Other
    """
    And I set the field "id_defaultdata" to "Other"
    And I press "Save changes"
    Then I should see "Module metadata"
    And I should see "Subject matter"

    And I am on "Course 1" course homepage
    And I follow "Standard forum name"
    And I navigate to "Module metadata" in current page administration
    Then I should see "Subject matter"
    And I set the field "id_local_metadata_field_subjectmatter" to "History"
    And I press "Save changes"
    And I should see "Metadata saved"
    And I press "Cancel"
    And I navigate to "Module metadata" in current page administration
    Then I should see "Subject matter"
    And the field "id_local_metadata_field_subjectmatter" matches value "History"
