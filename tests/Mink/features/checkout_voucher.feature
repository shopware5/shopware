@checkout @voucher
Feature: Checkout articles with voucher

    @registration @noResponsive
    Scenario: I can use vouchers in my basket and pay as new customer via prepayment
        Given the cart contains the following products:
            | number  | name                 | quantity |
            | SW10137 | Fahrerbrille Chronos | 1        |
        Then  the aggregations should look like this:
            | label | value   |
            | total | 61,89 € |

        When  I add the article "SW10142" to my basket
        Then  the aggregations should look like this:
            | label | value    |
            | total | 106,88 € |

        When  I add the voucher "absolut" to my basket
        Then  the aggregations should look like this:
            | label | value    |
            | total | 101,88 € |

        When  I remove the voucher
        Then  the aggregations should look like this:
            | label | value    |
            | total | 106,88 € |

        When  I remove the article on position 2
        Then  the aggregations should look like this:
            | label | value   |
            | total | 61,89 € |

        When  I add the voucher "prozentual" to my basket
        Then  the aggregations should look like this:
            | label | value   |
            | total | 55,89 € |

        When  I follow the link "checkout" of the page "CheckoutCart"
        And   I register me:
            | field         | register[personal] | register[billing] |
            | customer_type | business           |                   |
            | salutation    | mr                 |                   |
            | firstname     | Max                |                   |
            | lastname      | Mustermann         |                   |
            | skipLogin     | 1                  |                   |
            | email         | test@example.com   |                   |
            | company       |                    | Muster GmbH       |
            | street        |                    | Musterstr. 55     |
            | zipcode       |                    | 55555             |
            | city          |                    | Musterhausen      |
            | country       |                    | Deutschland       |

        Then  I should not see "Ein Fehler ist aufgetreten!"
        And   the aggregations should look like this:
            | label | value   |
            | total | 55,89 € |
        And   I should see "Gesamtsumme"
        And   I should see "AGB und Widerrufsbelehrung"

        When  I proceed to checkout
        Then  I should see "Vielen Dank für Ihre Bestellung bei Shopware Demo!"

    @registration @noEmotion
    Scenario: I can use vouchers in my basket and pay as new customer via prepayment
        Given the cart contains the following products:
            | number  | name                 | quantity |
            | SW10137 | Fahrerbrille Chronos | 1        |
        Then  the aggregations should look like this:
            | label | value   |
            | total | 61,89 € |

        When  I add the article "SW10142" to my basket
        Then  the aggregations should look like this:
            | label | value    |
            | total | 106,88 € |

        When  I add the voucher "absolut" to my basket
        Then  the aggregations should look like this:
            | label | value    |
            | total | 101,88 € |

        When  I remove the voucher
        Then  the aggregations should look like this:
            | label | value    |
            | total | 106,88 € |

        When  I remove the article on position 2
        Then  the aggregations should look like this:
            | label | value   |
            | total | 61,89 € |

        When  I add the voucher "prozentual" to my basket
        Then  the aggregations should look like this:
            | label | value   |
            | total | 55,89 € |

        When  I follow the link "checkout" of the page "CheckoutCart"
        And   I register me:
            | field         | register[personal] | register[billing] |
            | customer_type | business           |                   |
            | salutation    | mr                 |                   |
            | firstname     | Max                |                   |
            | lastname      | Mustermann         |                   |
            | skipLogin     | 1                  |                   |
            | email         | test@example.com   |                   |
            | company       |                    | Muster GmbH       |
            | street        |                    | Musterstr. 55     |
            | zipcode       |                    | 55555             |
            | city          |                    | Musterhausen      |
            | country       |                    | Deutschland       |

        Then  I should not see "Ein Fehler ist aufgetreten!"
        And   the aggregations should look like this:
            | label | value   |
            | total | 55,89 € |

        When  I press "Weiter"
        Then  I should see "Gesamtsumme"
        And   I should see "AGB und Widerrufsbelehrung"

        When  I proceed to checkout
        Then  I should see "Vielen Dank für Ihre Bestellung bei Shopware Demo!"

    Scenario: I can use a free-shipping voucher and put articles with 7% tax in my basket
        Given the articles from "The Deli Garage" have tax id 4
        And   the cart contains the following products:
            | number  | name            | quantity |
            | SW10038 | Mehrzwecknudeln | 1        |
        Then  the aggregations should look like this:
            | label | value   |
            | total | 15,38 € |
            | 7 %   | 1,01 €  |

        When  I add the article "SW10039" to my basket
        And   I add the article "SW10172" to my basket

        Then  the aggregations should look like this:
            | label | value   |
            | total | 34,35 € |
            | 7 %   | 1,46 €  |
            | 19 %  | 1,90 €  |

        When  I add the voucher "kostenfrei" to my basket
        Then  the aggregations should look like this:
            | label | value   |
            | total | 32,45 € |
            | 7 %   | 1,46 €  |
            | 19 %  | 1,60 €  |
