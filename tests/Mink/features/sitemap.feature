@sitemap
Feature: View all categories on sitemap

    @navigation
    Scenario: I can see all categories
        Given I am on the page "Sitemap"
        Then  I should see all active categories

    @forms @supplier @emotions
    Scenario: I can additionally see all other important subsites
        Given I am on the page "Sitemap"
        Then  I should see all custom pages
        And   I should see all supplier pages
        And   I should see all landingpages

    @navigation @xml
    Scenario: All categories are also in the sitemap.xml
        Given I am on the sitemap.xml
        Then  I should see all active categories

    @forms @supplier @emotions @xml
    Scenario: All other important subsites are also in the sitemap.xml
        Given I am on the sitemap.xml
        Then  I should see all custom pages
        And   I should see all supplier pages
        And   I should see all landingpages