@checkout
Feature: Checkout articles (scenario origin is cart with articles in it)

    Background:
        Given I am on the detail page for article 181
        And   I put the article into the basket
        Then  the element "CartPosition" should have the content:
            | position | content         |
            | name     | Reisekoffer Set |
            | number   | SW10181         |

        When  I follow the link "checkout" of the page "CheckoutCart"
        Then  I should be on the page "Account"

    @fastOrder @payment @delivery @noEmotion
    Scenario Outline: I can finish my order with different payment and delivery methods
        Given I register me:
            | field         | register[personal] | register[billing] |
            | salutation    | mr                 |                   |
            | firstname     | Max                |                   |
            | lastname      | Mustermann         |                   |
            | skipLogin     | 1                  |                   |
            | email         | test@example.de    |                   |
            | phone         | 05555 / 555555     |                   |
            | street        |                    | Musterstr. 55     |
            | zipcode       |                    | 55555             |
            | city          |                    | Musterhausen      |

        And   I submit the form "shippingPaymentForm" on page "CheckoutConfirm" with:
            | field     | value            |
            | payment   | <paymentMethod>  |
            | sDispatch | <shippingMethod> |

        Then  the page "CheckoutCart" should have the content:
            | position | content         |
            | shipping | <shippingCosts> |
            | totalSum | <totalSum>      |

        When  I proceed to checkout
        Then  I should see "Vielen Dank für Ihre Bestellung bei Shopware Demo!"

    Examples:
        | paymentMethod | shippingMethod | shippingCosts | totalSum |
        | 5             | 9              | 3,90 €        | 141,89 € |
        | 3             | 9              | 3,90 €        | 141,89 € |
        | 4             | 9              | 3,90 €        | 146,89 € |
        | 5             | 14             | 9,90 €        | 147,89 € |
        | 3             | 14             | 9,90 €        | 147,89 € |
        | 4             | 14             | 9,90 €        | 152,89 € |

    @fastOrder @payment @shipping @noEmotion
    Scenario: I can finish my order with different payment and delivery methods
        Given I register me:
            | field         | register[personal] | register[billing] |
            | salutation    | mr                 |                   |
            | firstname     | Max                |                   |
            | lastname      | Mustermann         |                   |
            | skipLogin     | 1                  |                   |
            | email         | test@example.de    |                   |
            | phone         | 05555 / 555555     |                   |
            | street        |                    | Musterstr. 55     |
            | zipcode       |                    | 55555             |
            | city          |                    | Musterhausen      |

        And   I submit the form "shippingPaymentForm" on page "CheckoutConfirm" with:
            | field            | value          |
            | payment          | 2              |
            | sDebitAccount    | 123456789      |
            | sDebitBankcode   | 1234567        |
            | sDebitBankName   | Shopware Bank  |
            | sDebitBankHolder | Max Mustermann |

        Then  the page "CheckoutCart" should have the content:
            | position | content  |
            | shipping | 3,90 €   |
            | totalSum | 128,09 € |

        When  I follow the link "changeButton" of the element "CheckoutPayment"
        And   I submit the form "shippingPaymentForm" on page "CheckoutConfirm" with:
            | field         | value                  |
            | payment       | 6                      |
            | sSepaIban     | DE68210501700012345678 |
            | sSepaBic      | SHOPWAREXXX            |
            | sSepaBankName | Shopware Bank          |

        Then  the page "CheckoutCart" should have the content:
            | position | content  |
            | shipping | 75,00 €  |
            | totalSum | 214,99 € |

        When  I proceed to checkout
        Then  I should see "Vielen Dank für Ihre Bestellung bei Shopware Demo!"
