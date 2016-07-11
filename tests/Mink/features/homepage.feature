Feature: General functionality

    @configChange @maintenance
    Scenario: I can close the shop due to maintenance
        Given I enable the config "setoffline"
        And   I go to the homepage
        Then  the response status code should be 503
        And   I should not see "Übersicht"
        And   I should not see "Service/Hilfe"
        But   I should see "Wegen Wartungsarbeiten nicht erreichbar!"
        And   I should see "Aufgrund nötiger Wartungsarbeiten ist der Shop zur Zeit nicht erreichbar."

        When  I follow "Beispiele"
        Then  I should not see "Weitere Artikel in dieser Kategorie"
        But   I should see "Wegen Wartungsarbeiten nicht erreichbar!"

        When  I follow "Kontakt"
        Then  I should not see "Kontaktformular"
        But   I should see "Wegen Wartungsarbeiten nicht erreichbar!"
