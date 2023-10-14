@gradingform @gradingform_checklist @javascript
Feature: Converting checklist score to grades
  In order to use and refine checklist to grade students
  As a teacher
  I need to be able to use different grade settings

  Scenario: Set checklist as a grading method for forums.
    Given the following "users" exist:
      | username | firstname | lastname | email |
      | teacher1 | Teacher | 1 | teacher1@example.com |
      | student1 | Student | 1 | student1@example.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student        |
    And the following "activities" exist:
      | activity | name   | description     | course | idnumber | grade[modgrade_type]| advancedgradingmethod_submissions |
      | forum    | forum1 | C1 first forum  | C1     | forum1   | point               | checklist                         |
    And the following forum discussions exist in course "Course 1":
      | user     | forum  | name        | message     |
      | teacher1 | forum1 | discussion1 | message1    |
    When I log in as "teacher1"
    And I change window size to "large"
    And I am on "Course 1" course homepage with editing mode on
    And I am on the "forum1" "forum activity editing" page
    And I expand all fieldsets
    And I select "Point" from the "grade_forum[modgrade_type]" singleselect
    And I select "Checklist" from the "advancedgradingmethod_forum" singleselect
    And I press "Save and return to course"
    And I follow "forum1"
    And I navigate to "Advanced grading" in current page administration
    And I select "Checklist" from the "setmethod" singleselect
    And I follow "Define new grading form from scratch"
    And I set the following fields to these values:
      | Name | Assignment 1 checklist |
      | Description | Checklist test description |
    And I click on "#checklist-groups-NEWID1-description" "css_element"
    And I set the field "checklist-groups-NEWID1-description-input" to "Group 1"
    And I click on "#checklist-groups-NEWID1-items-NEWID0-definition" "css_element"
    And I set the field "checklist-groups-NEWID1-items-NEWID0-definition-input" to "Has title"
    And I click on "#checklist-groups-NEWID1-items-NEWID1-definition" "css_element"
    And I set the field "checklist-groups-NEWID1-items-NEWID1-definition-input" to "Has description"
    And I click on "#checklist-groups-NEWID1-items-NEWID2-definition" "css_element"
    And I set the field "checklist-groups-NEWID1-items-NEWID2-definition-input" to "Has conclusions"
    And I press "Save checklist and make it ready"
    And I am on "Course 1" course homepage
    And I follow "forum1"
    And I click on "Grade users" "button"
    And I should see "Group 1"
    And I should see "Has title"
    And I should see "Has description"
    And I should see "Has conclusions"
