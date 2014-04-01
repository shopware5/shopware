@checkout
Feature: Checkout articles

  Background: I can login as a user with correct credentials
	    Given I log in successful as "test@example.com" with password "shopware"
		 Then I should see "Willkommen, Max Mustermann"

	Scenario: I can open article detail page
	    Given I am on the detail page for article "167"
	     Then I should see "Sonnenbrille Speed Eyes"

		 When I put the article "3" times into the basket
	     Then The total sum should be "42,37 €"
	     Then I should see "Gesamtsumme"
	     Then I should see "AGB und Widerrufsbelehrung"

		 When I proceed to checkout
	     Then I should see "Vielen Dank für Ihre Bestellung bei Shopware 4 Demo!"



