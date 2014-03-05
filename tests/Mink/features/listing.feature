@listing
Feature: Show Listing

	Background:
		Given I am on the listing page:
			| parameter | value |
			| sPerPage  | 24    |
			| sSort     | 5     |

	Scenario Outline: I can change the currency and check the ab-prices in euro and dollar
		Given I press "USD"
		Then  The price of the article on position <pos> should be <price_dollar>

		When  I press "EUR"
		Then  The price of the article on position <pos> should be <price_euro>

	Examples:
		| pos  | price_euro | price_dollar |
		| "2"  | "11,40 €"  | "15,53 $"    |
		| "3"  | "12,80 €"  | "17,44 $"    |
		| "4"  | "17,90 €"  | "24,39 $"    |
		| "5"  | "12,80 €"  | "17,44 $"    |
		| "6"  | "21,40 €"  | "29,16 $"    |
		| "11" | "119,00 €" | "162,14 $"   |
		| "15" | "50,00 €"  | "68,13 $"    |


	Scenario: I can change the view method
		Given the response should contain "table-view active"
		But   the response should not contain "list-view active"

		When  I follow "Listen-Ansicht"
		Then  the response should contain "list-view active"
		But   the response should not contain "table-view active"