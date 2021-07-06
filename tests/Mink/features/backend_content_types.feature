@backend
Feature: Content Types module should work
    Background:
        Given I am logged in to the backend as an admin user
        And I go to the page "ContentTypeManager"

    Scenario: I can create a new content type with a product selection field
        When I click on the button to add a new content type
        Then There should be a window with the alias "content-type-manager-detail-window"

        When I fill in the following:
            | Name      | Recipes     |
            | Icon-Name | sprite-cake |
        And I switch to the "Felder" tab
        Then I should see "Neues Feld erstellen"

        When I click on the button to add a new field
        Then There should be a window with the alias "content-type-manager-field-window"

        When I fill in the following:
            | Label | Produkt        |
            | Typ   | Produktauswahl |
        Then I should see a dropdown appear

        When I click on the button to save the field
        And I click on the button to save the content type
        Then I should see a success message

        When I go to the page "CustomRecipes"
        And I click on the button to add a new recipe
        Then There should be a window with the alias "CustomRecipes-detail-window"
        And I should not see "Error"
