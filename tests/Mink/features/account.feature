@account
Feature: My account (without changing login data)

    Background:
        Given I am on the page "Account"
        And   I log in successful as "Max Mustermann" with email "test@example.com" and password "shopware"

    @password
    Scenario Outline: I can't change my password, when something is wrong
        When  I follow "Persönliche Daten ändern"
        And   I change my password from "<password>" to "<new_password>" with confirmation "<confirmation>"
        Then  I should see "<message>"

        Examples:
            | password  | new_password | confirmation | message                                                                  |
            |           |              |              | Das aktuelle Passwort stimmt nicht!                                      |
            | shopware  |              |              | Bitte wählen Sie ein Passwort, welches aus mindestens 8 Zeichen besteht. |
            | shopware  | shopware4    |              | Die Passwörter stimmen nicht überein.                                    |
            | shopware  | sw4          | sw4          | Bitte wählen Sie ein Passwort, welches aus mindestens 8 Zeichen besteht. |
            | shopware  | shopware4    | shopware5    | Die Passwörter stimmen nicht überein.                                    |
            | shopware4 | shopware5    | shopware5    | Das aktuelle Passwort stimmt nicht!                                      |

    @email
    Scenario Outline: I can't change my email, when something is wrong
        When  I follow "Persönliche Daten ändern"
        And   I change my email with password "<password>" to "<new_email>" with confirmation "<confirmation>"
        Then  I should see "<message>"

        Examples:
            | password  | new_email         | confirmation      | message                                        |
            |           |                   |                   | Das aktuelle Passwort stimmt nicht!            |
            | shopware  |                   |                   | Bitte geben Sie eine gültige E-Mail-Adresse ein |
            | shopware  | test2@example.com |                   | Die E-Mail-Adressen stimmen nicht überein.      |
            | shopware  | abc               | abc               | Bitte geben Sie eine gültige E-Mail-Adresse ein |
            | shopware  | test@example.com  | test2@example.com | Die E-Mail-Adressen stimmen nicht überein.      |
            | shopware4 | test2@example.com | test2@example.com | Das aktuelle Passwort stimmt nicht!            |

    @shipping
    Scenario Outline: I can change my shipping address
        When I follow "Lieferadresse ändern"
        And  I change my shipping address:
            | field         | address         |
            | salutation    | <salutation>    |
            | company       | <company>       |
            | firstname     | <firstname>     |
            | lastname      | <lastname>      |
            | street        | <street>        |
            | zipcode       | <zipcode>       |
            | city          | <city>          |
            | country       | <country>       |
            | customer_type | <customer_type> |

        Then I should see "Die Adresse wurde erfolgreich gespeichert"
        And  the "shipping" address should be "<company>, <firstname> <lastname>, <street>, <zipcode> <city>, <country>"

        Examples:
            | salutation | company     | firstname | lastname   | street              | zipcode | city        | country     | customer_type |
            | ms         |             | Erika     | Musterfrau | Heidestraße 17 c    | 12345   | Köln        | Schweiz     | private       |
            | mr         | shopware AG | Max       | Mustermann | Mustermannstraße 92 | 48624   | Schöppingen | Deutschland | business      |

    @payment
    Scenario Outline: I can change my payment method
        Then  the current payment method should be "<oldPaymentName>"

        When  I change the payment method to <paymentId>
        Then  I should see "Ihre Zahlungsweise wurde erfolgreich gespeichert"
        And   the current payment method should be "<paymentName>"

        Examples:
            | oldPaymentName | paymentId | paymentName |
            | Vorkasse       | 3         | Nachnahme   |
            | Nachnahme      | 4         | Rechnung    |
            | Rechnung       | 5         | Vorkasse    |

    @payment
    Scenario: I can change my payment method with additional data
        When  I change the payment method to 2:
            | field            | value          |
            | sDebitAccount    | 123456789      |
            | sDebitBankcode   | 1234567        |
            | sDebitBankName   | Shopware Bank  |
            | sDebitBankHolder | Max Mustermann |
        Then  I should see "Ihre Zahlungsweise wurde erfolgreich gespeichert"
        And   the current payment method should be "Lastschrift"

        When  I change the payment method to 6:
            | field         | value                  |
            | sSepaIban     | DE68210501700012345678 |
            | sSepaBic      | SHOPWAREXXX            |
            | sSepaBankName | Shopware Bank          |

        Then  I should see "Ihre Zahlungsweise wurde erfolgreich gespeichert"
        And   the current payment method should be "SEPA"

    @configChange @esd
    Scenario: I can disable ESD-Articles in account
        Given I should see "Sofortdownloads"
        When  I disable the config "showEsd"
        And   I reload the page
        Then  I should see "Willkommen, Max Mustermann"
        But   I should not see "Sofortdownloads"

    @profile
    Scenario Outline: I can't change my profile when something is wrong
        Given I follow "Persönliche Daten ändern"
        And   I change my profile with "" "<salutation>" "<firstname>" "<lastname>"
        Then  I should see "Bitte füllen Sie alle rot markierten Felder aus"

        Examples:
        | salutation  | firstname     | lastname    |
        |             |               |             |
        | mr          |               |             |
        | mr          | Max           |             |
        |             |               | Mustermann  |
        |             | Max           | Mustermann  |
        |             | Max           |             |