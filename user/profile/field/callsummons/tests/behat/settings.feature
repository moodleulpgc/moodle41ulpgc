@core @core_user @profilefield  @profilefield_callsummons
Feature: callsummons fields is installed and configurable.
    In order to add callsummons profile fields properly
    As an admin
    I should be able to create and set up callsummons profile fields.

  @javascript
  Scenario: Verify you can create callsummons profile fields.
    Given I log in as "admin"
    When I navigate to "Users > Accounts > User profile fields" in site administration
    And I set the field "Create a new profile field" to "Call summons"
    And I set the following fields to these values:
        | Short name                  | callsummons |
        | Name                        | Latest call |
        | Enabled                     | Yes         |
        | Target group                | C56         |
        | Allow dismiss notifications | No          |
        | Notifications icon          | fa-star     |
        | Always display the icon     | No          |
    And the "Is this field required?" "field" should be disabled
    And the "Is this field locked?" "field" should be disabled
    And the "Should the data be unique?" "field" should be disabled
    And the "Display on signup page?" "field" should be disabled
    And the "Who is this field visible to?" "field" should be disabled
    And I click on "Save changes" "button"
    Then I should see "Latest call" in the "Profile field" "table"
