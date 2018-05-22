@local @local_metadata @metadatacontext @metadatacontext_category
Feature: Enable category context plugin
  In order to use metadata for course categories
  As an admin
  I need to enable metadata for course categories

  @javascript
  Scenario: Enable metadata for course categories
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
    And I set the field "id_s_metadatacontext_category_metadataenabled" to "1"
    And I press "Save changes"
    Then the field "s_metadatacontext_category_metadataenabled" matches value "1"
    And I navigate to "Courses" node in "Site administration"
    Then I should see "Category metadata"
    And I navigate to "Category metadata" node in "Site administration > Courses"
    Then I should see "Category metadata"
    And I should see "Create a new profile field:"
    And I should see "Create a new profile category"
    And I set the field "datatype" to "datetime"
    Then I should see "Creating a new 'Date/Time' profile field"
    And I set the field "id_shortname" to "creationdate"
    And I set the field "id_name" to "Creation date"
    And I set the field "id_param1" to "2016"
    And I set the field "id_param2" to "2034"
    And I press "Save changes"
    Then I should see "Category metadata"
    And I should see "Creation date"

    And I navigate to "Courses" node in "Site administration"
    And I press "Blocks editing on"
    And I navigate to "Manage courses and categories" node in "Site administration > Courses"
    Then I should see "Miscellaneous"
    And I add the "Administration" block
    And I follow "Edit this category"
    Then I should see "Edit category settings"
    And I add the "Administration" block
    And I should see "Category metadata"
    And I follow "Category metadata"
    Then I should see "Creation date"
    And I set the field "id_local_metadata_field_creationdate_enabled" to "1"
    And I set the field "id_local_metadata_field_creationdate_day" to "21"
    And I set the field "id_local_metadata_field_creationdate_month" to "12"
    And I set the field "id_local_metadata_field_creationdate_year" to "2016"
    And I press "Save changes"
    And I should see "Metadata saved"

    And I navigate to "Manage courses and categories" node in "Site administration > Courses"
    And I follow "Edit this category"
    And I follow "Category metadata"
    Then I should see "Creation date"
    And the field "id_local_metadata_field_creationdate_day" matches value "21"
    And the field "id_local_metadata_field_creationdate_month" matches value "12"
    And the field "id_local_metadata_field_creationdate_year" matches value "2016"
    And the field "id_local_metadata_field_creationdate_enabled" matches value "1"