@newsletter
Feature: Newsletter

  Scenario: Subscribe to and unsubscripe from newsletter
    Given I am on the frontpage
    When I fill in "newsletter_input" with "test@example.de"
    And I press "newsletter"
    Then I should see "Vielen Dank. Wir haben Ihre Adresse eingetragen."

    When I select "-1" from "subscribeToNewsletter"
    And I press "Speichern"
    Then I should see " Ihre eMail-Adresse wurde gelöscht "

  @javascript
  Scenario:
    Given I log in successful as "test@example.com" with password "shopware"
    When I check "newsletter"
    Then I should see "Erfolgreich gespeichert"
    And the checkbox "newsletter" should be checked

    When I uncheck "newsletter"
    Then I should see "Erfolgreich gespeichert"
    And the checkbox "newsletter" should be unchecked

