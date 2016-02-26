@checkout
Feature: Checkout articles (scenario origin is checkout confirm page logged in as default private customer)

    Background:
        Given the cart contains the following products:
            | number  | name              | quantity | itemPrice | sum    |
            | SW10084 | Back-Set 3 teilig | 15       | 14,99     | 224,85 |
        And   I proceed to order confirmation with email "test@example.com" and password "shopware"

    @billing
    Scenario: I can change the billing address and switch to old address on next order
        When  I change my billing address:
            | field     | register[personal] | register[billing] |
            | firstname | Hans               |                   |
            | lastname  | Meier              |                   |
            | country   |                    | Schweiz           |
        Then  the aggregations should look like this:
            | label         | value    |
            | sum           | 222,85 € |
            | shipping      | 3,90 €   |
            | total         | 226,75 € |
            | sumWithoutVat | 190,55 € |
            | 19 %          | 36,20 €  |

        When  I proceed to checkout
        Then  I should see "Vielen Dank für Ihre Bestellung bei Shopware Demo!"

        When the cart contains the following products:
            | number  | name                       | quantity | itemPrice | sum    |
            | SW10233 | Dartpfeil Steel Smiley 745 | 25       | 13,99     | 349,75 |
            | SW10021 | Glas -Teetasse 0,25l       | 12       | 13,00     | 156,00 |
        And   I proceed to order confirmation
        Then  the aggregations should look like this:
            | label         | value    |
            | sum           | 503,75 € |
            | shipping      | 3,90 €   |
            | total         | 507,65 € |
            | sumWithoutVat | 426,60 € |
            | 19 %          | 81,05 €  |

        When  I follow the link "otherButton" of the element "CheckoutBilling"
        Then  I should see "Wählen Sie eine Rechnungsadresse aus"

        When  I choose the address "shopware AG, Max Mustermann"
        Then  the aggregations should look like this:
            | label         | value    |
            | sum           | 503,75 € |
            | shipping      | 3,90 €   |
            | total         | 507,65 € |
            | sumWithoutVat | 426,60 € |
            | 19 %          | 81,05 €  |

        When  I proceed to checkout
        Then  I should see "Vielen Dank für Ihre Bestellung bei Shopware Demo!"

    @shipping
    Scenario: I can change the shipping address and switch to old address on next order
        When  I change my shipping address:
            | field     | register[shipping] |
            | firstname | Hans               |
            | lastname  | Meier              |
            | country   | Schweiz            |
        Then  the aggregations should look like this:
            | label    | value    |
            | sum      | 187,00 € |
            | shipping | 21,00 €  |
            | total    | 208,00 € |
        And   I should not see "MwSt."

        When  I proceed to checkout
        Then  I should see "Vielen Dank für Ihre Bestellung bei Shopware Demo!"

        When the cart contains the following products:
            | number  | name                       | quantity | itemPrice | sum    |
            | SW10233 | Dartpfeil Steel Smiley 745 | 25       | 11,76     | 294,00 |
            | SW10021 | Glas -Teetasse 0,25l       | 12       | 10,92     | 131,04 |
        And   I proceed to order confirmation
        Then  the aggregations should look like this:
            | label    | value    |
            | sum      | 423,04 € |
            | shipping | 21,00 €  |
            | total    | 444,04 € |
        And   I should not see "MwSt."

        When  I follow the link "otherButton" of the element "CheckoutShipping"
        Then  I should see "Wählen Sie eine Lieferadresse aus"

        When  I choose the address "shopware AG, Max Mustermann"
        Then  the aggregations should look like this:
            | label         | value    |
            | sum           | 503,75 € |
            | shipping      | 3,90 €   |
            | total         | 507,65 € |
            | sumWithoutVat | 426,60 € |
            | 19 %          | 81,05 €  |

        When  I proceed to checkout
        Then  I should see "Vielen Dank für Ihre Bestellung bei Shopware Demo!"

    @payment @delivery
    Scenario Outline: I can finish my order with different payment and delivery methods
        When  I change the payment method to <paymentMethod>
        And   I change the shipping method to <shippingMethodId>

        Then  the current payment method should be "<shippingMethodName>"
        And   the aggregations should look like this:
            | label    | value           |
            | shipping | <shippingCosts> |
            | total    | <totalSum>      |

        When  I proceed to checkout
        Then  I should see "Vielen Dank für Ihre Bestellung bei Shopware Demo!"

        Examples:
            | paymentMethod | shippingMethodId | shippingMethodName | shippingCosts | totalSum |
            | 3             | 9                | Nachnahme          | 3,90 €        | 226,75 € |
            | 4             | 9                | Rechnung           | 3,90 €        | 231,75 € |
            | 5             | 14               | Vorkasse           | 9,90 €        | 232,75 € |
            | 3             | 14               | Nachnahme          | 9,90 €        | 232,75 € |
            | 4             | 14               | Rechnung           | 9,90 €        | 237,75 € |

    @delivery @payment
    Scenario Outline: I can change the shipping method to Express and pay via debit
        When  I change the payment method to 2:
            | field            | value          |
            | sDebitAccount    | 123456789      |
            | sDebitBankcode   | 1234567        |
            | sDebitBankName   | Shopware Bank  |
            | sDebitBankHolder | Max Mustermann |
        And   I change the shipping method to <shippingMethod>

        Then  the current payment method should be "Lastschrift"
        And   I should see "Abschlag für Zahlungsart"
        And   the aggregations should look like this:
            | label    | value           |
            | shipping | <shippingCosts> |
            | total    | <totalSum>      |

        When  I proceed to checkout
        Then  I should see "Vielen Dank für Ihre Bestellung bei Shopware Demo!"

        Examples:
            | shippingMethod | shippingCosts | totalSum |
            | 9              | 3,90 €        | 204,46 € |
            | 14             | 9,90 €        | 210,46 € |

    @configChange
    Scenario: I can customize the checkout confirm page
        Then  I should see "Bitte beachten Sie bei Ihrer Bestellung auch unsere Widerrufsbelehrung."
        And   I should see "Ich habe die AGB Ihres Shops gelesen und bin mit deren Geltung einverstanden."

        When  I disable the config "revocationNotice"
        And   I reload the page
        Then  I should not see "Bitte beachten Sie bei Ihrer Bestellung auch unsere Widerrufsbelehrung."
        But   I should see "Ich habe die AGB Ihres Shops gelesen und bin mit deren Geltung einverstanden."