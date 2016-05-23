@listing
Feature: Show Listing

    @filter @javascript
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
