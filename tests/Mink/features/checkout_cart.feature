@checkout
Feature: Checkout articles (scenario origin is cart with articles in it)

  Background:
    Given I am on the detail page for article 167
    Then  I should see "Sonnenbrille Speed Eyes"

    When  I put the article "3" times into the basket
    Then  the cart should contain 1 articles with a value of "38,47 €"
    And   the aggregations should look like this:
      | aggregation   | value   |
      | sum           | 38,47 € |
      | shipping      | 3,90 €  |
      | total         | 42,37 € |
      | sumWithoutVat | 35,61 € |
      | 19 %          | 6,76 €  |

    When I proceed to confirm
    Then I should be on the page "Account"

    @noEmotion
    Scenario Outline: I can finish my order
      Given I register me
        | field                | billing         |
        | customer_type        | <customer_type> |
        | salutation           | mr              |
        | firstname            | Max             |
        | lastname             | Mustermann      |
        | email                | <email>         |
        | password             | shopware        |
        | passwordConfirmation | shopware        |
        | phone                | 05555 / 555555  |
        | company              | Muster GmbH     |
        | street               | Musterstr.      |
        | streetnumber         | 55              |
        | zipcode              | 55555           |
        | city                 | Musterhausen    |
        | country              | Deutschland     |

      When I follow "Weiter"
      Then the aggregations should look like this:
        | aggregation   | value   |
        | sum           | 38,47 € |
        | shipping      | 3,90 €  |
        | total         | 42,37 € |
        | sumWithoutVat | 35,61 € |
        | 19 %          | 6,76 €  |


    Examples:
    | customer_type | email            |
    | private       | test@example.gov |
    | business      | test@example.biz |