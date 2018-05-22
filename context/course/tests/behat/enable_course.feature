@local @local_metadata @metadatacontext @metadatacontext_course
Feature: Enable course context plugin
  In order to use metadata for courses
  As an admin
  I need to enable metadata for courses

  @javascript
  Scenario: Enable metadata for courses
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
    And I navigate to "Settings" node in "Site administration > Plugins > Local plugins > Metadata"
    And I set the field "id_s_metadatacontext_course_metadataenabled" to "1"
    And I press "Save changes"
    Then the field "s_metadatacontext_course_metadataenabled" matches value "1"

    And I navigate to "Courses" node in "Site administration"
    Then I should see "Course metadata"
    And I navigate to "Course metadata" node in "Site administration > Courses"
    Then I should see "Course metadata"
    And I should see "Create a new profile field:"
    And I should see "Create a new profile category"
    And I set the field "datatype" to "menu"
    Then I should see "Creating a new 'Dropdown menu' profile field"
    And I set the field "id_shortname" to "classsize"
    And I set the field "id_name" to "Class size"
    And I set the field "id_param1" to multiline:
    """
    0-10
    11-30
    31-50
    51-100
    >100
    """
    And I set the field "id_defaultdata" to "0-10"
    And I press "Save changes"
    Then I should see "Course metadata"
    And I should see "Class size"

    And I am on "Course 1" course homepage
    And I navigate to "Course metadata" in current page administration
    Then I should see "Course metadata"
    And I should see "Class size"
    And I set the field "id_local_metadata_field_classsize" to "31-50"
    And I press "Save changes"
    Then I should see "Metadata saved"
    And I am on "Course 1" course homepage
    And I navigate to "Course metadata" in current page administration
    Then I should see "Class size"
    And the field "id_local_metadata_field_classsize" matches value "31-50"
