@form
Feature: forms

    @captchaInactive
    Scenario: I can raise a request regarding an article
        Given I am on the detail page for article 95
        When  I follow "Fragen zum Artikel?"
        Then  I should be on the page "Form"
        And   I should see "Anfrage-Formular"
        And   the "inquiry" field should contain "Ich habe folgende Fragen zum Artikel Blütenarrangement mit Rattan"

        When  I fill in "email" with "info@example.com"
        And   I fill in "vorname" with "Max"
        And   I fill in "nachname" with "Mustermann"
        And   I press "Senden"
        Then  I should see "Bitte füllen Sie alle rot markierten Felder aus."

        When  I select "Herr" from "anrede"
        And   I press "Senden"
        Then  I should see "Ihre Anfrage wurde versendet!"

    @captchaInactive
    Scenario: I can raise a request regarding a quotation
        Given I am on the detail page for article 207
        When  I press "In den Warenkorb"
        And   I follow "Angebot anfordern"
        Then  I should be on the page "Form"
        And   I should see "Anfrage-Formular"
        And   the "inquiry" field should contain:
        """
        Bitte unterbreiten Sie mir ein Angebot über die nachfolgenden Positionen
        3 x Staffelung, Mindest- / Maximalabnahme (SW10206) - 200,00 EUR
        """

        When  I select "Frau" from "anrede"
        And   I fill in "email" with "info@example.com"
        And   I fill in "vorname" with "Erika Musterfrau"
        And   I press "Senden"
        Then  I should see "Bitte füllen Sie alle rot markierten Felder aus."

        And   I fill in "vorname" with "Erika"
        And   I fill in "nachname" with "Musterfrau"
        And   I press "Senden"
        Then  I should see "Ihre Anfrage wurde versendet!"

    @javascript @knownFailing
    Scenario Outline: Each form has its captcha
        Given I am on form <formId>
        Then  I should see "<formTitle>"
        And   I should see a captcha

        Examples:
            | formId | formTitle          |
            | 5      | Kontaktformular    |
            | 8      | Partnerformular    |
            | 9      | Defektes Produkt   |
            | 10     | Rückgabe           |
            | 16     | Anfrage-Formular   |
            | 22     | Support beantragen |
