@local @local_metadata @metadatacontext @metadatacontext_group
Feature: Enable group context plugin
  In order to use metadata for groups
  As an admin
  I need to enable metadata for groups

  @javascript
  Scenario: Enable metadata for groups
    Given the following "users" exist:
      | username | firstname | lastname | email |
      | teacher1 | Teacher | 1 | teacher1@example.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1 | 0 |
    And the following "groups" exist:
      | name | course | idnumber |
      | Group A | C1 | G1 |
    And the following "course enrolments" exist:
      | user | course | role |
      | teacher1 | C1 | editingteacher |
    And I log in as "admin"
    And I navigate to "Settings" node in "Site administration > Plugins > Local plugins > Metadata"
    And I set the field "id_s_metadatacontext_group_metadataenabled" to "1"
    And I press "Save changes"
    Then the field "s_metadatacontext_group_metadataenabled" matches value "1"

    And I navigate to "Courses" node in "Site administration"
    Then I should see "Group metadata"
    And I navigate to "Group metadata" node in "Site administration > Courses"
    Then I should see "Group metadata"
    And I should see "Create a new profile field:"
    And I should see "Create a new profile category"
    And I set the field "datatype" to "menu"
    Then I should see "Creating a new 'Dropdown menu' profile field"
    And I set the field "id_shortname" to "natimezone"
    And I set the field "id_name" to "N.A. Timezone"
    And I set the field "id_param1" to multiline:
    """
    Newfoundland
    Atlantic
    Eastern
    Central
    Mountain
    Pacific
    """
    And I press "Save changes"
    Then I should see "Group metadata"
    And I should see "N.A. Timezone"

    And I am on "Course 1" course homepage
    And I turn editing mode on
    And I navigate to "Groups" node in "Course administration > Users"
    And I set the field "Groups" to "Group A"
    And I press "Edit group settings"
    And I add the "Administration" block
    And I should see "Group metadata"
    And I follow "Group metadata"
    Then I should see "N.A. Timezone"
    And I set the field "id_local_metadata_field_natimezone" to "Central"
    And I press "Save changes"
    And I should see "Metadata saved"
    And I press "Cancel"
    And I follow "Group metadata"
    Then I should see "N.A. Timezone"
    And the field "id_local_metadata_field_natimezone" matches value "Central"