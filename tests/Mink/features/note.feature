@note
Feature: Note

    Background:
        Given the note contains the following products:
            | number      | name                           |
            | SW100025211 | Zahlungsarten & Riskmanagement |
            | SW10002694  | Abschlag bei Zahlungsarten     |

    @esd @variants
    Scenario: I can add several products to the note from their detail pages
        When  I am on the detail page for article 197
        And   I follow "Auf den Merkzettel"
        Then  I should be on the page "Note"

        When  I go to the detail page for article 122
        And   I choose the following article configuration:
            | groupId | value     |
            | 5       | 1,0 Liter |
        And   I follow "Auf den Merkzettel"
        Then  I should be on the page "Note"
        And   the note should contain the following products:
            | number      | name                           | supplier            | description                                                                                                                                                  | price   | image                           | link                                                          |
            | SW100025211 | Zahlungsarten & Riskmanagement | Example             | In Shopware haben Sie ein sehr umfangreiches Riskmanagement, in dem Sie gewünscht Zahlungsarten unter Berücksichtigung verschiedenster Faktoren...           | 119,99  | Kwon-Tasche-Coach-schwarz       | /beispiele/zahlungsarten/228/zahlungsarten-und-riskmanagement |
            | SW10002694  | Abschlag bei Zahlungsarten     | Example             | In Shopware können Sie bei gewünschten Zahlungsarten auch Abschläge definieren. So ist bei diesem Beispiel ein Abschlag von 10% des...                       | 47,90   | Kwon-Fitness--Boxhandschuh-blau | /beispiele/zahlungsarten/230/abschlag-bei-zahlungsarten       |
            | SW10196     | ESD Download Artikel           | Example             | Electronic Software Distribution (ESD) hilft Ihnen bei dem Vertrieb von reinen Software Produkten. Diese Produkte werden online bestellt, bezahlt und zum... | 34,99   | Buecher-ESD503f5a25e6a12        | /beispiele/darstellung/197/esd-download-artikel               |
            | SW10123.3   | Sasse Korn 32% 1,0 Liter       | Feinbrennerei Sasse | Tuslibet inhospitalitas. Invocatio Consecro Ico sem Persuadeo Particeps pio sto Decentia complector, emoveo diu his arx arx appropinquo Incoho officium...   | ab 2,99 | Sasse-Korn-02-l                 | /genusswelten/edelbraende/122/sasse-korn-32?number=SW10123.3  |

    Scenario: I can remove articles from my note
        When  I remove the article on position 2 of my note
        Then  I should see 1 element of type "NotePosition"

        When  I remove the article on position 1 of my note
        Then  I should see 0 elements of type "NotePosition"

    @comparison
    Scenario: I can compare articles from my note
        When  I compare the article on position 1 of my note
        And   I go to the page "Note"
        And   I compare the article on position 2 of my note
        And   I follow "Vergleich starten"
        Then  the comparison should contain the following products:
            | image                           | name                           | ranking | description                                                                       | price  | link                                                          |
            | Kwon-Tasche-Coach-schwarz       | Zahlungsarten & Riskmanagement | 0       | In Shopware haben Sie ein sehr umfangreiches Riskmanagement, in dem Sie gewünscht | 119,99 | /beispiele/zahlungsarten/228/zahlungsarten-und-riskmanagement |
            | Kwon-Fitness--Boxhandschuh-blau | Abschlag bei Zahlungsarten     | 0       | In Shopware können Sie bei gewünschten Zahlungsarten auch Abschläge definieren.   | 47,90  | /beispiele/zahlungsarten/230/abschlag-bei-zahlungsarten       |
        When  I go to the page "Note"
        And   I follow "Vergleich löschen"
        And   I go to the page "Note"
        Then  I should not see "Artikel vergleichen"

    Scenario: I can put articles from the listing to my note
        When  I follow "Abschlag bei Zahlungsarten"
        And   I follow "Zahlungsarten"
        Then  I should see "Zahlungsarten & Riskmanagement"
        And   I should be on the page "Listing"

        When  I follow the link "remember" of the element "ArticleBox" on position 2
        Then  I should be on the page "Note"
        And   I should see 2 element of type "NotePosition"

        When  I follow "Zahlungsarten & Riskmanagement"
        And   I follow "Zahlungsarten"
        Then  I should see "Zahlungsarten & Riskmanagement"
        And   I should be on the page "Listing"

        When  I follow the link "remember" of the element "ArticleBox" on position 1
        Then  I should be on the page "Note"
        And   I should see 3 element of type "NotePosition"
