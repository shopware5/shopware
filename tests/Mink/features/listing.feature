@listing
Feature: Show Listing

	Scenario Outline: I can check ab-prices
		Given I am on "http://hd.test.shopware.in/mink/listing/?sPerPage=24&sTemplate=list&sPage=1&sSort=5"
		Then  The price of the article on position <pos> should be <price>

	Examples:
		|  pos |    price   |
		|  "2" |  "11,40 €" |
		|  "3" |  "12,80 €" |
		|  "4" |  "17,90 €" |
		|  "5" |  "12,80 €" |
		|  "6" |  "21,40 €" |
		| "11" | "119,00 €" |
		| "15" |  "50,00 €" |
