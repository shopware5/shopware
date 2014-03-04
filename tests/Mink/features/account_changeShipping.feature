@account
Feature: Shipping Adress

	Scenario: I can change my shipping adress
		Given I log in successful as "test@example.com" with password "shopware"
		Then  I should see "Willkommen, Max Mustermann"

		Then  I follow "Lieferadresse ändern"

		Then  I change my shipping adress:
			|     field       |      value     |
			|   salutation    |       ms       |
			|     company     |                |
			|    firstname    |     Erika      |
			|    lastname     |   Musterfrau   |
			|     street      |  Heidestraße   |
			|  streetnumber   |       17       |
			|     zipcode     |     12345      |
			|      city       |      Köln      |
			|	  country	  |    Schweiz     |

		Then  I should see "Erfolgreich gespeichert"
		 And  I should see "Erika Musterfrau"
		 And  I should see "Heidestraße 17"
		 And  I should see "12345 Köln"
		 And  I should see "Schweiz"

		Then  I follow "Lieferadresse ändern"

		Then  I change my shipping adress:
			|     field       |       value      |
			|   salutation    |        mr        |
			|     company     |    shopware AG   |
			|    firstname    |        Max       |
			|    lastname     |    Mustermann    |
			|     street      | Mustermannstraße |
			|  streetnumber   |        92        |
			|     zipcode     |       48624      |
			|      city       |    Schöppingen   |
			|	  country	  |    Deutschland   |

		Then  I should see "Ihre Lieferadresse wurde erfolgreich gespeichert"
		 And  I should see "shopware AG"
		 And  I should see "Max Mustermann"
		 And  I should see "Mustermannstraße 92"
		 And  I should see "48624 Schöppingen"
		 And  I should see "Deutschland"