@assignfeedback @assignfeedback_solutionsheet @_file_upload
Feature: In an assignment, students can see solutions only if they have made a submission
  In order to see solution sheets
  As a student
  I need to make a submission first.

  Background:
    Given the following "courses" exist:
      | fullname | shortname | category | groupmode |
      | Course 1 | C1 | 0 | 1 |
    And the following "activity" exists:
      | activity                            | assign                  |
      | course                              | C1                      |
      | name                                | Test assignment name    |
      | intro                               | Questions here          |
      | assignsubmission_onlinetext_enabled | 1                       |
      | assignsubmission_file_enabled       | 0                       |
      | attemptreopenmethod                 | manual                  |
      | hidegrader                          | 1                       |
      | submissiondrafts                    | 0                       |
    And the following "users" exist:
      | username | firstname | lastname | email |
      | teacher1 | Teacher | 1 | teacher1@example.com |
      | student1 | Student | 1 | student1@example.com |
      | student2 | Student | 2 | student2@example.com |
    And the following "course enrolments" exist:
      | user | course | role |
      | teacher1 | C1 | editingteacher |
      | student1 | C1 | student |
      | student2 | C1 | student |
    And I am on the "Test assignment name" Activity page logged in as teacher1
    And I navigate to "Settings" in current page administration
    And I follow "Expand all"
    And I set the field "assignfeedback_solutionsheet_enabled" to "1"
    And I set the field "assignfeedback_solutionsheet_requiresubmission" to "1"
    And I upload "mod/assign/feedback/solutionsheet/tests/fixtures/solutionsheet.txt" file to "Upload solution sheets" filemanager
    And I press "Save and display"
    And I log out
    And I am on the "Test assignment name" Activity page logged in as student1
    And I press "Add submission"
    And I set the following fields to these values:
      | Online text | My submission. |
    And I press "Save changes"
    And I log out

  @javascript
  Scenario: Students can see the solution when unhidden and when they have made a submission.
    When I am on the "Test assignment name" Activity page logged in as student1
    Then I should see "The solutions are not yet available"
    And I should see "Only students who made a submission will"
    And I should not see "solutionsheet.txt"
    And I log out

    When I am on the "Test assignment name" Activity page logged in as student2
    Then I should see "The solutions are not yet available"
    And I should see "Only students who made a submission will"
    And I should not see "solutionsheet.txt"
    And I log out

    When I am on the "Test assignment name" Activity page logged in as teacher1
    Then I should see "Solution sheets"
    And I should see "solutionsheet.txt"
    And I should see "Students can not currently access the solutions"
    And I should see "Click to show the solutions"

    When I follow "Click to show the solutions"
    Then I should see "Are you sure you want to show the solutions"
    When I press "Yes"
    Then I should see "Changes saved"
    And I should see "Solution sheets"
    And I should see "solutionsheet.txt"
    And I should see "Click to hide the solutions"
    And I log out

    When I am on the "Test assignment name" Activity page logged in as student1
    Then I should see "solutionsheet.txt"
    And I should not see "The solutions are not yet available"
    And I should not see "Only students who made a submission will"
    And I log out

    When I am on the "Test assignment name" Activity page logged in as student2
    Then I should not see "solutionsheet.txt"
    And I should not see "The solutions are not yet available"
    And I should see "Only students who made a submission will"
    And I log out
