@checkout
Feature: Checkout articles

  Background: I can login as a user with correct credentials
    Given I log in successful as "test@example.com" with password "shopware"
    Then  I should see "Willkommen, Max Mustermann"
    When  I follow "Zahlungsart ändern"
    Then  I select "5" from "register[payment]"
    And   I press "Ändern"
    Then  I should see "Ihre Zahlungsweise wurde erfolgreich gespeichert"

    Given I am on the detail page for article 167
    Then  I should see "Sonnenbrille Speed Eyes"

    When  I put the article "3" times into the basket
    Then  The sum should be "38,47 €"
    And   The shipping costs should be "3,90 €"
    And   The total sum should be "42,37 €"
    And   The sum without VAT should be "35,61 €"
    And   The VAT should be "6,76 €"

  Scenario: I can put articles to the basket, check all prices and pay via C.O.D. service
    Given I add the article "SW10170" to my basket
    Then The sum should be "78,42 €"
    And The shipping costs should be "3,90 €"
    And The total sum should be "82,32 €"
    And The sum without VAT should be "69,18 €"
    And The VAT should be "13,14 €"

    When I remove the article on position 1
    Then The sum should be "37,95 €"
    And The shipping costs should be "3,90 €"
    And The total sum should be "41,85 €"
    And The sum without VAT should be "35,17 €"
    And The VAT should be "6,68 €"

    When I follow "Zur Kasse gehen"
    And I change my payment method to "3"
    Then I should see "Nachnahme"
    And The sum should be "37,95 €"
    And The shipping costs should be "3,90 €"
    And The total sum should be "41,85 €"
    And The sum without VAT should be "35,17 €"
    And The VAT should be "6,68 €"
    And I should see "AGB und Widerrufsbelehrung"

    When I proceed to checkout
    Then I should see "Vielen Dank für Ihre Bestellung bei Shopware 4 Demo!"


  Scenario: I can change the shipping-country to a non-EU-country and back and pay via bill
    Given I change my shipping address on confirm page:
      | field   | value   |
      | country | Schweiz |

    Then The sum should be "32,02 €"
    And The shipping costs should be "21,00 €"
    And The total sum should be "53,02 €"
    And I should not see "MwSt."

    When I change my shipping address on confirm page:
      | field   | value       |
      | country | Deutschland |

    Then The sum should be "38,47 €"
    And The shipping costs should be "3,90 €"
    And The total sum should be "42,37 €"
    And The sum without VAT should be "35,61 €"
    And The VAT should be "6,76 €"

    When I change my payment method to "4"
    Then I should see "Rechnung"
    And I should see "Zuschlag für Zahlungsart"
    And The sum should be "43,47 €"
    And The shipping costs should be "3,90 €"
    And The total sum should be "47,37 €"
    And The sum without VAT should be "39,81 €"
    And The VAT should be "7,56 €"
    And I should see "AGB und Widerrufsbelehrung"

    When I proceed to checkout
    Then I should see "Vielen Dank für Ihre Bestellung bei Shopware 4 Demo!"

  Scenario: I can change the delivery to Express and pay via debit
    Given I change my delivery to "14"
    Then The sum should be "38,47 €"
    And The shipping costs should be "9,90 €"
    And The total sum should be "48,37 €"
    And The sum without VAT should be "40,65 €"
    And The VAT should be "7,72 €"

    When I change my payment method to debit using account of "Max Mustermann" (no. "123456789") of bank "shopware Bank" (code "1234567")
    Then I should see "Lastschrift"
    And I should see "Abschlag für Zahlungsart"
    And The sum should be "34,62 €"
    And The shipping costs should be "9,90 €"
    And The total sum should be "44,52 €"
    And The sum without VAT should be "37,42 €"
    And The VAT should be "7,10 €"
    And I should see "AGB und Widerrufsbelehrung"

    When I proceed to checkout
    Then I should see "Vielen Dank für Ihre Bestellung bei Shopware 4 Demo!"

  Scenario: I can change the currency and pay via prepayment
    Given I press "USD"
    Then The sum should be "52,41 $"
    And The shipping costs should be "5,31 $"
    And The total sum should be "57,72 $"
    And The sum without VAT should be "48,51 $"
    And The VAT should be "9,21 $"

    When  I press "EUR"
    Then  The sum should be "38,47 €"
    And   The shipping costs should be "3,90 €"
    And   The total sum should be "42,37 €"
    And   The sum without VAT should be "35,61 €"
    And   The VAT should be "6,76 €"
    And   I should see "AGB und Widerrufsbelehrung"

    When  I proceed to checkout
    Then  I should see "Vielen Dank für Ihre Bestellung bei Shopware 4 Demo!"

  @javascript
  Scenario: I can change the language and pay via PayPal

    When  I select "English" from "__shop"
    Then  I should see "Your shopping cart does not contain any products"

    When  I select "Deutsch" from "__shop"
    Then  I should see "Sonnenbrille Speed Eyes"

    When I follow "Zur Kasse gehen"
    Then I should see "AGB und Widerrufsbelehrung"
    When I proceed to checkout
    Then I should see "Vielen Dank für Ihre Bestellung bei Shopware 4 Demo!"