@qbehaviour @qbehaviour_deferrednonegatives
Feature: Preview a Multiple choice question with Deferred all or nothing behaviour
  As a teacher
  In order to check my questions will work with Deferred all or nothing behaviour
  I need to preview them with Deferred all or nothing behaviour

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email               |
      | teacher1 | T1        | Teacher1 | teacher1@moodle.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
    And the following "question categories" exist:
      | contextlevel | reference | name           |
      | Course       | C1        | Test questions |
    And the following "questions" exist:
      | questioncategory | qtype       | name             | template    |
      | Test questions   | multichoice | Multi-choice-001 | two_of_four |
    Given I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I navigate to "Question bank" in current page administration

  @javascript @_switch_window
  Scenario: Preview a Multiple choice question and submit a partially correct response.
    When I choose "Preview" action for "Multi-choice-001" in the question bank
    And I switch to "questionpreview" window
    And I set the field "How questions behave" to "Deferred feedback (no negatives)"
    And I press "Start again with these options"
    And I click on "One" "text"
    And I click on "Two" "text"
    And I press "Submit and finish"
    Then I should see "One is odd"
    And I should see "Two is even"
    And I should see "Mark 0.00 out of 1.00"
    And I should see "That is not right at all"
    And I switch to the main window

  @javascript @_switch_window
  Scenario: Preview a Multiple choice question and submit a correct response.
    When I choose "Preview" action for "Multi-choice-001" in the question bank
    And I switch to "questionpreview" window
    And I set the field "How questions behave" to "Deferred feedback (no negatives)"
    And I press "Start again with these options"
    And I click on "One" "text"
    And I click on "Three" "text"
    And I press "Submit and finish"
    Then I should see "One is odd"
    And I should see "Three is odd"
    And I should see "Mark 1.00 out of 1.00"
    And I should see "Well done!"
    And I should see "The odd numbers are One and Three."
    And I should see "The correct answers are: One, Three"
    And I switch to the main window
