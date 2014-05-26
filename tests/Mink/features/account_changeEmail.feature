@account
Feature: Change Email

	Scenario Outline: I can change my email
		Given I log in successful as <email> with password "shopware"
		Then  I should see "Willkommen, Max Mustermann"

		Then  I change my email with password "shopware" to <new_email> with confirmation <new_email>

		Then  I should see "Zugangsdaten wurden erfolgreich gespeichert"

	Examples:
		| email               | new_email           |
		| "test@example.com"  | "test2@example.com" |
		| "test2@example.com" | "test@example.com"  |


	Scenario Outline: I can't change my password, when something is wrong
		Given I log in successful as "test@example.com" with password "shopware"
		Then  I should see "Willkommen, Max Mustermann"

		Then  I change my email with password <password> to <new_email> with confirmation <confirmation>

		Then  I should see "Ein Fehler ist aufgetreten!"
		And   I should see <message>

	Examples:
		| password    | new_email           | confirmation        | message                                          |
		| "shopware"  | "abc"               | "abc"               | "Bitte geben Sie eine gültige eMail-Adresse ein" |
		| "shopware"  | "test@example.com"  | "test2@example.com" | "Die eMail-Adressen stimmen nicht überein."      |
		| "shopware4" | "test2@example.com" | "test2@example.com" | "Das aktuelle Passwort stimmt nicht!"            |