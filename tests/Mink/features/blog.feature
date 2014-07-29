@blog
Feature: Blog

    Background:
        Given I am on the blog category 17
        Then  I should see "Blogfunktion"
        And   I should see 3 blog articles

    @filter
    Scenario Outline: I can filter the blog articles by its date
        Given I follow "<date>"
        Then  I should see 1 blog article
        And   I should see "<title>"

    Examples:
        | date       | title                                               |
        | 2012-08-22 | Der Sommer wird bunt                                |
        | 2012-08-18 | Sonnenschutz - so gehören Sie zur Crème de la Crème |
        | 2012-08-08 | Ich packe meinen Koffer                             |

    @crossselling
    Scenario Outline: I can see some matching articles on each blog article page
        Given I click to read the blog article on position <position>
        Then  I should see "<title>"
        Then  I should see "Passende Artikel"
        And   I should see <count> articles

    Examples:
        | position | title                                               | count |
        | 1        | Der Sommer wird bunt                                | 6     |
        | 2        | Sonnenschutz - so gehören Sie zur Crème de la Crème | 5     |
        | 3        | Ich packe meinen Koffer                             | 5     |

    @captcha @javascript
    Scenario Outline: I can see a captcha on each blog article page
        Given I follow "<title>"
        Then  I should see "<title>"
        And   I should see "Kommentar schreiben"
        And   I should see a captcha

    Examples:
        | title                                               |
        | Der Sommer wird bunt                                |
        | Sonnenschutz - so gehören Sie zur Crème de la Crème |
        | Ich packe meinen Koffer                             |