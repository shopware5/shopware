@checkout
Feature: Checkout articles (scenario origin is checkout confirm page logged in as default private customer)

    Background:
        Given the cart contains the following products:
            | number  | name              | quantity | itemPrice | sum    |
            | SW10084 | Back-Set 3 teilig | 15       | 14,99     | 224,85 |
        And   I proceed to order confirmation with email "test@example.com" and password "shopware"

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