@blog
Feature: Blog

    Background:
        Given I am on the blog category 17
        And   I should see 3 elements of type "BlogBox"

    @filter
    Scenario: I can filter the blog articles by its date
        Given I follow "2012-08"
        Then  I should see 3 elements of type "BlogBox"
        And   I should see "Der Sommer wird bunt 22.08.12"
        And   I should see "Sonnenschutz - so gehören Sie zur Crème de la Crème 18.08.12"
        And   I should see "Ich packe meinen Koffer… 08.08.12"

    @crossselling
    Scenario Outline: I can see some matching articles on each blog article page
        Given I click to read the blog article on position <position>
        Then  I should see "<title>"
        Then  I should see "Passende Artikel"
        And   I should see <count> elements of type "BlogArticleBox"

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
        When  I open the comment form
        Then  I should see a captcha

        Examples:
            | title                                               |
            | Der Sommer wird bunt                                |
            | Sonnenschutz - so gehören Sie zur Crème de la Crème |
            | Ich packe meinen Koffer                             |

    @captchaInactive @comments
    Scenario: I can write a comment
        Given I follow "Der Sommer wird bunt"
        Then  I should see "Kommentar schreiben"
        When  I write a comment:
            | field    | value           |
            | name     | Max Mustermann  |
            | eMail    | info@example.de |
            | points   | 9               |
            | headline | Neue Bewertung  |
            | comment  | Hallo Welt      |

        Then I should see "Vielen Dank für die Abgabe Ihrer Bewertung! Sie erhalten in wenigen Minuten eine Bestätigungs-E-Mail"

        When  I click the link in my latest email
        Then  I should see "Vielen Dank für die Abgabe Ihrer Bewertung! Ihre Bewertung wird nach Überprüfung freigeschaltet."
        But   I should not see "Hallo Welt"

        When  the shop owner activates my latest comment
        Then  I should see an average evaluation of 9 from following comments:
            | author         | stars | headline       | comment    |
            | Max Mustermann | 9     | Neue Bewertung | Hallo Welt |
