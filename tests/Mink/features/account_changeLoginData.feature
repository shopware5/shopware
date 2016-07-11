@account
Feature: Successful changes of login data

    Background:
        Given I am on the page "Account"

    @password @login
    Scenario Outline: I can change my password
        Given I log in successful as "Max Mustermann" with email "test@example.com" and password "<password>"
        And   I follow "Persönliche Daten ändern"
        When  I change my password from "<password>" to "<new_password>"
        Then  I should see "Das Passwort wurde erfolgreich geändert."

        When  I log me out
        And   I follow "Mein Konto"
        And   I log in with email "test@example.com" and password "<password>"
        Then  I should see "Ihre Zugangsdaten konnten keinem Benutzer zugeordnet werden"

        Examples:
            | password  | new_password |
            | shopware  | shopware4    |
            | shopware4 | shopware     |

    @email @login
    Scenario Outline: I can change my email
        Given I log in successful as "Max Mustermann" with email "<email>" and password "shopware"
        And   I follow "Persönliche Daten ändern"
        When  I change my email with password "shopware" to "<new_email>"
        Then  I should see "Die E-Mail Adresse wurde erfolgreich geändert."

        When  I log me out
        And   I follow "Mein Konto"
        And   I log in with email "<email>" and password "shopware"
        Then  I should see "Ihre Zugangsdaten konnten keinem Benutzer zugeordnet werden"

        Examples:
            | email             | new_email         |
            | test@example.com  | test2@example.com |
            | test2@example.com | test@example.com  |

    @billing
    Scenario Outline: I can change my billing address
        Given I log in successful as "<user>" with email "test@example.com" and password "shopware"
        When  I follow "Rechnungsadresse ändern"
        And   I change my billing address:
            | field                    | address      |
            | additional.customer_type | <type>       |
            | salutation               | <salutation> |
            | company                  | <company>    |
            | firstname                | <firstname>  |
            | lastname                 | <lastname>   |
            | street                   | <street>     |
            | zipcode                  | <zipcode>    |
            | city                     | <city>       |
            | country                  | <country>    |

        Then  I should see "Die Adresse wurde erfolgreich gespeichert."
        And   the "billing" address should be "<company>, <firstname> <lastname>, <street>, <zipcode> <city>, <country>"

        Examples:
            | user             | type     | salutation | company     | firstname | lastname   | street              | zipcode | city        | country     |
            | Max Mustermann   | private  | ms         |             | Erika     | Musterfrau | Heidestraße 17 c    | 12345   | Köln        | Schweiz     |
            | Max Mustermann   | business | mr         | shopware AG | Max       | Mustermann | Mustermannstraße 92 | 48624   | Schöppingen | Deutschland |

    @registration
    Scenario: I can create a new account
        Given I am on the homepage
        When  I follow "Mein Konto"
        And   I register me:
            | field         | register[personal] | register[billing] | register[shipping] |
            | customer_type | business           |                   |                    |
            | salutation    | mr                 |                   | ms                 |
            | firstname     | Max                |                   | Erika              |
            | lastname      | Mustermann         |                   | Musterfrau         |
            | email         | ab.c               |                   |                    |
            | password      | abcdefgh           |                   |                    |
            | company       |                    | Muster GmbH       |                    |
            | street        |                    |                   | Heidestraße        |
            | zipcode       |                    | 55555             | 12345              |
            | city          |                    | Musterhausen      | Bern               |
            | country       |                    | Deutschland       | Schweiz            |

        Then  I should see "Bitte geben Sie eine gültige E-Mail-Adresse ein"
        And   I should see "Bitte füllen Sie alle rot markierten Felder aus"

        When  I register me:
            | field    | register[personal] | register[billing] |
            | email    | test@example.com   |                   |
            | password | abc                |                   |
            | street   |                    | Musterstr. 55     |

        Then  I should see "Diese E-Mail-Adresse ist bereits registriert"
        And   I should see "Bitte wählen Sie ein Passwort, welches aus mindestens 8 Zeichen besteht."
        But   I should see "Bitte füllen Sie alle rot markierten Felder aus"

        When  I register me:
            | field    | register[personal] |
            | email    | test@example.net   |
            | password | abcdefgh           |

        Then  I should see "Willkommen, Max Mustermann"

        When  I follow "Bestellungen"
        Then  I should see "Sie haben noch keine Bestellung durchgeführt."

        When  I follow "Sofortdownloads"
        Then  I should see "Sie haben noch keine Sofortdownloadartikel gekauft"

    @forgot @login
    Scenario: I can request a new password, if I forgot it
        When  I follow "Passwort vergessen?"

        Then  I should see "Passwort vergessen?"
        And   I should see "Wir senden Ihnen eine Bestätigungs-E-Mail. Klicken Sie auf den darin enthaltenen Link, um Ihr Passwort zu ändern."

        When  I fill in "email" with "test@example.info"
        And   I press "E-Mail senden"
        Then  I should see "Wir haben Ihnen eine Bestätigungs-E-Mail gesendet."

        When  I follow "Zurück"
        And   I fill in "email" with "test@example.com"
        And   I press "E-Mail senden"
        Then  I should see "Wir haben Ihnen eine Bestätigungs-E-Mail gesendet."

        When  I follow "Mein Konto"
        And   I log in successful as "Max Mustermann" with email "test@example.com" and password "shopware"
        And   I log me out
        And   I click the link in my latest email

        Then  I should see "Wenn Sie das Passwort für Ihr Konto vergessen haben, können Sie hier ein neues definieren. Wenn Sie das neue Passwort speichern, wird Ihr altes Passwort ungültig."

        When  I fill in "password[password]" with "shopware5"
        And   I fill in "password[passwordConfirmation]" with "shopware5"
        And   I press "Passwort ändern"
        Then  I should see "Ihr Passwort wurde erfolgreich geändert."
        And   I should be on the page "Account"

        When  I log me out
        And   I go to the page "Account"
        And   I log in with email "test@example.com" and password "shopware"
        Then  I should see "Ihre Zugangsdaten konnten keinem Benutzer zugeordnet werden"

    @profile
    Scenario Outline: I can change my profile
        Given I log in with email "test@example.com" and password "shopware"
        When  I follow "Persönliche Daten ändern"
        And   I change my profile with "<salutation>" "<firstname>" "<lastname>"

        Then  I should see "Die persönlichen Daten wurden erfolgreich gespeichert."
        Then  I follow "Übersicht"
        And   I should be welcome'd with with "Willkommen, <firstname> <lastname>"

        Examples:
          | salutation | firstname | lastname   |
          | Herr       | Max       | Mustermann |
          | Frau       | Erika     | Musterfrau |
          | Frau       | Elfriede  | Mustermann |
          | Herr       | Max       | Mustermann |