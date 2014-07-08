@newsletter
Feature: Newsletter

  Scenario: Subscribe to and unsubscripe from newsletter
    Given I subscribe to the newsletter on frontpage with "test@example.de"
    Then  I should see "Vielen Dank. Wir haben Ihre Adresse eingetragen."

    When  I select "-1" from "subscribeToNewsletter"
    And   I press "Speichern"
    Then  I should see "Ihre eMail-Adresse wurde gelöscht"

  @javascript @account
  Scenario:
    Given I am on the page "Account"
    And   I log in successful as "Max Mustermann" with email "test@example.com" and password "shopware"
    When  I check "newsletter"
    Then  I should see "Erfolgreich gespeichert"
    And   the checkbox "newsletter" should be checked

    When  I uncheck "newsletter"
    Then  I should see "Ihre eMail-Adresse wurde gelöscht"
    And   the checkbox "newsletter" should be unchecked
    And   I log me out