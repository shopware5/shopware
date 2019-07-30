@javascript @knownFailing
Feature: Backend Modules should open
    Background:
        Given I am logged in to the backend as an admin user

    Scenario Outline: I can open a module
        When I open the module "<moduleName>"
        Then The module should open a window

        Examples:
            | moduleName  |
            | Article     |
            | ArticleList |
            | Customer    |
