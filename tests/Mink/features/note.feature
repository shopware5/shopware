@note
Feature: Note

	Background:
		Given I am on the detail page for article 167
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
		Then  My note should look like this:
			| name                    | supplier              | ordernumber | text                                                                                                                                                     | price   | image              | link                                      |
			| Sonnenbrille Speed Eyes | Sun Smile and Protect | SW10167     | N sui ut glorificus, voro subdo flos alter laxe novem orbus sesquimellesimus, eruo ivi sero trimodus insuadibilis sus ver Jugiter episcopalis. Humilitas | 13,49 € | Sonnenbrille-gruen | /sommerwelten/167/sonnenbrille-speed-eyes |

		When  I visit the detail page of the article on position 1 of my note
		And  I go to next article
		Then  I should see "Sonnenbrille Big Eyes"

		When  I follow "Auf den Merkzettel"
		Then  My note should look like this:
			| name                    | supplier              | ordernumber | text                                                                                                                                                       | price   | image                  | link                                      |
			| Sonnenbrille Big Eyes   | Sun Smile and Protect | SW10166     | Caput. Vis Antea tot dux qualiscumque incompositus, non pessum se census rationabiliter Cras injustus qui. Sis canalis sententiosus Mico, fio eo amo Posco | 9,99 €  | Sonnenbrille-Damen-rot | /sommerwelten/166/sonnenbrille-big-eyes   |
			| Sonnenbrille Speed Eyes | Sun Smile and Protect | SW10167     | N sui ut glorificus, voro subdo flos alter laxe novem orbus sesquimellesimus, eruo ivi sero trimodus insuadibilis sus ver Jugiter episcopalis. Humilitas   | 13,49 € | Sonnenbrille-gruen     | /sommerwelten/167/sonnenbrille-speed-eyes |

		When  I compare the article on position 1 of my note
		And  I compare the article on position 2 of my note
		And  I follow "Vergleich starten"
		Then  The comparision should look like this:
			| image                              | name                    | ranking | text                                                                                                                                             | price   | link                                      |
			| Sonnenbrille-Damen-rot_105x105.jpg | Sonnenbrille Big Eyes   | 0       | Caput. Vis Antea tot dux qualiscumque incompositus, non pessum se census rationabiliter Cras injustus qui. Sis canalis sententiosus Mico, fio eo | 9,99 €  | /sommerwelten/166/sonnenbrille-big-eyes   |
			| Sonnenbrille-gruen_105x105.jpg     | Sonnenbrille Speed Eyes | 0       | N sui ut glorificus, voro subdo flos alter laxe novem orbus sesquimellesimus, eruo ivi sero trimodus insuadibilis sus ver Jugiter episcopalis    | 13,49 € | /sommerwelten/167/sonnenbrille-speed-eyes |

		When  I move backward one page
		And  I follow "Vergleich löschen"
		And  I go to my note
		Then  I should not see "Artikel vergleichen"

		When  I remove the article on position 2 of my note
		Then  My note should look like this:
			| name                  | supplier              | ordernumber | text                                                                                                                                                       | price  | image                  | link                                    |
			| Sonnenbrille Big Eyes | Sun Smile and Protect | SW10166     | Caput. Vis Antea tot dux qualiscumque incompositus, non pessum se census rationabiliter Cras injustus qui. Sis canalis sententiosus Mico, fio eo amo Posco | 9,99 € | Sonnenbrille-Damen-rot | /sommerwelten/166/sonnenbrille-big-eyes |

		When  I remove the article on position 1 of my note
		Then  My note should be empty