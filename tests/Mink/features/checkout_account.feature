@checkout @customergroups
Feature: Checkout articles (scenario origin is account without articles in basket)

    Background: I can login as a user with correct credentials
        Given I am on the page "Account"

    @login
    Scenario Outline: I can login, add articles to basket and finish my order
        Given I log in successful as "<user>" with email "<email>" and password "shopware"

        When  I am on the detail page for article 167
        Then  I should see "Sonnenbrille Speed Eyes"

        When  I put the article "3" times into the basket
        Then  the cart should contain 1 articles with a value of "<sum>"
        And   the aggregations should look like this:
            | label         | value           |
            | sum           | <sum>           |
            | shipping      | <shipping>      |
            | total         | <total>         |
            | sumWithoutVat | <sumWithoutVat> |
            | 19 %          | <tax>           |

        When  I proceed to order confirmation
        And   I proceed to checkout
        Then  I should see "Vielen Dank für Ihre Bestellung bei Shopware Demo!"

        Examples:
            | user                       | email             | sum     | shipping | total   | sumWithoutVat | tax    |
            | Max Mustermann             | test@example.com  | 38,47 € | 3,90 €   | 42,37 € | 35,61 €       | 6,76 € |
            | Händler Kundengruppe-Netto | mustermann@b2b.de | 32,02 € | 3,28 €   | 42,00 € | 35,30 €       | 6,70 € |

    @registration
    Scenario Outline: I can register, add articles to basket and finish my order
        Given I register me:
            | field         | register[personal] | register[billing] |
            | customer_type | <customer_type>    |                   |
            | salutation    | mr                 |                   |
            | firstname     | Max                |                   |
            | lastname      | Mustermann         |                   |
            | email         | <email>            |                   |
            | password      | shopware           |                   |
            | company       |                    | Muster GmbH       |
            | street        |                    | Musterstr. 55     |
            | zipcode       |                    | 55555             |
            | city          |                    | Musterhausen      |
            | country       |                    | Deutschland       |
        Then  I should see "Willkommen, Max Mustermann"

        When  I am on the detail page for article 167
        Then  I should see "Sonnenbrille Speed Eyes"

        When  I put the article "3" times into the basket
        Then  the cart should contain 1 articles with a value of "38,47 €"
        And   the aggregations should look like this:
            | label         | value   |
            | sum           | 38,47 € |
            | shipping      | 3,90 €  |
            | total         | 42,37 € |
            | sumWithoutVat | 35,61 € |
            | 19 %          | 6,76 €  |

        When  I proceed to order confirmation
        And   I proceed to checkout
        Then  I should see "Vielen Dank für Ihre Bestellung bei Shopware Demo!"

        Examples:
            | customer_type | email             |
            | private       | test@example.info |
            | business      | test@example.air  |