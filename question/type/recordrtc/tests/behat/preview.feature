@ou @ou_vle @qtype @qtype_recordrtc @_switch_window @javascript
Feature: Preview record audio and video questions
  As a teacher
  In order to check record audio and video questions will work for students
  I need to preview them

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
    And the following "question categories" exist:
      | contextlevel | reference | name           |
      | Course       | C1        | Test questions |
    And the following "questions" exist:
      | questioncategory | qtype     | name                     | template |
      | Test questions   | recordrtc | Record audio question    | audio    |
      | Test questions   | recordrtc | Record customav question | customav |

  Scenario: Preview a question and try to submit nothing.
    When I am on the "Record audio question" "core_question > preview" page logged in as teacher
    Then I should see "Please record yourself talking about Moodle."
    And I press "Save"
    And I should see "Not yet answered"
    And I press "Submit and finish"
    And I should see "Not answered"
    And I should see "I hope you spoke clearly and coherently."
    And I switch to the main window

  Scenario: Preview an audio question and try to submit a response.
    Given the following config values are set as admin:
      | behaviour | immediatefeedback | question_preview |
      | history   | shown             | question_preview |
    When I am on the "Record audio question" "core_question > preview" page logged in as teacher
    And I should see "Please record yourself talking about Moodle."
    When "teacher" has recorded "small.mp3" into the record RTC question
    And I press "Submit and finish"
    And "Download recording.mp3" "link" should exist
    And I switch to the main window

  Scenario: Can still access the recording from an attempt made when the format was .ogg
    Given the following config values are set as admin:
      | behaviour | immediatefeedback | question_preview |
      | history   | shown             | question_preview |
    When I am on the "Record audio question" "core_question > preview" page logged in as teacher
    And I should see "Please record yourself talking about Moodle."
    When "teacher" has recorded "moodle-tim.ogg" into the record RTC question
    And I press "Submit and finish"
    And "Download recording.ogg" "link" should exist
    And I switch to the main window

  Scenario: Preview a Customised (customav) question with three audio inputs and try to submit three responses.
    Given the following config values are set as admin:
      | behaviour | immediatefeedback | question_preview |
      | history   | shown             | question_preview |
    When I am on the "Record customav question" "core_question > preview" page logged in as teacher
    And I should see "Please record yourself talking about following aspects of Moodle."
    And I should see "Development"
    And I should see "Installation"
    And I should see "User experience"
    When "teacher" has recorded "small.mp3" as "audio" into input "development" of the record RTC question
    And "teacher" has recorded "small.mp3" as "audio" into input "installation" of the record RTC question
    And "teacher" has recorded "small.mp3" as "audio" into input "user_experience" of the record RTC question
    And I press "Submit and finish"
    And "Download development.mp3" "link" should exist
    And "Download installation.mp3" "link" should exist
    And "Download user_experience.mp3" "link" should exist
    And I switch to the main window
