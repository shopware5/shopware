@note
Feature: Note

    Background:
        Given I am on the detail page for article 230
        And   I follow "Auf den Merkzettel"
        And   I am on the detail page for article 228
        And   I follow "Auf den Merkzettel"
        And   I go to the page "Note"
        Then  My note should look like this:
            | name                           | supplier | ordernumber | text                                                                                                                                               | price    | image                           | link                                                          |
            | Zahlungsarten & Riskmanagement | Example  | SW100025211 | In Shopware haben Sie ein sehr umfangreiches Riskmanagement, in dem Sie gewünscht Zahlungsarten unter Berücksichtigung verschiedenster Faktoren... | 119,99 € | Kwon-Tasche-Coach-schwarz       | /beispiele/zahlungsarten/228/zahlungsarten-und-riskmanagement |
            | Abschlag bei Zahlungsarten     | Example  | SW10002694  | In Shopware können Sie bei gewünschten Zahlungsarten auch Abschläge definieren. So ist bei diesem Beispiel ein Abschlag von 10% des...             | 47,90 €  | Kwon-Fitness--Boxhandschuh-blau | /beispiele/zahlungsarten/230/abschlag-bei-zahlungsarten       |

    Scenario: I can remove articles from my note
        When  I remove the article on position 2 of my note
        Then  I should see 1 element of type "NotePosition"

        When  I remove the article on position 1 of my note
        Then  I should see 0 elements of type "NotePosition"

    @noResponsive
    Scenario: I can put articles from my note into the basket
        When  I put the article on position 1 of my note in the basket
        And   I follow "Merkzettel"
        And   I put the article on position 2 of my note in the basket

        Then  the cart should contain 2 articles with a value of "165,89 €"
        And   the aggregations should look like this:
            | aggregation   | value    |
            | sum           | 165,89 € |
            | shipping      | 3,90 €   |
            | total         | 169,79 € |
            | sumWithoutVat | 142,68 € |
            | 19 %          | 27,11 €  |

    @comparison
    Scenario: I can compare articles from my note
        When  I compare the article on position 1 of my note
        And   I go to the page "Note"
        And   I compare the article on position 2 of my note
        And   I follow "Vergleich starten"
        Then  The comparison should look like this:
            | image                           | name                           | ranking | description                                                                              | price  | link                                                          |
            | Kwon-Tasche-Coach-schwarz       | Zahlungsarten & Riskmanagement | 0       | In Shopware haben Sie ein sehr umfangreiches Riskmanagement, in dem Sie gewünscht | 119,99 | /beispiele/zahlungsarten/228/zahlungsarten-und-riskmanagement |
            | Kwon-Fitness--Boxhandschuh-blau | Abschlag bei Zahlungsarten     | 0       | In Shopware können Sie bei gewünschten Zahlungsarten auch Abschläge definieren.   | 47,90  | /beispiele/zahlungsarten/230/abschlag-bei-zahlungsarten       |
        When  I go to the page "Note"
        And   I follow "Vergleich löschen"
        And   I go to the page "Note"
        Then  I should not see "Artikel vergleichen"

    @noEmotion
    Scenario: I can put articles from the listing to my note
        When  I follow "Abschlag bei Zahlungsarten"
        And   I follow "Zahlungsarten"
        Then  I should see "Zahlungsarten & Riskmanagement"

        When  I follow the link "remember" of the element "ArticleBox" on position 2
        And   I go to the page "Note"
        Then  I should see 2 element of type "NotePosition"

        When  I follow "Zahlungsarten & Riskmanagement"
        And   I follow "Zahlungsarten"
        Then  I should see "Zahlungsarten & Riskmanagement"

        When  I follow the link "remember" of the element "ArticleBox" on position 1
        And   I go to the page "Note"
        Then  I should see 3 element of type "NotePosition"