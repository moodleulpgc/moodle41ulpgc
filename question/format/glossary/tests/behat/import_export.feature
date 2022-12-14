@qformat @qformat_glossary
Feature: Test importing questions from Moodle glossary export.
  In order to reuse glossary entries as questions
  As an teacher
  I need to be able to import them in an exported glossary file.

  Background:
    Given the following "courses" exist:
      | fullname | shortname | format |
      | Course 1 | C1        | topics |
    And the following "users" exist:
      | username | firstname |
      | teacher  | Teacher   |
    And the following "course enrolments" exist:
      | user    | course | role           |
      | teacher | C1     | editingteacher |

  @javascript @_file_upload
  Scenario: import XML file of an export glossary

    When I am on the "Course 1" "core_question > course question import" page logged in as teacher
    And I set the field "id_format_glossary" to "1"
    And I upload "question/format/glossary/tests/fixtures/Glossary.xml" file to "Import" filemanager
    And I press "id_submitbutton"
    Then I should see "Parsing questions from import file."
    And I should see "Importing 1 questions from file"
    And I should see "An activity in Moodle in which learners may attempt questions in a variety of formats"
    When I press "Continue"
    Then I should see "An activity"

    # Now export again.
    When I am on the "Course 1" "core_question > course question export" page logged in as teacher
    And I set the field "id_format_glossary" to "1"
    And I set the field "Export category" to "Vocabulary"
    And I press "Export questions to file"
    And following "click here" should download between "6500" and "7500" bytes
