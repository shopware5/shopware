@listing
Feature: Show Listing

	Scenario Outline: I can change the currency and check the ab-prices in euro and dollar
		Given I am on the listing page:
			| parameter | value |
			| sPerPage  | 24    |
			| sSort     | 5     |
		And  I press "USD"
		Then  The price of the article on position <pos> should be <price_dollar>

		When  I press "EUR"
		Then  The price of the article on position <pos> should be <price_euro>

	Examples:
		| pos | price_euro | price_dollar |
		| 2   | "11,40 €"  | "15,53 $"    |
		| 3   | "12,80 €"  | "17,44 $"    |
		| 4   | "17,90 €"  | "24,39 $"    |
		| 5   | "12,80 €"  | "17,44 $"    |
		| 6   | "21,40 €"  | "29,16 $"    |
		| 11  | "119,00 €" | "162,14 $"   |
		| 15  | "50,00 €"  | "68,13 $"    |


	Scenario: I can change the view method
		Given I am on the listing page:
			| parameter | value |
		Then  the response should contain "table-view active"
		But   the response should not contain "list-view active"

		When  I follow "Listen-Ansicht"
		Then  the response should contain "list-view active"
		But   the response should not contain "table-view active"

	Scenario: I can filter the articles by supplier
		Given I am on the listing page:
			| parameter | value |
			| sPerPage  | 48    |
		When  I set the filter to:
			| filter     | value                |
			| Hersteller | Sonnenschirm Versand |
		Then I should see 5 articles

		When  I set the filter to:
			| filter     | value       |
			| Hersteller | Teapavilion |
		Then I should see 23 articles

		When I reset all filters
		Then I should see 48 articles

	Scenario: I can filter the articles by custom filters
		Given I am on the listing page:
			| parameter | value |
			| sCategory | 21    |
		When  I set the filter to:
			| filter        | value     |
			| Geschmack     | mild      |
			| Flaschengröße | 0,5 Liter |
			| Alkoholgehalt | >30%      |
		Then I should see 4 articles

		When  I set the filter to:
			| filter          | value   |
			| Trinktemperatur | Gekühlt |
			| Farbe           | rot     |
		Then I should see 2 articles

		When I reset all filters
		Then I should see 10 articles
