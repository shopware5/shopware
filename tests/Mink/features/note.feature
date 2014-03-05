@note
Feature: Note

	Scenario: I can set an article to my note, change the currency, and remove the article from the note
		Given I am on the detail page for article "167"
		Then  I should see "Sonnenbrille Speed Eyes"

		When  I press "USD"
		Then  I should see "18,38"

		When  I follow "Auf den Merkzettel"
		Then  I should see "Sonnenbrille Speed Eyes"

		When  I press "EUR"
		Then  I should see "13,49"

		When  I follow "Löschen"
		Then  I should see "Merkzettel"
		 But  I should not see "Sonnenbrille Speed Eyes"

