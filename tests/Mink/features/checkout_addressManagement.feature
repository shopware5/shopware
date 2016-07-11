@checkout
Feature: Checkout address management

  Background:
    Given I am on the page "Account"
    And   I log in successful as "Max Mustermann" with email "test@example.com" and password "shopware"
    And   the cart contains the following products:
      | number  | name                    | quantity |
      | SW10167 | Sonnenbrille Speed Eyes | 3        |

  @javascript
  Scenario Outline: I can create a new address during checkout
    When  I proceed to order confirmation
    And I click the link "orChooseOtherAddress" in the address box with title "Rechnungsadresse"
    Then I should see appear "Adressbuch"
    And I should see "Falls Sie eine neue Adresse hinzufügen möchten, können Sie hier eine neue erstellen."
    When I click on the link "createNewAddress"
    Then I should see appear "Neue Adresse erstellen"
    When I create the address:
      | field                    | address      |
      | additional.customer_type | <type>       |
      | salutation               | <salutation> |
      | firstname                | <firstname>  |
      | lastname                 | <lastname>   |
      | street                   | <street>     |
      | zipcode                  | <zipcode>    |
      | city                     | <city>       |
      | country                  | <country>    |
    Then I should see appear "Adressbuch"
    And there should be a modal addressbox "<company>, <firstname> <lastname>, <street>, <zipcode> <city>, <country>"
    Examples:
      | type    | company | salutation | firstname | lastname           | street                                                 | zipcode | city                         | country     |
      | private |         | mr         | Karl      | Schneider          | Vogelsang 22                                           | 12345   | Berlin                       | Deutschland |
      | private |         | ms         | Karla     | Müller-Hoppenstedt | Bischöflich-Geistlicher-Rat-Josef-Zinnbauer-Straße 22A | 25924   | Friedrich-Wilhelm-Lübke-Koog | Deutschland |

  @javascript
  Scenario Outline: I can choose a different billing address during checkout
    When  I proceed to order confirmation
    And I click the link "orChooseOtherAddress" in the address box with title "Rechnungsadresse"
    Then I should see appear "Adressbuch"
    And there should be a modal addressbox "<company>, <firstname> <lastname>, <street>, <zipcode> <city>, <country>"
    When I click "useThisAddress" on modal addressbox "<company>, <firstname> <lastname>, <street>, <zipcode> <city>, <country>"
    Then I should see appear "<company>, <firstname> <lastname>, <street>, <zipcode> <city>, <country>" in addressbox "Rechnungsadresse" after "Adressbuch" disappeared
    Examples:
      | company | firstname | lastname  | street       | zipcode | city   | country     |
      |         | Karl      | Schneider | Vogelsang 22 | 12345   | Berlin | Deutschland |

  @javascript
  Scenario Outline: I can choose a different shipping address during checkout
    When  I proceed to order confirmation
    And I click the link "orChooseOtherAddress" in the address box with title "Lieferadresse"
    Then I should see appear "Adressbuch"
    And there should be a modal addressbox "<company>, <firstname> <lastname>, <street>, <zipcode> <city>, <country>"
    When I click "useThisAddress" on modal addressbox "<company>, <firstname> <lastname>, <street>, <zipcode> <city>, <country>"
    Then I should see appear "<company>, <firstname> <lastname>, <street>, <zipcode> <city>, <country>" in addressbox "Lieferadresse" after "Adressbuch" disappeared
    Examples:
      | company | firstname | lastname           | street                                                 | zipcode | city                         | country     |
      |         | Karla     | Müller-Hoppenstedt | Bischöflich-Geistlicher-Rat-Josef-Zinnbauer-Straße 22A | 25924   | Friedrich-Wilhelm-Lübke-Koog | Deutschland |

  @javascript
  Scenario Outline: I can choose a the same shipping and billing address during checkout
    When  I proceed to order confirmation
    And I click the link "orChooseOtherAddress" in the address box with title "Lieferadresse"
    Then I should see appear "Adressbuch"
    And there should be a modal addressbox "<company>, <firstname> <lastname>, <street>, <zipcode> <city>, <country>"
    When I click "useThisAddress" on modal addressbox "<company>, <firstname> <lastname>, <street>, <zipcode> <city>, <country>"
    Then I should see appear "<company>, <firstname> <lastname>, <street>, <zipcode> <city>, <country>" in addressbox "Rechnungs- und Lieferadresse" after "Adressbuch" disappeared
    Examples:
      | company     | firstname | lastname   | street              | zipcode | city        | country     |
      | shopware AG | Max       | Mustermann | Mustermannstraße 92 | 48624   | Schöppingen | Deutschland |

  @javascript
  Scenario Outline: I can change the shipping address
    When  I proceed to order confirmation
    And I click the link "changeAddress" in the address box with title "Lieferadresse"
    Then I should see appear "Adresse bearbeiten"
    When I change the address:
      | field                    | address          |
      | additional.customer_type | <new_type>       |
      | salutation               | <new_salutation> |
      | firstname                | <new_firstname>  |
      | lastname                 | <new_lastname>   |
      | zipcode                  | <new_zipcode>    |
      | city                     | <new_city>       |
    Then I should see appear "<new_firstname> <new_lastname>, <street>, <new_zipcode> <new_city>, <country>" in addressbox "Lieferadresse" after "Adresse bearbeiten" disappeared
    Examples:
      | street              | country     | new_type | new_salutation | new_firstname | new_lastname | new_zipcode | new_city     |
      | Mustermannstraße 92 | Deutschland | private  | ms             | Susanne       | Musterfrau   | 54875       | Sindelfingen |
      | Mustermannstraße 92 | Deutschland | private  | mr             | Max           | Mustermann   | 48624       | Schöppingen  |

  @javascript
  Scenario Outline: I can change the billing address
    When  I proceed to order confirmation
    And I click the link "changeAddress" in the address box with title "Rechnungsadresse"
    Then I should see appear "Adresse bearbeiten"
    When I change the address:
      | field                    | address          |
      | additional.customer_type | <type>           |
      | company                  | <company>        |
      | salutation               | <new_salutation> |
      | firstname                | <new_firstname>  |
      | lastname                 | <new_lastname>   |
      | zipcode                  | <new_zipcode>    |
      | city                     | <new_city>       |
    Then I should see appear "<company>, <new_firstname> <new_lastname>, <street>, <new_zipcode> <new_city>, <country>" in addressbox "Rechnungsadresse" after "Adresse bearbeiten" disappeared
    Examples:
      | company     | street              | type     | country     | new_salutation | new_firstname | new_lastname | new_zipcode | new_city     |
      | Muster GmbH | Mustermannstraße 92 | business | Deutschland | ms             | Johanna       | Parsel       | 01010       | Trepko       |
      | Muster GmbH | Mustermannstraße 92 | business | Deutschland | mr             | Max           | Mustermann   | 55555       | Musterhausen |

  @javascript
  Scenario Outline: I can set a billing address as new default
    When  I proceed to order confirmation
    And I click the link "orChooseOtherAddress" in the address box with title "Rechnungsadresse"
    Then I should see appear "Adressbuch"
    When I click "useThisAddress" on modal addressbox "<firstname> <lastname>, <street>, <zipcode> <city>, <country>"
    And I set "<salutation> <firstname> <lastname>, <street>, <zipcode> <city>, <country>" as default after "Adressbuch" disappeared
    And I proceed to checkout
    Then  I should see "Vielen Dank für Ihre Bestellung bei Shopware Demo!"
    When the cart contains the following products:
      | number  | name                    | quantity |
      | SW10167 | Sonnenbrille Speed Eyes | 3        |
    And I proceed to order confirmation
    Then the "Rechnungsadresse" addressbox must contain "<salutation> <firstname> <lastname>, <street>, <zipcode> <city>, <country>"
    Examples:
      | salutation | firstname | lastname   | street              | zipcode | city         | country     |
      | Herr       | Karl      | Schneider  | Vogelsang 22        | 12345   | Berlin       | Deutschland |
      | Herr       | Max       | Mustermann | Mustermannstraße 92 | 55555   | Musterhausen | Deutschland |