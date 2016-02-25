@listing
Feature: Show Listing

    @noResponsive
    Scenario: I can change the view method
        Given I am on the listing page for category 3 on page 1
        Then  the articles should be shown in a table-view

        When  I follow "Listen-Ansicht"
        Then  the articles should be shown in a list-view

    @filter @noResponsive
    Scenario Outline: I can filter the articles by supplier
        Given I am on the listing page:
            | parameter | value |
            | sPage     | 1     |
            | sPerPage  | 24    |
        When  I set the filter to:
            | filter     | value      |
            | Hersteller | <supplier> |
        Then  I should see <articles> elements of type "ArticleBox"

        Examples:
            | supplier             | articles |
            | Sonnenschirm Versand | 5        |
            | Teapavilion          | 23       |

    @filter @noResponsive
    Scenario: I can filter the articles by custom filters
        Given I am on the listing page:
            | parameter | value |
            | sCategory | 21    |

        When  I set the filter to:
            | filter        | value     |
            | Geschmack     | mild      |
            | Flaschengröße | 0,5 Liter |
            | Alkoholgehalt | >30%      |
        Then  I should see 4 elements of type "ArticleBox"

        When  I set the filter to:
            | filter          | value   |
            | Trinktemperatur | Gekühlt |
            | Farbe           | rot     |
        Then  I should see 2 elements of type "ArticleBox"

        When  I reset all filters
        Then  I should see 10 elements of type "ArticleBox"

    @filter @noEmotion @javascript
    Scenario: I can filter the articles by custom filters
        Given I am on the listing page:
            | parameter | value |
            | sCategory | 21    |

        When  I set the filter to:
            | filter        | value     |
            | Geschmack     | mild      |
            | Flaschengröße | 0,5 Liter |
            | Alkoholgehalt | >30%      |
        Then  I should see 4 elements of type "ArticleBox"

        When  I set the filter to:
            | filter          | value   |
            | Trinktemperatur | Gekühlt |
            | Farbe           | rot     |
        Then  I should see 2 elements of type "ArticleBox"

        When  I reset all filters
        Then  I should see 10 elements of type "ArticleBox"

    @sort @javascript
    Scenario: I can change the sort
        Given I am on the listing page:
            | parameter | value |
            | sPerPage  | 12    |
            | sSort     | 1     |
        Then  I should see "Kundengruppen Brutto / Nettopreise"

        When  I select "Niedrigster Preis" from "o"
        Then  I should see the article "Fliegenklatsche lila" in listing
        But   I should not see the article "Kundengruppen Brutto / Nettopreise" in listing

        When  I select "Höchster Preis" from "o"
        Then  I should see the article "Dart Automat Standgerät" in listing
        But   I should not see the article "Fliegenklatsche lila" in listing

        When  I select "Artikelbezeichnung" from "o"
        Then  I should see the article "Artikel mit Abverkauf" in listing
        But   I should not see the article "Dart Automat Standgerät" in listing

        When  I select "Erscheinungsdatum" from "o"
        Then  I should see the article "Kundengruppen Brutto / Nettopreise" in listing
        But   I should not see the article "Artikel mit Abverkauf" in listing

    @perPage @noResponsive @javascript
    Scenario Outline: I can change the articles per page
        Given I am on the listing page:
            | parameter | value  |
            | sPerPage  | <from> |
        Then  I should see <from> elements of type "ArticleBox"

        When  I select "<to>" from "n"
        Then  I should see <to> elements of type "ArticleBox"

        Examples:
            | from | to |
            | 12   | 24 |
            | 24   | 36 |
            | 36   | 48 |
            | 48   | 12 |

    @customergroups
    Scenario:
        Given I am on the page "Account"
        And   I log in successful as "Händler Kundengruppe-Netto" with email "mustermann@b2b.de" and password "shopware"
        And   I am on the listing page:
            | parameter | value |
            | sCategory | 30    |

        Then  the article on position 1 should have this properties:
            | property | value   |
            | price    | 42,02 € |
        And   the article on position 2 should have this properties:
            | property | value   |
            | price    | 16,81 € |
        And   the article on position 3 should have this properties:
            | property | value  |
            | price    | 6,71 € |

        When  I am on the page "Account"
        And   I log me out
        And   I am on the listing page:
            | parameter | value |
            | sCategory | 30    |

        Then  the article on position 1 should have this properties:
            | property | value   |
            | price    | 50,00 € |
        And   the article on position 2 should have this properties:
            | property | value   |
            | price    | 20,00 € |
        And   the article on position 3 should have this properties:
            | property | value  |
            | price    | 7,99 € |

    @browsing @noResponsive
    Scenario Outline: I can browse through the listing
        Given I am on the listing page:
            | parameter | value     |
            | sPage     | 4         |
            | sSort     | 5         |
            | sPerPage  | <perPage> |

        Then  I should see <perPage> elements of type "ArticleBox"

        When  I browse to previous page 3 times
        Then  I should not be able to browse to previous page

        When  I browse to next page <countNextPage> times
        Then  I should see "iPadtasche mit Stiftmappe"
        And   I should see "Kickerball Kork"

        When  I browse to page <lastPage>
        Then  I should see <countLastPage> elements of type "ArticleBox"
        And   I should not be able to browse to next page
        And   I should not be able to browse to page 1

        Examples:
            | perPage | countNextPage | lastPage | countLastPage |
            | 12      | 6             | 8        | 12            |
            | 24      | 3             | 8        | 24            |
            | 36      | 2             | 6        | 16            |
            | 48      | 1             | 5        | 4             |
