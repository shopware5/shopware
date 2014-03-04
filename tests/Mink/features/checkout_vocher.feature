@checkout
Feature: Checkout articles with voucher

	Scenario: I can use vouchers in my basket and pay as new customer via prepayment
		Given I am on the detail page for article "137"
		Then I should see "Fahrerbrille Chronos"

		When I put the article "1" times into the basket
		Then The total sum should be "61,89 €"

		When I add the article "SW10142" to my basket
		Then The total sum should be "106,88 €"

		When I add the voucher "absolut" to my basket
		Then The total sum should be "101,88 €"

		When I remove the voucher
		Then The total sum should be "106,88 €"

		When I remove the article on position "2"
		Then The total sum should be "61,89 €"

		When I add the voucher "prozentual" to my basket
		Then The total sum should be "55,89 €"

		When I follow "Zur Kasse gehen"
		 And I check "skipLogin"
		 And I press "Neuer Kunde"
		 And I register me
			 |      field      |     billing      |
			 |  customer_type  |     business     |
			 |   salutation    |        mr        |
			 |    firstname    |       Max        |
			 |    lastname     |    Mustermann    |
			 |      email      | test@example.com |
			 |      phone      |  05555 / 555555  |
			 |     company     |   Muster GmbH    |
			 |     street      |    Musterstr.    |
			 |  streetnumber   |        55        |
			 |     zipcode     |      55555       |
			 |      city       |   Musterhausen   |
			 |	  country	   |   Deutschland    |

		Then I should not see "Ein Fehler ist aufgetreten!"
		 And The total sum should be "55,89 €"
		 And I should see "Gesamtsumme"
		 And I should see "AGB und Widerrufsbelehrung"

		When I proceed to checkout
		Then I should see "Vielen Dank für Ihre Bestellung bei Shopware 4 Demo!"

