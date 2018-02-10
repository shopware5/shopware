@checkout
Feature: Checkout articles (scenario origin is cart with one product in it)

    Background:
        Given the cart contains the following products:
            | articleId | number  | name            | quantity |
            | 181       | SW10181 | Reisekoffer Set | 1        |

    @onlypostallowed
    Scenario: I can't add product to cart using the HTTP GET method
        When I add the article "181" to my basket over HTTP GET
        Then the response status code should be 503

    @fastOrder @payment @delivery
    Scenario Outline: I can finish my order with different payment and delivery methods
        Given I proceed to checkout as:
            | field       | register[personal] | register[billing] |
            | salutation  | mr                 |                   |
            | firstname   | Max                |                   |
            | lastname    | Mustermann         |                   |
            | accountmode | 1                  |                   |
            | email       | test@example.de    |                   |
            | street      |                    | Musterstr. 55     |
            | zipcode     |                    | 55555             |
            | city        |                    | Musterhausen      |
        And   I change the payment method to <paymentMethod>:
            | field     | value            |
            | sDispatch | <shippingMethod> |
        Then  the aggregations should look like this:
            | label    | value           |
            | shipping | <shippingCosts> |
            | total    | <totalSum>      |

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

    @fastOrder @payment @shipping
    Scenario: I can finish my order with different payment and delivery methods
        Given I proceed to checkout as:
            | field       | register[personal] | register[billing] |
            | salutation  | mr                 |                   |
            | firstname   | Max                |                   |
            | lastname    | Mustermann         |                   |
            | accountmode | 1                  |                   |
            | email       | test@example.de    |                   |
            | street      |                    | Musterstr. 55     |
            | zipcode     |                    | 55555             |
            | city        |                    | Musterhausen      |
        And   I change the payment method to 2:
            | field            | value          |
            | sDebitAccount    | 123456789      |
            | sDebitBankcode   | 1234567        |
            | sDebitBankName   | Shopware Bank  |
            | sDebitBankHolder | Max Mustermann |
        Then  the aggregations should look like this:
            | label    | value    |
            | shipping | 3,90 €   |
            | total    | 128,09 € |

        When  I change the payment method to 6:
            | field         | value                  |
            | sSepaIban     | DE68210501700012345678 |
            | sSepaBic      | SHOPWAREXXX            |
            | sSepaBankName | Shopware Bank          |
        Then  the aggregations should look like this:
            | label    | value    |
            | shipping | 75,00 €  |
            | total    | 214,99 € |

        When  I proceed to checkout
        Then  I should see "Vielen Dank für Ihre Bestellung bei Shopware Demo!"

    @esd @vrrl @account @payment
    Scenario: I can checkout an esd product with vrrl and download it from my account
        Given I add the article "SW10196" to my basket
        Then  the cart should contain the following products:
            | number  | name                 | quantity | itemPrice | sum    |
            | SW10181 | Reisekoffer Set      | 1        | 139,99    | 139,99 |
            | SW10196 | ESD Download Artikel | 1        | 34,99     | 34,99  |

        When  I proceed to order confirmation with email "test@example.com" and password "shopware"
        Then  I should see "Bitte beachten Sie bei Ihrer Bestellung auch unsere Widerrufsbelehrung."
        And   I should see "Ich habe die AGB Ihres Shops gelesen und bin mit deren Geltung einverstanden."
        And   I should see "Ja, ich möchte sofort Zugang zu dem digitalen Inhalt und weiß, dass mein Widerrufsrecht mit dem Zugang erlischt."
        And   the checkbox "sAGB" is unchecked
        And   the checkbox "esdAgreementChecked" is unchecked
        And   the current payment method should be "Rechnung"

        When  I proceed to checkout
        Then  I should see "Bitte bestätigen Sie die Wiederrufsbelehrung bezüglich der digitalen Inhalte."
        And   the checkbox "sAGB" is checked
        But   the checkbox "esdAgreementChecked" is unchecked

        When  I check "esdAgreementChecked"
        And   I proceed to checkout
        Then  I should see "Vielen Dank für Ihre Bestellung bei Shopware Demo!"
        And   my finished order should look like this:
            | product                  | quantity | price    | sum      | esd |
            | Reisekoffer Set          | 1        | 139,99 € | 139,99 € |     |
            | ESD Download Artikel     | 1        | 34,99 €  | 34,99 €  | x   |
            | Warenkorbrabatt          | 1        | -2,00 €  | -2,00 €  |     |
            | Zuschlag für Zahlungsart | 1        | 5,00 €   | 5,00 €   |     |

    @configChange @esd @vrrl @account @payment
    Scenario: I can checkout an esd product without vrrl and download it from my account
        Given I add the article "SW10196" to my basket
        Then  the cart should contain the following products:
            | number  | name                 | quantity | itemPrice | sum    |
            | SW10181 | Reisekoffer Set      | 1        | 139,99    | 139,99 |
            | SW10196 | ESD Download Artikel | 1        | 34,99     | 34,99  |

        When  I disable the config "showEsdWarning"
        And   I proceed to order confirmation with email "test@example.com" and password "shopware"
        Then  I should see "Bitte beachten Sie bei Ihrer Bestellung auch unsere Widerrufsbelehrung."
        And   I should see "Ich habe die AGB Ihres Shops gelesen und bin mit deren Geltung einverstanden."
        And   the checkbox "sAGB" is unchecked
        And   the current payment method should be "Rechnung"
        But   I should not see "Ja, ich möchte sofort Zugang zu dem digitalen Inhalt und weiß, dass mein Widerrufsrecht mit dem Zugang erlischt."

        When  I proceed to checkout
        Then  I should see "Vielen Dank für Ihre Bestellung bei Shopware Demo!"
        And   my finished order should look like this:
            | product                  | quantity | price    | sum      | esd |
            | Reisekoffer Set          | 1        | 139,99 € | 139,99 € |     |
            | ESD Download Artikel     | 1        | 34,99 €  | 34,99 €  | x   |
            | Warenkorbrabatt          | 1        | -2,00 €  | -2,00 €  |     |
            | Zuschlag für Zahlungsart | 1        | 5,00 €   | 5,00 €   |     |

    @calculation
    Scenario: I add purchaseunit articles to the cart and get the correct prices
        Given I add the article "SW10003" to my basket
        And I add the article "SW10205.2" to my basket
        And I add the article "SW10205.3" to my basket
        Then  the cart should contain the following products:
            | number  | name                       | quantity | itemPrice | sum    |
            | SW10181 | Reisekoffer Set      | 1        | 139,99    | 139,99 |
            | SW10003 | Münsterländer Aperitif 16% | 1        | 14,95     | 14,95  |
            | SW10205.2 | Artikel mit Grundpreisberechnung | 1        | 400,00     | 400,00  |
            | SW10205.3 | Artikel mit Grundpreisberechnung | 1        | 50,00     | 50,00  |

    @calculation
    Scenario: I add a scale price article to the cart and get the correct price
        Given I add the article "SW10206" to my basket
        Then  the cart should contain the following products:
            | number  | name                                  | quantity | itemPrice | sum    |
            | SW10181 | Reisekoffer Set      | 1        | 139,99    | 139,99 |
            | SW10206 | Staffelung, Mindest- / Maximalabnahme | 3        | 200,00    | 600,00 |

    @calculation
    Scenario: I add an article with pseudo price to the cart and get the correct price
        Given I add the article "SW10207" to my basket
        Then  the cart should contain the following products:
            | number  | name                                  | quantity | itemPrice | sum    |
            | SW10181 | Reisekoffer Set      | 1        | 139,99    | 139,99 |
            | SW10207 | Pseudopreis | 1        | 500,00    | 500,00 |

    @calculation
    Scenario: I add an article with an additional price to the cart and get the correct price
        Given I add the article "SW10204.7" to my basket
        Then  the cart should contain the following products:
            | number  | name                                  | quantity | itemPrice | sum    |
            | SW10181 | Reisekoffer Set      | 1        | 139,99    | 139,99 |
            | SW10204.7 | Artikel mit Aufpreiskonfigurator mit Figuren und Ball-Set / 36 Monate | 1        | 279,65    | 279,65 |

  @calculation
  Scenario: I add an article with a new price group, add it to the cart and get the correct price
    Given A price group named "UNITTEST" that grants "50%" discount
    Given The article "SW10205.2" is assigned to the price group "UNITTEST"
    Then I add the article "SW10205.2" to my basket
    Then  the cart should contain the following products:
      | number    | name                                                                  | quantity | itemPrice | sum    |
      | SW10181 | Reisekoffer Set      | 1        | 139,99    | 139,99 |
      | SW10205.2 | Artikel mit Grundpreisberechnung | 1        | 200,00     | 200,00  |

    @calculation @knownFailing
    Scenario: I add an article without an purchase unit and get the correct price
        Given there is a category defined:
            | name | parentName |
            | PUT  | Deutsch    |
        And the manufacturer exist:
            | name |
            | UnitManu |
        And the customer group exist:
            | key | taxInput |
            | EK  | 0        |
        And the following product exist:
            | number  | name               | price | customergroup | category | manufacturer | purchaseunit | shippingFree |
            | SW11109 | purchase unit test | 2.13  | EK            | PUT      | UnitManu     | NULL         | 1            |
        And the cart contains the following products:
            | articleId | number  | name               | quantity   |
            | 273       | SW11109 | purchase unit test | 100        |
        Then  the aggregations should look like this:
            | label         | value   |
            | sum           | 254,00 € |
            | shipping      | 0,00 €  |
            | total         | 254,00 € |
            | sumWithoutVat | 213,45 € |
            | 19 %          | 40,55 €  |

    @calculation @repair
        Scenario: I should repair my database changes until we've implemented the new test structure :)
        Given the customer group exist:
            | key | taxInput |
            | EK  | 1        |
        And The article "SW10205.2" has no active price group
