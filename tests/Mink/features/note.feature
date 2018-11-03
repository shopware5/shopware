@note
Feature: Note

    Background:
        Given the note contains the following products:
            | number     | name                         |
            | SW10001    | Versandkostenfreier Artikel  |
            | SW100718.1 | Ausweichversandkosten weiss  |

    @esd @variants
    Scenario: I can add several products to the note from their detail pages
        When  I am on the detail page for article 197
        And   I press "Auf den Merkzettel"
        Then  I should be on the page "Note"

        When  I go to the detail page for article 122
        And   I choose the following article configuration:
            | groupId | value     |
            | 5       | 1,0 Liter |
        And   I press "Auf den Merkzettel"
        Then  I should be on the page "Note"
        And   the note should contain the following products:
            | number     | name                        | supplier            | price   | image                                                | link                                                                    |
            | SW10001    | Versandkostenfreier Artikel | Example             | 35,99   | Kwon-Fussschuetzer-grip-leather-schwarz503e21d45c10c | /beispiele/versandkosten/220/versandkostenfreier-artikel?number=SW10001 |
            | SW100718.1 | Ausweichversandkosten weiss | Example             | 49,99   | Kwon-mma-mixed-fight-Handschuhe                      | /beispiele/versandkosten/223/ausweichversandkosten?number=SW100718.1    |
            | SW10196    | ESD Download Artikel        | Example             | 34,99   | Buecher-ESD503f5a25e6a12                             | /beispiele/darstellung/197/esd-download-artikel                         |
            | SW10123.3  | Sasse Korn 32% 1,0 Liter    | Feinbrennerei Sasse | ab 2,99 | Sasse-Korn-02-l                                      | /genusswelten/edelbraende/122/sasse-korn-32?number=SW10123.3            |

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
            | image                                                | name                        | ranking | description                                                                          | price | link                                                                    |
            | Kwon-mma-mixed-fight-Handschuhe                      | Ausweichversandkosten       | 0       | Diese Versandart greift als Ausweich-Versandart grundsätzlich immer dann wenn die... | 49,99 | /beispiele/versandkosten/223/ausweichversandkosten?number=SW100718.1    |
            | Kwon-Fussschuetzer-grip-leather-schwarz503e21d45c10c | Versandkostenfreier Artikel | 0       | Sie haben die Möglichkeit, Artikel versandkostenfrei zu versenden. Auch wenn Sie...  | 35,99 | /beispiele/versandkosten/220/versandkostenfreier-artikel?number=SW10001 |
        When  I go to the page "Note"
        And   I press "Vergleich löschen"
        And   I go to the page "Note"
        Then  I should not see "Artikel vergleichen"

    Scenario: I can put articles from the listing to my note
        When  I follow "Versandkostenfreier Artikel"
        And   I follow "Versandkosten"
        Then  I should see "Ausweichversandkosten"
        And   I should be on the page "Listing"

        When  I press the button "remember" of the element "ArticleBox" on position 2
        Then  I should be on the page "Note"
        And   I should see 2 element of type "NotePosition"

        When  I follow "Ausweichversandkosten"
        And   I follow "Versandkosten"
        Then  I should see "Ausweichversandkosten"
        And   I should be on the page "Listing"

        When  I press the button "remember" of the element "ArticleBox" on position 4
        Then  I should be on the page "Note"
        And   I should see 3 element of type "NotePosition"
