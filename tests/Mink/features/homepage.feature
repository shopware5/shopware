Feature: General functionality

    @currency @noResponsive
    Scenario Outline: I can put articles into my basket in different currencies
        Given I am on the listing page:
            | parameter | value |
            | sPerPage  | 24    |
            | sSort     | 5     |

        When  I change the currency to "<currency>"
        Then  the article on position 11 should have this properties:
            | property | value          |
            | price    | <price_config> |
        And   the article on position 13 should have this properties:
            | property | value          |
            | price    | <price_normal> |
        And   the article on position 15 should have this properties:
            | property | value        |
            | price    | <price_base> |

        When  I follow the link "details" of the element "ArticleBox" on position 11
        Then  I should see "<price_config_detail>"

        When  I put the article into the basket
        And   I go to the listing page:
            | parameter | value |
            | sPerPage  | 24    |
            | sSort     | 5     |
        And   I follow the link "details" of the element "ArticleBox" on position 13
        Then  I should see "<price_normal>"

        When  I put the article into the basket
        And   I go to the listing page:
            | parameter | value |
            | sPerPage  | 24    |
            | sSort     | 5     |
        And   I follow the link "details" of the element "ArticleBox" on position 15
        Then  I should see "<price_base>"

        When  I put the article into the basket
        Then  the aggregations should look like this:
            | label    | value      |
            | shipping | <shipping> |
            | total    | <total>    |
            | 19 %     | <vat>      |

        Examples:
            | currency | price_config | price_normal | price_base | price_config_detail | shipping | total     | vat      |
            | USD      | 162,14       | 13.625,00    | 68,13      | 243,21 $            | 5,31     | 13.938,92 | 2.225,55 |
            | EUR      | 119,00       | 10.000,00    | 50,00      | 178,50 €            | 3,90     | 10.230,40 | 1.633,42 |

    @configChange @maintenance
    Scenario: I can close the shop due to maintenance
        Given I enable the config "setoffline"
        And   I go to the homepage
        Then  the response status code should be 503
        And   I should not see "Mein Konto"
        And   I should not see "Service/Hilfe"
        But   I should see "Wegen Wartungsarbeiten nicht erreichbar!"
        And   I should see "Aufgrund nötiger Wartungsarbeiten ist der Shop zur Zeit nicht erreichbar."

        When  I follow "Beispiele"
        Then  I should not see "Weitere Artikel in dieser Kategorie"
        But   I should see "Wegen Wartungsarbeiten nicht erreichbar!"

        When  I follow "Kontakt"
        Then  I should not see "Kontaktformular"
        But   I should see "Wegen Wartungsarbeiten nicht erreichbar!"
