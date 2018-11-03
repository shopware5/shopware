@checkout
Feature: Check that cart value is not negative

    Background:
        Given I am on the detail page for article 4

    Scenario:
        When    I click "addToCartButton" to add the article to the cart
        And     I open the cart page
        Then    the cart should contain the following products:
            | number  | name                 | quantity | itemPrice | sum    |
            | SW10004 | Latte Macchiato 17%  | 1        | 7,99      | 7,99   |
        And     the aggregations should look like this:
            | label         | value   |
            | sum           | 10,99 € |
            | shipping      | 3,90 €  |
            | total         | 14,89 € |
            | sumWithoutVat | 12,51 € |
            | 19 %          | 2,38 €  |

        When    I remove the article on position 1
        Then    the cart should contain 0 articles with a value of "0,00 €"