@checkout
Feature: Checkout esd article

  @esd @account @payment @search @listing
    Scenario: I can buy an esd article and download it from my account
      Given I log in successful as "Max Mustermann" with email "test@example.com" and password "shopware"
      Then  my current payment method should be "Vorkasse"

      When  I follow "Zahlungsart ändern"
       And  I change my payment method to 4
      Then  my current payment method should be "Rechnung"

      When  I search for "ESD"
      Then  I should see "Zu \"ESD\" wurden 1 Artikel gefunden!"

      When  I order the article on position 1
      Then  the cart should contain 1 articles with a value of "37,99 €"
      And   I should see "ESD Download Artikel"
      And   I should see "Zuschlag für Zahlungsart"
      And   the total sum should be "37,99 €" when shipping costs are "0,00 €" and VAT is:
        | percent | value  |
        | 19 %    | 6,07 € |

      When  I proceed to checkout
      Then  I should see "Vielen Dank für Ihre Bestellung bei Shopware 4 Demo!"
       And  my finished order should look like this:
         | product                  | quantity | price   | sum     | esd |
         | ESD Download Artikel     | 1        | 34,99 € | 34,99 € |  x  |
         | Warenkorbrabatt          | 1        | -2,00 € | -2,00 € |     |
         | Zuschlag für Zahlungsart | 1        | 5,00 €  | 5,00 €  |     |

      When  I follow "Zahlungsart ändern"
      And   I change my payment method to 5
      Then  my current payment method should be "Vorkasse"