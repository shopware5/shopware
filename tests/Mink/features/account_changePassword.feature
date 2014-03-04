@account
Feature: Change Password

	Scenario Outline: I can change my password
		Given I log in successful as "test@example.com" with password <password>
		Then  I should see "Willkommen, Max Mustermann"

		Then  I change my password from <password> to <new_password> with confirmation <new_password>

		Then  I should see "Zugangsdaten wurden erfolgreich gespeichert"

	Examples:
	|  password   | new_password |
	| "shopware"  |  "shopware4" |
	| "shopware4" |  "shopware"  |


	Scenario Outline: I can't change my password, when something is wrong
		Given I log in successful as "test@example.com" with password "shopware"
		Then  I should see "Willkommen, Max Mustermann"

		Then  I change my password from <password> to <new_password> with confirmation <confirmation>

		Then  I should see "Ein Fehler ist aufgetreten!"
		And   I should see <message>

	Examples:
	|  password   | new_password | confirmation |                                  message                                  |
	| "shopware"  |    "sw4"     |    "sw4"     | "Bitte wählen Sie ein Passwort welches aus mindestens 8 Zeichen besteht." |
	| "shopware"  |  "shopware4" |  "shopware5" |                  "Die Passwörter stimmen nicht überein."                  |
	| "shopware4" |  "shopware5" |  "shopware5" |                   "Das aktuelle Passwort stimmt nicht!"                   |