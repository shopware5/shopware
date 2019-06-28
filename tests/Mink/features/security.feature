@security
Feature: Security

  @csrf @javascript
  Scenario: I can switch sub(language) shops without getting csrf token exceptions
    Given  I am on the page "account"
    And    I select "English" from "__shop"
    Then   I the language should be "en"
    And    I log in with email "test@example.com" and password "shopware"
    Then   I log me out
    Then   I click on login again
    And    I select "Deutsch" from "__shop"
    Then   I the language should be "de"
    And    I log in successful as "Max Mustermann" with email "test@example.com" and password "shopware"

  @csrf @exception
  Scenario: I get a http response code 400 when i submit a form without csrf token (without javascript)
    Given  I am on the page "account"
    And    I log in with email "test@example.com" and password "shopware"
    Then   The http response code should be "400"
