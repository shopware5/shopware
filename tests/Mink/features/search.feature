@search @misc
Feature: Search things

    Background:
        Given I am on the page "Homepage"

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

    @javascript @noinfinitescrolling
    Scenario: Search with special uri characters
        When I search for "101% alle"
        Then I should see "Zu \"101% alle\" wurden 74 Artikel gefunden!"
        But  I should see 12 elements of type "ArticleBox"
        When I browse to next page 6 times
        Then I should see 2 elements of type "ArticleBox"
        But  I should see "Zu \"101% alle\" wurden 74 Artikel gefunden!"

    Scenario: Search with no hits
        When I search for "foo"
        Then I should see the no results message for keyword "foo"

    @searchWithoutMinLength
    Scenario: Search with with empty string
        When I search for ""
        Then I should see "Zu \"\" wurden 196 Artikel gefunden!"

    @javascript @searchWithoutMinLength
    Scenario: Search with empty string and filter afterwards
        When I search for ""
        Then I should see "Zu \"\" wurden 196 Artikel gefunden!"

        When  I set the filter to:
            | filter    | value |
            | Geschmack | mild  |
        Then I should see "Zu \"\" wurden 6 Artikel gefunden!"

    @javascript
    Scenario: Infinite Scrolling is active in search results
        When I search for "art"
        Then I should see "Zu \"art\" wurden 17 Artikel gefunden!"
        And  I should see 12 elements of type "ArticleBox"
        When I scroll to the bottom of the page
        Then I should see 17 elements of type "ArticleBox" eventually

    @javascript
    Scenario Outline: Live-Search with hits
        When  I received the search-results for "<searchTerm>"
        Then  I should see "<hits> Treffer"

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
