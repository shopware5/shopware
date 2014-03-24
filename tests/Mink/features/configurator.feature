@configurator
Feature: Configurator articles

	Scenario: I can choose a table configurator article
		Given I am on the detail page for article 204
		Then  I should see "Artikel mit Tabellenkonfigurator"

		When  I select "SW10203.13" from "sAdd"
		Then  I put the article "2 Stück" times into the basket
		Then  the total sum should be "31,90 €"
		And   I should see "Artikel mit Tabellenkonfigurator pink / L"
		And   I should see "SW10203.13"


	Scenario Outline: I can choose a standard configurator article
		Given I am on the detail page for article 202
		Then  I should see "Artikel mit Standardkonfigurator"

		When  I select <color> from "group[6]"
		And  I select <size> from "group[7]"
		And  I press "recalc"
		Then  I put the article <quantity> times into the basket
		Then  the total sum should be <total>
		And   I should see <configuration>
		And   I should see <articlenumber>

	Examples:
		| color  | size | quantity | total     | configuration | articlenumber |
		| "rot"  | "40" | "1"      | "22,89 €" | "rot / 40"    | "SW10201.12"  |
		| "pink" | "37" | "2"      | "43,88 €" | "pink / 37"   | "SW10201.16"  |
		| "blau" | "39" | "3"      | "64,87 €" | "blau / 39"   | "SW10201.4"   |


	Scenario Outline: I can choose a surcharge configurator article
		Given I am on the detail page for article 205
		Then  I should see "Artikel mit Aufpreiskonfigurator"

		When  I select <spares> from "group[12]"
		And   I select <warranty> from "group[13]"
		And   I press "recalc"
		Then  I put the article <quantity> times into the basket
		Then  the total sum should be <total>
		And   I should see <configuration>
		And   I should see <articlenumber>

	Examples:
		| spares                                  | warranty    | quantity | total      | configuration          | articlenumber |
		| "ohne"                                  | "24 Monate" | "1"      | "180,40 €" | "ohne / 24"            | "SW10204.1"   |
		| "mit Figuren"                           | "36 Monate" | "1"      | "269,65 €" | "Figuren / 36"         | "SW10204.6"   |
		| "mit Figuren und Ball-Set"              | "24 Monate" | "1"      | "222,05 €" | "Figuren und Ball-Set" | "SW10204.3"   |
		| "mit Figuren, Ball-Set und Service Box" | "36 Monate" | "1"      | "293,45 €" | "Figuren, Ball-Set"    | "SW10204.8"   |


	Scenario Outline: I can choose a step-by-step configurator article
		Given I am on the detail page for article 203
		Then  I should see "Artikel mit Auswahlkonfigurator"

		When  I select <color> from "group[6]"
		And   I press "recalc"
		When  I select <size> from "group[7]"
		And   I press "recalc"
		Then  I put the article <quantity> times into the basket
		Then  the total sum should be <total>
		And   I should see <configuration>
		And   I should see <articlenumber>

	Examples:
		| color  | size    | quantity | total      | configuration  | articlenumber |
		| "blau" | "39/40" | "1"      | "90,90 €"  | "blau / 39/40" | "SW10202.1"   |
		| "grün" | "48/49" | "2"      | "179,90 €" | "grün / 48/49" | "SW10202.13"  |


	Scenario Outline: I can't choose a configurator articles out of stock
		Given I am on the detail page for article <article>
		Then  I should see <name>

		When  I select <color> from "group[6]"
		And   I press "recalc"
		When  I select <size> from "group[7]"
		And   I press "recalc"
		Then  I should see "Diese Auswahl steht nicht zur Verfügung!"

	Examples:
		| article | name                               | color  | size    |
		| 202     | "Artikel mit Standardkonfigurator" | "blau" | "36"    |
		| 203     | "Artikel mit Auswahlkonfigurator"  | "blau" | "41/42" |