@blog
Feature: Blog

    Background:
        Given I am on the blog category 17
        Then  I should see "Blogfunktion"
        And   I should see 3 elements of type "BlogBox"

    @filter
    Scenario Outline: I can filter the blog articles by its date
        Given I follow "<date>"
        Then  I should see 1 element of type "BlogBox"
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
        And   I should see a captcha

    Examples:
        | title                                               |
        | Der Sommer wird bunt                                |
        | Sonnenschutz - so gehören Sie zur Crème de la Crème |
        | Ich packe meinen Koffer                             |

    @captchaInactive
    Scenario: I can write a comment
        Given I follow "Der Sommer wird bunt"
        Then  I should see "Kommentar schreiben"
        When  I write a comment:
            | field    | value           |
            | name     | Max Mustermann  |
            | eMail    | info@example.de |
            | points   | 10              |
            | headline | Neue Bewertung  |
            | comment  | Hallo Welt      |
            | sCaptcha | 123456          |
        Then  I should not see "Bitte füllen Sie alle rot markierten Felder aus"
        But   I should see "Vielen Dank für die Abgabe Ihrer Bewertung! Sie erhalten in wenigen Minuten eine Bestätigungsmail. Bestätigen Sie den Link in dieser eMail um die Bewertung freizugeben."
        But   I should not see "Hallo Welt"

        When  I click the link in my latest email
        Then  I should see "Vielen Dank für die Abgabe Ihrer Bewertung! Ihre Bewertung wird nach Überprüfung freigeschaltet."
        But   I should not see "Hallo Welt"

        When  the shop owner activate my latest comment
        Then  I should see "Hallo Welt"