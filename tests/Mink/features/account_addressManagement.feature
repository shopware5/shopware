@account @accountaddressmanagement
Feature: Using the address management system

  Background:
    Given I am on the page "Account"

  Scenario: I can register a new Account
    Given I register me:
      | field         | register[personal]                 | register[billing] |
      | customer_type | business                           |                   |
      | salutation    | mr                                 |                   |
      | firstname     | Max                                |                   |
      | lastname      | Mustermann                         |                   |
      | email         | account@adressmanagement.localhost |                   |
      | password      | shopware                           |                   |
      | company       |                                    | Muster GmbH       |
      | street        |                                    | Musterstr. 55     |
      | zipcode       |                                    | 55555             |
      | city          |                                    | Musterhausen      |
      | country       |                                    | Deutschland       |
    Then  I should see "Willkommen, Max Mustermann"

  Scenario Outline: I can add a new address
    Given I log in successful as "<user>" with email "account@adressmanagement.localhost" and password "shopware"
    When  I follow "Adressen"
    And  I follow "Neue Adresse hinzufügen"
    And I create a new address:
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

    Then  I should see "Die Adresse wurde erfolgreich erstellt"
    And   there should be an address "<company>, <firstname> <lastname>, <street>, <zipcode> <city>, <country>"

    Examples:
      | user           | type     | salutation | company     | firstname | lastname | street      | zipcode | city        | country     |
      | Max Mustermann | private  | mr         |             | Julius    | Caesar   | Colloseum 1 | 00000   | Roma        | Italien     |
      | Max Mustermann | business | ms         | luluax GmbH | Frauke    | Friemel  | Segelweg 2  | 11111   | Schöppingen | Deutschland |

  Scenario Outline: I can set a new billing address
    Given I log in successful as "<user>" with email "account@adressmanagement.localhost" and password "shopware"
    When  I follow "Adressen"
    And I click "setDefaultShippingButton" on address "<company>, <firstname> <lastname>, <street>, <zipcode> <city>, <country>"
    Then I should see only "<company>, <firstname> <lastname>, <street>, <zipcode> <city>, <country>" with title "Standard-Lieferadresse"
    And I should see "Die Adresse wird nun als Standard-Lieferadresse verwendet"

    Examples:
      | user           | company | firstname | lastname | street      | zipcode | city | country |
      | Max Mustermann |         | Julius    | Caesar   | Colloseum 1 | 00000   | Roma | Italien |

  Scenario Outline: I can set a new shipping address
    Given I log in successful as "<user>" with email "account@adressmanagement.localhost" and password "shopware"
    When  I follow "Adressen"
    And I click "setDefaultBillingButton" on address "<company>, <firstname> <lastname>, <street>, <zipcode> <city>, <country>"
    Then I should see only "<company>, <firstname> <lastname>, <street>, <zipcode> <city>, <country>" with title "Standard-Rechnungsadresse"
    And I should see "Die Adresse wird nun als Standard-Rechnungsadresse verwendet"

    Examples:
      | user           | company     | firstname | lastname | street     | zipcode | city        | country     |
      | Max Mustermann | luluax GmbH | Frauke    | Friemel  | Segelweg 2 | 11111   | Schöppingen | Deutschland |

  Scenario Outline: I can change an address
    Given I log in successful as "<user>" with email "account@adressmanagement.localhost" and password "shopware"
    When  I follow "Adressen"
    And I click "changeLink" on address "<old_company>, <old_firstname> <old_lastname>, <old_street>, <old_zipcode> <old_city>, <old_country>"
    And I change the current address to:
      | field                    | address         |
      | salutation               | <salutation>    |
      | company                  | <company>       |
      | firstname                | <firstname>     |
      | lastname                 | <lastname>      |
      | street                   | <street>        |
      | zipcode                  | <zipcode>       |
      | city                     | <city>          |
      | country                  | <country>       |
      | additional.customer_type | <customer_type> |
    Then I should see "Die Adresse wurde erfolgreich gespeichert"
    And there should be an address "<company>, <firstname> <lastname>, <street>, <zipcode> <city>, <country>"
    Examples:
      | user           | salutation | old_company | company   | old_firstname | firstname            | old_lastname | lastname | old_street  | street                     | old_zipcode | zipcode | old_city    | city              | old_country | country     | customer_type |
      | Max Mustermann | mr         |             |           | Julius        | Jörg-Johannes Martin | Caesar       | Tramitz  | Colloseum 1 | Stargazer-French-Fries 234 | 00000       | 33378   | Roma        | Rheda-Wiedenbrück | Italien     | Deutschland | private       |
      | Max Mustermann | ms         | luluax GmbH | luluax AG | Frauke        | Frauke               | Friemel      | Frommel  | Segelweg 2  | Siegerweg 42               | 11111       | 22301   | Schöppingen | Hamburg           | Deutschland | Deutschland | business      |

  Scenario Outline: I can delete adresses
    Given I log in successful as "<user>" with email "account@adressmanagement.localhost" and password "shopware"
    When  I follow "Adressen"
    And I delete the address "<company>, <firstname> <lastname>, <street>, <zipcode> <city>, <country>"
    Then there must not be an address "<company>, <firstname> <lastname>, <street>, <zipcode> <city>, <country>"
    Examples:
      | user           | company     | firstname | lastname   | street              | zipcode | city         | country     |
      | Max Mustermann | Muster GmbH | Max       | Mustermann | Musterstr. 55       | 55555   | Musterhausen | Deutschland |
      | Max Mustermann | shopware AG | Max       | Mustermann | Mustermannstraße 92 | 48624   | Schöppingen  | Deutschland |

  Scenario Outline: I can add a new address and set it as billing address
    Given I log in successful as "<user>" with email "account@adressmanagement.localhost" and password "shopware"
    When  I follow "Adressen"
    And  I follow "Neue Adresse hinzufügen"
    And I create a new address:
      | field                               | address                   |
      | additional.customer_type            | <type>                    |
      | additional.setDefaultBillingAddress | <isDefaultBillingAddress> |
      | salutation                          | <salutation>              |
      | company                             | <company>                 |
      | firstname                           | <firstname>               |
      | lastname                            | <lastname>                |
      | street                              | <street>                  |
      | zipcode                             | <zipcode>                 |
      | city                                | <city>                    |
      | country                             | <country>                 |
    Then  I should see "Die Adresse wurde erfolgreich erstellt"
    And I should see only "<company>, <firstname> <lastname>, <street>, <zipcode> <city>, <country>" with title "Standard-Rechnungsadresse"
    Examples:
      | user           | type     | salutation | company     | firstname | lastname   | street        | zipcode | city         | country     | isDefaultBillingAddress |
      | Max Mustermann | business | mr         | Muster GmbH | Max       | Mustermann | Musterstr. 55 | 55555   | Musterhausen | Deutschland | 1                       |

  Scenario Outline: I can add a new address and set it as shipping address
    Given I log in successful as "<user>" with email "account@adressmanagement.localhost" and password "shopware"
    When  I follow "Adressen"
    And  I follow "Neue Adresse hinzufügen"
    And I create a new address:
      | field                                | address                    |
      | additional.customer_type             | <type>                     |
      | additional.setDefaultShippingAddress | <isDefaultShippingAddress> |
      | salutation                           | <salutation>               |
      | company                              | <company>                  |
      | firstname                            | <firstname>                |
      | lastname                             | <lastname>                 |
      | street                               | <street>                   |
      | zipcode                              | <zipcode>                  |
      | city                                 | <city>                     |
      | country                              | <country>                  |
    Then  I should see "Die Adresse wurde erfolgreich erstellt"
    And I should see only "<company>, <firstname> <lastname>, <street>, <zipcode> <city>, <country>" with title "Standard-Lieferadresse"
    Examples:
      | user           | type     | salutation | company     | firstname | lastname   | street              | zipcode | city        | country     | isDefaultShippingAddress |
      | Max Mustermann | business | mr         | shopware AG | Max       | Mustermann | Mustermannstraße 92 | 48624   | Schöppingen | Deutschland | 1                        |

  Scenario Outline: There is no delete button or unexpected action on default shipping and billing address
    Given I log in successful as "<user>" with email "account@adressmanagement.localhost" and password "shopware"
    When  I follow "Adressen"
    Then I must not see "<deleteLink>" in box with "<addressTitle>" title
    And I must not see "<switchLink>" in box with "<addressTitle>" title
    Examples:
      | user           | addressTitle              | deleteLink | switchLink                              |
      | Max Mustermann | Standard-Lieferadresse    | Löschen    | Als Standard-Lieferadresse verwenden    |
      | Max Mustermann | Standard-Rechnungsadresse | Löschen    | Als Standard-Rechnungsadresse verwenden |