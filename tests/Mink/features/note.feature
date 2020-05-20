@note
Feature: Note

    Background:
        Given the note contains the following products:
            | number     | name                         |
            | SW10001    | Versandkostenfreier Artikel  |
            | SW100718.1 | Ausweichversandkosten weiss  |

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
        And   I wait for "1" seconds
        And   I am on "/compare/overlay"
        And   I wait for "1" seconds
        Then  the comparison should contain the following products:
            | image                                                | name                        | ranking | description                                                                          | price | link                                                                    |
            | Kwon-mma-mixed-fight-Handschuhe                      | Ausweichversandkosten       | 0       | Diese Versandart greift als Ausweich-Versandart grundsätzlich immer dann wenn die... | 49,99 | /beispiele/versandkosten/223/ausweichversandkosten?number=SW100718.1    |
            | Kwon-Fussschuetzer-grip-leather-schwarz503e21d45c10c | Versandkostenfreier Artikel | 0       | Sie haben die Möglichkeit, Artikel versandkostenfrei zu versenden. Auch wenn Sie...  | 35,99 | /beispiele/versandkosten/220/versandkostenfreier-artikel?number=SW10001 |
        When  I am on "/compare"
        And   I press "Vergleich löschen"
        And   I go to the page "Note"
        Then  I should not see "Artikel vergleichen"

    Scenario: I can put articles from the listing to my note
        When  I follow "Versandkostenfreier Artikel"
        And   I follow "Versandkosten"
        Then  I should see "Ausweichversandkosten"
        And   I should be on the page "Listing"

        When  I press the button "remember" of the element "ArticleBox" on position 2
        And   I am on the page "Note"
        Then  I should see 2 element of type "NotePosition"

        When  I follow "Ausweichversandkosten"
        And   I follow "Versandkosten"
        Then  I should see "Ausweichversandkosten"
        And   I should be on the page "Listing"

        When  I press the button "remember" of the element "ArticleBox" on position 4
        And   I am on the page "Note"
        Then  I should see 3 element of type "NotePosition"
