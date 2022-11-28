@ou @ou_vle @qtype @qtype_recordrtc
Feature: Test importing record audio and video questions
  As a teacher
  In order to reuse record audio and video questions
  I need to import them

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email               |
      | teacher  | Mark      | Allright | teacher@example.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "course enrolments" exist:
      | user    | course | role           |
      | teacher | C1     | editingteacher |

  @javascript @_file_upload
  Scenario: import a recordrtc question with Single audio.
    When I am on the "Course 1" "core_question > course question import" page logged in as teacher
    And I set the field "id_format_xml" to "1"
    And I upload "question/type/recordrtc/tests/fixtures/audio-question.xml" file to "Import" filemanager
    And I press "id_submitbutton"
    Then I should see "Parsing questions from import file."
    And I should see "Importing 1 questions from file"
    And I should see "Please record yourself talking about Moodle."
    And I press "Continue"
    And I should see "Record audio question"

  @javascript @_file_upload
  Scenario: import a recordrtc question with Customised audio/video without feedback.
    When I am on the "Course 1" "core_question > course question import" page logged in as teacher
    And I set the field "id_format_xml" to "1"
    And I upload "question/type/recordrtc/tests/fixtures/customav-question.xml" file to "Import" filemanager
    And I press "id_submitbutton"
    Then I should see "Parsing questions from import file."
    And I should see "Importing 1 questions from file"
    And I should see "Please record yourself talking about following aspects of Moodle."
    And I should see "Development: [[development:audio]]"
    And I press "Continue"
    And I should see "Record audio question"
