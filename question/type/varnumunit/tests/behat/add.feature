@ou @ou_vle @qtype @qtype_varnumunit @javascript
Feature: Test creating a Variable numeric set with units question type
  In order evaluate students calculating ability
  As an teacher
  I need to create a Variable numeric set with units questions.

  Background:
    Given the following "users" exist:
      | username | firstname |
      | teacher  | Teacher   |
    And the following "courses" exist:
      | fullname | shortname | format |
      | Course 1 | C1        | topics |
    And the following "course enrolments" exist:
      | user    | course | role           |
      | teacher | C1     | editingteacher |
    When I am on the "Course 1" "core_question > course question bank" page logged in as "teacher"

  Scenario: Create a Variable numeric set with units question.
    And I add a "Variable numeric set with units" question filling the form with:
      | Question name        | Variable numeric set with units question |
      | Question text        | What is [[a]] m + [[b]] m?               |
      | id_vartype_0         | Predefined variable                      |
      | id_unitfraction      | Value : 90%, Units : 10%                 |
      | Variable 1           | a                                        |
      | id_variant0_0        | 2                                        |
      | id_variant1_0        | 3                                        |
      | id_variant2_0        | 5                                        |
      | id_vartype_1         | Predefined variable                      |
      | Variable 2           | b                                        |
      | id_variant0_1        | 8                                        |
      | id_variant1_1        | 5                                        |
      | id_variant2_1        | 3                                        |
      | Variable 3           | c = a + b                                |
      | In student response  | No superscripts                          |
      | id_answer_0          | c                                        |
      | id_fraction_0        | 100%                                     |
      | id_feedback_0        | The numerical part is right.             |
      | id_answer_1          | *                                        |
      | id_feedback_1        | Sorry, no.                               |
      | Unit 1               | match(m)                                 |
      | id_unitsfraction_0   | 100%                                     |
      | id_spaceinunit_0     | Remove all spaces before grading         |
      | id_unitsfeedback_0   | That is the right unit.                  |
      | Unit 2               | match(cm)                                |
      | id_unitsfraction_1   | 100%                                     |
      | id_spaceinunit_1     | Remove all spaces before grading         |
      | id_unitsfeedback_1   | That is the right unit 2.                |
      | id_otherunitfeedback | That is the wrong unit.                  |
      | Hint 1               | Please try again.                        |
      | Hint 2               | You may use a calculator if necessary.   |
    Then I should see "Variable numeric set with units question"
