@search
Feature: Search things

    Background:
        Given I am on the homepage

    Scenario: Search front page with one hit
        When I search for "Ibiza"
        Then I should see "Zu \"ibiza\" wurden 1 Artikel gefunden!"
        And  I should see "Strandtuch \"Ibiza\""

    Scenario: Search with few hits
        When I search for "Korn"
        Then I should see "Zu \"Korn\" wurden 3 Artikel gefunden!"
        And  I should not see "Blättern"
        And  I should see "Sasse Korn 32%"
        And  I should see "Münsterländer Lagerkorn 32%"
        And  I should see "Special Finish Lagerkorn X.O. 32%"

    Scenario: Search with many hits
        When I search for "str"
        Then I should see "Zu \"str\" wurden 13 Artikel gefunden!"
        But  I should see 12 elements of type "ArticleBox"

    Scenario: Search with no hits
        When I search for "foo"
        Then I should see the no results message for keyword "foo"

    @javascript
    Scenario Outline: Live-Search with hits
        When I received the search-results for "<searchTerm>"
        Then I should see "<hits> Treffer"

        Examples:
            | searchTerm | hits |
            | ibi        | 1    |
            | bril       | 6    |
            | str        | 13   |
            | arti       | 15   |
            | tee        | 21   |

    @javascript
    Scenario: Live-Search with no hits
        When I received the search-results for "foo"
        Then I should not see "Treffer"
