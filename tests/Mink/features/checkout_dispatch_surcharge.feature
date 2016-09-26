@checkout
Feature: Checkout with dispatch surcharge rules

    Background:
        Given   the cart contains the following products:
            | number  | name            | quantity |
            | SW10181 | Reisekoffer Set | 1        |

  @dispatchsurcharge
  Scenario:   I register with an address that fullfills special dispatch surcharge conditions
      Given   I proceed to checkout as:
          | field             | register[personal] | register[billing] | register[shipping] |
          | customer_type     | Privatkunde        |                   |                    |
          | salutation        | ms                 |                   | ms                 |
          | firstname         | Sabine             |                   | Sabine             |
          | lastname          | Mustermann         |                   | Mustermann         |
          | accountmode       | 1                  |                   |                    |
          | email             | sabine@muster.de   |                   |                    |
          | password          | shopware           |                   |                    |
          | shippingAddress   |                    | 1                 |                    |
          | street            |                    | Musterstr. 55     | Musterstr. 12      |
          | zipcode           |                    | 55555             | 48624              |
          | city              |                    | Musterhausen      | Schöppingen        |
          | country           |                    | Deutschland       | Deutschland        |
      Then  the aggregations should look like this:
          | label         | value     |
          | sum           | 137,99 €  |
          | shipping      | 153,90 €  |
          | total         | 291,89 €  |
          | sumWithoutVat | 245,29 €  |

      When I open the order confirmation page
      And  I change my shipping address:
          | field   | address |
          | zipcode | 12345   |
      Then  the aggregations should look like this:
          | label         | value     |
          | sum           | 137,99 €  |
          | shipping      | 3,90 €    |
          | total         | 141,89 €  |
          | sumWithoutVat | 119,24 €  |