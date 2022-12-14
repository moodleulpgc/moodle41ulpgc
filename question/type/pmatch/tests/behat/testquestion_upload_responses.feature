@ou @ou_vle @qtype @qtype_pmatch
Feature: Test uploading test responses in the pattern match test this question feature
  In order to test pattern match question accuracy
  As a teacher
  I need to upload test responses pattern match questions.

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
    And the following "question categories" exist:
      | contextlevel | reference | name           |
      | Course       | C1        | Test questions |
    And the following "questions" exist:
      | questioncategory | qtype    | name         | template |
      | Test questions   | pmatch   | My first pattern match question | listen |

  @javascript @_file_upload
  Scenario: Upload responses to test with.
    When I am on the "My first pattern match question" "qtype_pmatch > test responses" page logged in as teacher
    And I click on "Upload responses" "button"
    # Confirm list responses is correct.
    Then I should see "Pattern-match question testing tool: Testing question: My first pattern match question"
    And I should see "Back to Test question"
    And I should see "Marked responses to upload"
    And I upload "question/type/pmatch/tests/fixtures/uploadreponses.csv" file to "Marked responses" filemanager
    And I press "Upload these responses"
    And I should see "Saved 8 responses"
    And I should see "Upload another file"

  @javascript @_file_upload
  Scenario: Test error message if the file doesn't meet the condition.
    When I am on the "My first pattern match question" "qtype_pmatch > test responses upload" page logged in as teacher
    # Case 1: The file must be in .csv format.
    When I upload "question/type/pmatch/tests/fixtures/testerrorcase1.xls" file to "Marked responses" filemanager
    And I press "Upload these responses"
    Then I should see "The file must be in .csv/.xlsx/.html/.json/.ods format."
    # Case 2: The file requires at least two rows (the first row is the header row, the second row onwards for responses).
    When I upload "question/type/pmatch/tests/fixtures/testerrorcase2.csv" file to "Marked responses" filemanager
    And I press "Upload these responses"
    Then I should see "The file requires at least two rows (the first row is the header row, the second row onwards for responses)."
    # Case 3: The file has more than two columns. Please only include the expected mark and response.
    When I upload "question/type/pmatch/tests/fixtures/testerrorcase3.csv" file to "Marked responses" filemanager
    And I press "Upload these responses"
    Then I should see "The file has more than two columns. Please only include the expected mark and response."
    # Case 4: The file requires at least two columns (the first column for expected marks, the second column for responses).
    When I upload "question/type/pmatch/tests/fixtures/testerrorcase4.csv" file to "Marked responses" filemanager
    And I press "Upload these responses"
    Then I should see "The file requires at least two columns (the first column for expected marks, the second column for responses)."
    # Case 5: test error case 2 and case 4
    When I upload "question/type/pmatch/tests/fixtures/testerror.csv" file to "Marked responses" filemanager
    And I press "Upload these responses"
    Then I should see "The file requires at least two rows (the first row is the header row, the second row onwards for responses)."
    And I should see "The file requires at least two columns (the first column for expected marks, the second column for responses)."

  @javascript @_file_upload
  Scenario: Test upload XLSX file type.
    When I am on the "My first pattern match question" "qtype_pmatch > test responses upload" page logged in as teacher
    And I upload "question/type/pmatch/tests/fixtures/testreponses_xlsx_error_1.xlsx" file to "Marked responses" filemanager
    And I press "Upload these responses"
    Then I should see "The file requires at least two columns (the first column for expected marks, the second column for responses)."
    And I upload "question/type/pmatch/tests/fixtures/testreponses_xlsx_normal.xlsx" file to "Marked responses" filemanager
    And I press "Upload these responses"
    And I should see "Saved 4 responses"
