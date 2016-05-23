@checkout
Feature: Checkout articles

    Background:
        Given I am on the page "Account"
        And   I log in successful as "Max Mustermann" with email "test@example.com" and password "shopware"
        And   the cart contains the following products:
            | number  | name                    | quantity |
            | SW10167 | Sonnenbrille Speed Eyes | 3        |

    Scenario: I can put articles to the basket, check all prices and pay via C.O.D. service
        Given the cart should contain 1 articles with a value of "38,47 €"
        And   the aggregations should look like this:
            | label         | value   |
            | sum           | 38,47 € |
            | shipping      | 3,90 €  |
            | total         | 42,37 € |
            | sumWithoutVat | 35,61 € |
        When  I add the article "SW10170" to my basket
        Then  the cart should contain 2 articles with a value of "78,42 €"
        And   the aggregations should look like this:
            | label         | value   |
            | sum           | 78,42 € |
            | shipping      | 3,90 €  |
            | total         | 82,32 € |
            | sumWithoutVat | 69,18 € |
            | 19 %          | 13,14 € |

        When  I remove the article on position 1
        Then  the cart should contain 1 articles with a value of "37,95 €"
        And   the aggregations should look like this:
            | label         | value   |
            | sum           | 37,95 € |
            | shipping      | 3,90 €  |
            | total         | 41,85 € |
            | sumWithoutVat | 35,17 € |
            | 19 %          | 6,68 €  |

        When  I proceed to order confirmation
        And   I change the payment method to 3
        Then  the current payment method should be "Nachnahme"
        And   the aggregations should look like this:
            | label         | value   |
            | sum           | 37,95 € |
            | shipping      | 3,90 €  |
            | total         | 41,85 € |
            | sumWithoutVat | 35,17 € |
            | 19 %          | 6,68 €  |

        When  I proceed to checkout
        Then  I should see "Vielen Dank für Ihre Bestellung bei Shopware Demo!"

    @shipping @payment
    Scenario: I can change the shipping-country to a non-EU-country and back and pay via bill
        Given I proceed to order confirmation
        And   I change my shipping address:
            | field   | address |
            | country | Schweiz            |
        Then  the aggregations should look like this:
            | label    | value   |
            | sum      | 32,02 € |
            | shipping | 21,00 € |
            | total    | 53,02 € |
        And   I should not see "MwSt."

        When  I change my shipping address:
            | field   | address |
            | country | Deutschland        |
        Then  the aggregations should look like this:
            | label    | value   |
            | shipping | 3,90 €  |
            | total    | 42,37 € |
            | 19 %     | 6,76 €  |

        When  I change the payment method to 4
        Then  the current payment method should be "Rechnung"
        And   I should see "Zuschlag für Zahlungsart"
        And   the aggregations should look like this:
            | label         | value   |
            | sum           | 43,47 € |
            | shipping      | 3,90 €  |
            | total         | 47,37 € |
            | sumWithoutVat | 39,81 € |
            | 19 %          | 7,56 €  |
        And   I should see "AGB und Widerrufsbelehrung"

        When  I proceed to checkout
        Then  I should see "Vielen Dank für Ihre Bestellung bei Shopware Demo!"
