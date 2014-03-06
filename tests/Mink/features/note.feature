@note
Feature: Note

	Background:
		Given I am on the detail page for article "167"
		Then  I should see "Sonnenbrille Speed Eyes"

	Scenario: I can set an article to my note, change the currency, and remove the article from the note
		When  I press "USD"
		Then  I should see "18,38"

		When  I follow "Auf den Merkzettel"
		Then  I should see "Sonnenbrille Speed Eyes"

		When  I press "EUR"
		Then  I should see "13,49"

		When  I follow "Löschen"
		Then  I should see "Merkzettel"
		 But  I should not see "Sonnenbrille Speed Eyes"


	Scenario: I can compare articles from my note
		When  I follow "Auf den Merkzettel"
		Then  I should see "Sonnenbrille Speed Eyes"

		When  I follow "Vergleichen"
		Then  I should see "Sonnenbrille Speed Eyes"

		When  I move backward one page
		 And  I follow "Sonnenbrille Speed Eyes"
		 And  I go to next article
		Then  I should see "Sonnenbrille Big Eyes"

		When  I follow "Auf den Merkzettel"
		Then  I should see "Sonnenbrille Big Eyes"

		When  I follow "Vergleichen"
		Then  I should see "Sonnenbrille Big Eyes"

		When  I follow "Vergleich starten"
		Then  I should see "Sonnenbrille Speed Eyes"
		 And  I should see "N sui ut glorificus, voro subdo flos alter laxe novem orbus sesquimellesimus, eruo ivi sero trimodus insuadibilis sus ver Jugiter episcopalis"
		 And  I should see "13,49"

		 And  I should see "Sonnenbrille Big Eyes"
		 And  I should see "Caput. Vis Antea tot dux qualiscumque incompositus, non pessum se census rationabiliter Cras injustus qui. Sis canalis sententiosus Mico, fio eo"
		 And  I should see "9,99"

		When  I move backward one page
		 And  I follow "Vergleich löschen"
		 And  I go to "/note"

		Then  I should not see "Artikel vergleichen"

		When  I remove the article on position "2"
		 And  I remove the article on position "1"

		Then  I should not see "Sonnenbrille Speed Eyes"
		 And  I should not see "Sonnenbrille Big Eyes"