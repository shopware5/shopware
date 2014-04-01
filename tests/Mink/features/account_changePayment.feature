@account
Feature: Payment method

	Scenario: I can change my payment method
		Given I log in successful as "test@example.com" with password "shopware"
		Then  I should see "Willkommen, Max Mustermann"

		Then  I follow "Zahlungsart ändern"

		Then  I select "3" from "register[payment]"
		And  I press "Ändern"

		Then  I should see "Ihre Zahlungsweise wurde erfolgreich gespeichert"
		And  I should see "Nachnahme"
		And  I should see "Kauf von Direktdownloads nur per Lastschrift oder Kreditkarte möglich"

		Then  I follow "Zahlungsart ändern"

		Then  I select "4" from "register[payment]"
		And  I press "Ändern"

		Then  I should see "Ihre Zahlungsweise wurde erfolgreich gespeichert"
		And  I should see "Rechnung"

		Then  I follow "Zahlungsart ändern"

		Then  I select "2" from "register[payment]"
		And  I fill in "kontonr" with "12345"
		And  I fill in "blz" with "67890"
		And  I fill in "bank" with "shopware Bank"
		And  I fill in "bank2" with "shopware AG"
		And  I press "Ändern"

		Then  I should see "Ihre Zahlungsweise wurde erfolgreich gespeichert"
		And  I should see "Lastschrift"
		And  I should see "Kauf von Direktdownloads nur per Lastschrift oder Kreditkarte möglich"

		Then  I follow "Zahlungsart ändern"

		Then  I select "5" from "register[payment]"
		And  I press "Ändern"

		Then  I should see "Ihre Zahlungsweise wurde erfolgreich gespeichert"
		And  I should see "Vorkasse"
		And  I should see "Kauf von Direktdownloads nur per Lastschrift oder Kreditkarte möglich"