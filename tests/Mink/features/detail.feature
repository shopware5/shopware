@detail
Feature: detail page

    @captchaInactive
    Scenario: I can see evaluations
        Given I am on the detail page for article 198

        Then  I should see "Kundenbewertungen für \"Artikel mit Bewertung\""
        And   I should see an average customer evaluation of 10 from following evaluations:
            | author        | stars | headline      | comment                                                                                                                                     | answer                                                                                                                                                      |
            | Bert Bewerter | 10    | Super Artikel | Dieser Artikel zeichnet sich durch extreme Stabilität aus und fasst super viele Klamotten. Das Preisleistungsverhältnis ist exorbitant gut. | Vielen Dank für die positive Bewertung. Wir legen bei der Auswahl unserer Artikel besonders Wert auf die Qualität, sowie das Preis - / Leistungsverhältnis. |
            | Pep Eroni     | 10    | Hervorragend  | bin sehr zufrieden...                                                                                                                       | Danke                                                                                                                                                       |

        When  I write an evaluation:
            | field        | value           |
            | sVoteName    | Max Mustermann  |
            | sVoteMail    | info@example.de |
            | sVoteStars   | 1 sehr schlecht |
            | sVoteSummary | Neue Bewertung  |
            | sVoteComment | Hallo Welt      |
            | sCaptcha     | 123456          |
        And  I click the link in my latest email
        And  the shop owner activates my latest evaluation

        Then  I should see an average customer evaluation of 7 from following evaluations:
            | stars |
            | 1     |
            | 10    |
            | 10    |


    @plugin @notification
    Scenario: I can let me notify, when an article is available
        Given I am on the detail page for article 243
        Then  I should see "Benachrichtigen Sie mich, wenn der Artikel lieferbar ist"

        When  I submit the form "notificationForm" on page "Detail" with:
            | field              | value           |
            | sNotificationEmail | test@example.de |
        Then  I should see "Bestätigen Sie den Link der eMail die Sie gerade erhalten haben. Sie erhalten dann eine eMail sobald der Artikel wieder verfügbar ist"

        When  I click the link in my latest email
        Then  I should see "Vielen Dank! Wir haben Ihre Anfrage gespeichert! Sie werden benachrichtigt sobald der Artikel wieder verfügbar ist."
    @javascript @noResponsive
    Scenario: I can change the language
        Given I am on the detail page for article 159
        Then  I should see "Strohhut Women mit UV Schutz"

        When  I select "English" from "__shop"
        Then  I should see "Hat Women with UV protection"

        When  I go to previous article
        Then  I should see "Sunglass Big Eyes"

        When  I select "Deutsch" from "__shop"
        Then  I should see "Sonnenbrille Big Eyes"

    @captchaInactive
    Scenario: I can write an evaluation
        Given I am on the detail page for article 100
        Then  I should see "Bewertungen (0)"
        And   I should see "Bewertung schreiben"
        When  I write an evaluation:
            | field        | value           |
            | sVoteName    | Max Mustermann  |
            | sVoteMail    | info@example.de |
            | sVoteStars   | 3               |
            | sVoteSummary | Neue Bewertung  |
            | sVoteComment | Hallo Welt      |
            | sCaptcha     | 123456          |
        Then  I should not see "Bitte füllen Sie alle rot markierten Felder aus"
        But   I should see "Vielen Dank für die Abgabe Ihrer Bewertung! Sie erhalten in wenigen Minuten eine Bestätigungsmail. Bestätigen Sie den Link in dieser E-Mail um die Bewertung freizugeben."
        But   I should not see "Hallo Welt"

        When  I click the link in my latest email
        Then  I should see "Vielen Dank für die Abgabe Ihrer Bewertung! Ihre Bewertung wird nach Überprüfung freigeschaltet."
        But   I should not see "Hallo Welt"

        When  the shop owner activates my latest evaluation
        Then  I should see an average customer evaluation of 3 from following evaluations:
            | author         | stars | headline       | comment    |
            | Max Mustermann | 3     | Neue Bewertung | Hallo Welt |