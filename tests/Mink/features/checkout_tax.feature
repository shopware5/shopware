@checkout
Feature: Check that cart value for tax is calculated correctly

  Background:
      Given   I am on the page "Account"
      And     I log in successful as "Max Mustermann" with email "test@example.com" and password "shopware"
      And     the cart contains the following products:
          | number  | name                    | quantity |
          | SW10170 | Sonnenbrille "Red"      | 1        |
  @taxation
  Scenario Outline: I can change the shipping country to a country with different taxation
      Given I proceed to order confirmation
      And   I change my shipping address:
        | field   | address    |
        | country | Österreich |
      Then  the "shipping" address should be "<company>, <firstname> <lastname>, <street>, <zipcode> <city>, <country>"
      Examples:
        | company     | firstname | lastname   | street              | zipcode | city        | country     |
        | shopware AG | Max       | Mustermann | Mustermannstraße 92 | 48624   | Schöppingen | Österreich  |
      Then I open the order confirmation page
      And  the aggregations should look like this:
        | label         | value   |
        | sum           | 42,65 € |
        | shipping      | 24,99 € |
        | total         | 67,64 € |
        | sumWithoutVat | 50,86 € |
        | 33 %          | 16,78 € |

      When  I change my shipping address:
        | field   | address     |
        | country | Deutschland |
      Then  the "shipping" address should be "<company>, <firstname> <lastname>, <street>, <zipcode> <city>, <country>"
      Examples:
        | company     | firstname | lastname   | street              | zipcode | city        | country     |
        | shopware AG | Max       | Mustermann | Mustermannstraße 92 | 48624   | Schöppingen | Deutschland |
      Then I open the order confirmation page
      And  the aggregations should look like this:
        | label         | value   |
        | sum           | 37,95 € |
        | shipping      | 3,90 €  |
        | total         | 41,85 € |
        | sumWithoutVat | 35,17 € |
        | 19 %          | 6,68 €  |