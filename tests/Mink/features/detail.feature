@detail
Feature: detail page

  Scenario:
    Given I am on the detail page for article 198
    Then I should see "Kundenbewertungen für \"Artikel mit Bewertung\""
    And I should see an average customer evaluation of 10 from following evaluations
      | author        | evaluation | title         | text                                                                                                                                        | comment                                                                                                                                                     |
      | Bert Bewerter | 10         | Super Artikel | Dieser Artikel zeichnet sich durch extreme Stabilität aus und fasst super viele Klamotten. Das Preisleistungsverhältnis ist exorbitant gut. | Vielen Dank für die positive Bewertung. Wir legen bei der Auswahl unserer Artikel besonders Wert auf die Qualität, sowie das Preis - / Leistungsverhältnis. |
      | Pep Eroni     | 10         | Hervorragend  | bin sehr zufrieden...                                                                                                                       | Danke                                                                                                                                                       |
