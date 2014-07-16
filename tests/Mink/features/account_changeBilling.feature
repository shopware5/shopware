@account
Feature: Billing address

	Scenario: I can change my billing address
		Given I log in successful as "test@example.com" with password "shopware"
		Then  I should see "Willkommen, Max Mustermann"

		Then  I follow "Rechnungsadresse ändern"

		Then  I change my billing address:
			| field         | value          |
			| customer_type | private        |
			| salutation    | ms             |
			| firstname     | Erika          |
			| lastname      | Musterfrau     |
			| phone         | 06666 / 666666 |
			| street        | Heidestraße    |
			| streetnumber  | 17             |
			| zipcode       | 12345          |
			| city          | Köln           |
			| country       | Schweiz        |

		Then  I should see "Erfolgreich gespeichert"
		And  I should see "Erika Musterfrau"
		And  I should see "Heidestraße 17"
		And  I should see "12345 Köln"
		And  I should see "Schweiz"

		Then  I follow "Rechnungsadresse ändern"

		Then  I change my billing address:
			| field         | value          |
			| customer_type | business       |
			| salutation    | mr             |
			| firstname     | Max            |
			| lastname      | Mustermann     |
			| phone         | 05555 / 555555 |
			| company       | Muster GmbH    |
			| street        | Musterstr.     |
			| streetnumber  | 55             |
			| zipcode       | 55555          |
			| city          | Musterhausen   |
			| country       | Deutschland    |

		Then  I should see "Erfolgreich gespeichert"
		And  I should see "Muster GmbH"
		And  I should see "Max Mustermann"
		And  I should see "Musterstr. 55"
		And  I should see "55555 Musterhausen"
		And  I should see "Deutschland"