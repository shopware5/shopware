@checkout
Feature: Checkout articles with voucher

  Scenario: I can use vouchers in my basket and pay as new customer via prepayment
    Given I am on the detail page for article 137
    Then I should see "Fahrerbrille Chronos"

    When I put the article "1" times into the basket
    Then the total sum should be "61,89 €"

    When I add the article "SW10142" to my basket
    Then the total sum should be "106,88 €"

    When I add the voucher "absolut" to my basket
    Then the total sum should be "101,88 €"

    When I remove the voucher
    Then the total sum should be "106,88 €"

    When I remove the article on position 2
    Then the total sum should be "61,89 €"

    When I add the voucher "prozentual" to my basket
    Then the total sum should be "55,89 €"

    When I follow "Zur Kasse gehen"
    And I check "skipLogin"
    And I press "Neuer Kunde"
    And I register me
      | field         | billing          |
      | customer_type | business         |
      | salutation    | mr               |
      | firstname     | Max              |
      | lastname      | Mustermann       |
      | email         | test@example.com |
      | phone         | 05555 / 555555   |
      | company       | Muster GmbH      |
      | street        | Musterstr.       |
      | streetnumber  | 55               |
      | zipcode       | 55555            |
      | city          | Musterhausen     |
      | country       | Deutschland      |

    Then I should not see "Ein Fehler ist aufgetreten!"
    And the total sum should be "55,89 €"
    And I should see "Gesamtsumme"
    And I should see "AGB und Widerrufsbelehrung"

    When I proceed to checkout
    Then I should see "Vielen Dank für Ihre Bestellung bei Shopware 4 Demo!"

  Scenario: I can use a free-shipping voucher and put articles with 7% tax in my basket
    Given the articles from "The Deli Garage" have tax id 4
    When I am on the detail page for article 39
    Then I should see "Mehrzwecknudeln"

    When  I put the article "1" times into the basket
    Then  the total sum should be "15,38 €" when shipping costs are "3,90 €" and VAT is:
      | percent | value  |
      | 7 %     | 1,01 € |

    When I add the article "SW10039" to my basket
    And I add the article "SW10172" to my basket

    Then  the total sum should be "34,35 €" when shipping costs are "3,90 €" and VAT is:
      | percent | value  |
      | 7 %     | 1,46 € |
      | 19 %    | 1,90 € |

    When I add the voucher "kostenfrei" to my basket
    Then  the total sum should be "32,45 €" when shipping costs are "0,00 €" and VAT is:
      | percent | value  |
      | 7 %     | 1,46 € |
      | 19 %    | 1,60 € |