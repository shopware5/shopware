@blog
Feature: Blog

  Background:
    Given I am on the blog category 17
    Then I should see "Blogfunktion"
    And I should see 3 blog articles

  Scenario Outline: I can filter the blog articles by its date
    Given I follow "<date> (<count>)"
    Then I should see <count> blog articles
    And I should see "<title>"

  Examples:
    | date       | title                                               | count |
    | 2012-08-22 | Der Sommer wird bunt                                | 1     |
    | 2012-08-18 | Sonnenschutz - so gehören Sie zur Crème de la Crème | 1     |
    | 2012-08-08 | Ich packe meinen Koffer                             | 1     |


  Scenario Outline: I can see some matching articles on each blog article page
    Given I follow "<title>"
    Then I should see "Passende Artikel"
    And I should see <count> articles

  Examples:
    | title                                               | count |
    | Der Sommer wird bunt                                | 6     |
    | Sonnenschutz - so gehören Sie zur Crème de la Crème | 5     |
    | Ich packe meinen Koffer                             | 5     |

  @javascript
  Scenario Outline: I can see a captcha on each blog article page
    Given I follow "<title>"
    Then I should see "Kommentar schreiben"
    And I should see a captcha

  Examples:
    | title                                               |
    | Der Sommer wird bunt                                |
    | Sonnenschutz - so gehören Sie zur Crème de la Crème |
    | Ich packe meinen Koffer                             |

