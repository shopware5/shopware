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

      @javascript
  Scenario: I can change the sort
    Given I am on the listing page:
      | parameter | value |
        Then I should see "Kundengruppen Brutto / Nettopreise"

        When  I select "Beliebtheit" from "sSort"
        Then  I should see "ESD Download Artikel"
         But  I should not see "Kundengruppen Brutto / Nettopreise"

        When  I select "Niedrigster Preis" from "sSort"
        Then  I should see "Fliegenklatsche lila"
        But   I should not see "ESD Download Artikel"

        When  I select "Höchster Preis" from "sSort"
        Then  I should see "Dart Automat Standgerät"
        But   I should not see "Fliegenklatsche lila"

        When  I select "Artikelbezeichnung" from "sSort"
        Then  I should see "Artikel mit Abverkauf"
        But   I should not see "Dart Automat Standgerät"

        When  I select "Erscheinungsdatum" from "sSort"
        Then  I should see "Kundengruppen Brutto / Nettopreise"
        But   I should not see "Artikel mit Abverkauf"

  @javascript
  Scenario Outline: I can change the articles per page
    Given I am on the listing page:
      | parameter | value  |
      | sPerPage  | <from> |
    Then I should see <from> articles

    When I select "<to>" from "sPerPage"
    Then I should see <to> articles

  Examples:
    | from | to |
    | 12   | 24 |
    | 24   | 36 |
    | 36   | 48 |
    | 48   | 12 |