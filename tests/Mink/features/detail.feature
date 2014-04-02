@detail
Feature: detail page

  Scenario: I can see evaluations
    Given I am on the detail page for article 198

    Then I should see "Kundenbewertungen für \"Artikel mit Bewertung\""
    And I should see an average customer evaluation of 10 from following evaluations
      | author        | evaluation | title         | text                                                                                                                                        | comment                                                                                                                                                     |
      | Bert Bewerter | 10         | Super Artikel | Dieser Artikel zeichnet sich durch extreme Stabilität aus und fasst super viele Klamotten. Das Preisleistungsverhältnis ist exorbitant gut. | Vielen Dank für die positive Bewertung. Wir legen bei der Auswahl unserer Artikel besonders Wert auf die Qualität, sowie das Preis - / Leistungsverhältnis. |
      | Pep Eroni     | 10         | Hervorragend  | bin sehr zufrieden...                                                                                                                       | Danke                                                                                                                                                       |

  @plugin
  Scenario: I can let me notify, when an article is available
    Given the "Notification" plugin is enabled
    And I am on the detail page for article 243
    Then I should see "Benachrichtigen Sie mich, wenn der Artikel lieferbar ist"

    When I fill in "sNotificationEmail" with "test@example.de"
    And I press "Eintragen"
    Then I should see "Bestätigen Sie den Link der eMail die Sie gerade erhalten haben. Sie erhalten dann eine eMail sobald der Artikel wieder verfügbar ist"

  @javascript
  Scenario: I can change the language
    Given I am on the detail page for article 159
    Then  I should see "Strohhut Women mit UV Schutz"

    When  I select "English" from "__shop"
    Then  I should see "Hat Women with UV protection"

    When  I go to previous article
    Then  I should see "Sunglass Big Eyes"

    When  I select "Deutsch" from "__shop"
    Then  I should see "Sonnenbrille Big Eyes"
