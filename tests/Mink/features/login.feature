@login
Feature: Login

	Scenario: I can not login as a user with bad credentials
		Given I log in as "test@example.com" with password "wrong"
		Then  I should see "Ihre Zugangsdaten konnten keinem Benutzer zugeordnet werden"

	Scenario: I can login as a user with correct credentials
		Given I log in successful as "test@example.com" with password "shopware"
		Then I should see "Willkommen, Max Mustermann"

