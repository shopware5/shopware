@search
Feature: Search things

	Scenario: Search front page with one hit
		Given I am on the frontpage
		When I search for "Ibiza"
		Then I should see "Zu \"ibiza\" wurden 1 Artikel gefunden!"
		And I should see "Strandtuch \"Ibiza\""

	Scenario: Search with few hits
		Given I am on the frontpage
		When I search for "Korn"
		Then I should see "Zu \"Korn\" wurden 3 Artikel gefunden!"
		And I should not see "Blättern"
		And I should see "Sasse Korn 32%"
		And I should see "Münsterländer Lagerkorn 32%"
		And I should see "Special Finish Lagerkorn X.O. 32%"

	Scenario: Search with many hits
		Given I am on the frontpage
		When I search for "str"
		And I should see "Blättern"

	Scenario: Search with no hits
		Given I am on the frontpage
		When I search for "foo"
		Then I should see "Leider wurden zu \"foo\" keine Artikel gefunden"
