@account
Feature: Show Orders

	Scenario: I can see my orders
		Given I log in successful as "test@example.com" with password "shopware"
		Then  I should see "Willkommen, Max Mustermann"

		When  I follow "Meine Bestellungen"

		Then  I should see "Bestellungen nach Datum sortiert"
		And  I should see "20002"

		When  I follow "Anzeigen 20002"
		Then  I should see "Versandkostenfreier Artikel"
		And  I should see "Aufschlag bei Zahlungsarten"
		And  I should see "Express Versand"
		And  I should see "Versandkostenfreier Artikel"