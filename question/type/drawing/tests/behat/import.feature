@qtype @qtype_drawing @qtype_drawing_import
Feature: Test importing drawing questions
  As a teacher
  In order to reuse drawing questions
  I need to import them

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | T1        | Teacher1 | teacher1@example.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |

  @javascript @_file_upload
  Scenario: import drawing question.
    When I log in as "teacher1"
    And I am on the "Course 1" "core_question > course question import" page
    And I set the field "id_format_xml" to "1"
    And I upload "question/type/drawing/tests/behat/fixtures/test_drawing_question.moodle.xml" file to "Import" filemanager
    And I press "id_submitbutton"
    Then I should see "Parsing questions from import file."
    And I should see "Importing 1 questions from file"
    And I press "Continue"
    And I should see "drawing-001"
