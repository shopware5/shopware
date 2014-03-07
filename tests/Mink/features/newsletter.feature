@newsletter
Feature: Newsletter

	Scenario: Subscribe to and unsubscripe from newsletter
		Given I am on the frontpage
		When I fill in "newsletter_input" with "test@example.de"
		And I press "newsletter"
		Then I should see "Vielen Dank. Wir haben Ihre Adresse eingetragen."

		When I select "-1" from "subscribeToNewsletter"
		And I press "Speichern"
		Then I should see " Ihre eMail-Adresse wurde gelöscht "