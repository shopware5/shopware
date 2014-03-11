@notification
Feature: notification


  Scenario: I can let me notify, when an article is available
    Given I am on the detail page for article 243
    Then I should see "Benachrichtigen Sie mich, wenn der Artikel lieferbar ist"

    When I fill in "sNotificationEmail" with "test@example.de"
    And I press "Eintragen"
    Then I should see "Bestätigen Sie den Link der eMail die Sie gerade erhalten haben. Sie erhalten dann eine eMail sobald der Artikel wieder verfügbar ist"

  Scenario: I can't subscribe more than one time
    Given I am on the detail page for article 243
    Then I should see "Sie haben sich bereits für eine Benachrichtigung eingetragen"
