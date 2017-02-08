@detail @recommendations
Feature: Recommendation tabs on detail page

  Background:
    Given I am on the page "Account"
    And I log in successful as "Max Mustermann" with email "test@example.com" and password "shopware"
    And the cart contains the following products:
      | number  | name                 | quantity |
      # ArticleID 101
      | SW10100 | Fliegenklatsche gelb | 1        |
      # ArticleID 98
      | SW10101 | Fliegenklatsche grün | 1        |

  @recommendations @javascript
  Scenario: I can not see recommendations
    When I am on the detail page for article 98
    Then I should not see "Kunden kauften auch"

    When I am on the detail page for article 101
    Then I should not see "Kunden kauften auch"

    # Strandtuch "Ibiza"
    When I am on the detail page for article 178
    Then I should not see "Kunden haben sich ebenfalls angesehen"

  @recommendations @javascript
  Scenario: I can see recommendations after I buy two articles
    And I am on the page "CheckoutConfirm"
    And I proceed to checkout
    Then I should see "Vielen Dank für Ihre Bestellung bei Shopware Demo!"

    When I am on the detail page for article 98
    Then I should see "Kunden kauften auch"
    And I should see "Kunden haben sich ebenfalls angesehen"
    And I should see "Fliegenklatsche gelb"

    When I am on the detail page for article 101
    Then I should see "Kunden kauften auch"
    And I should see "Kunden haben sich ebenfalls angesehen"
    And I should see "Fliegenklatsche grün"