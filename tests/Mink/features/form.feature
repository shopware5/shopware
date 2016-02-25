@form
Feature: forms

    @captchaInactive
    Scenario: I can raise a request regarding an article
        Given I am on the detail page for article 95
        When  I follow "Fragen zum Artikel?"
        Then  I should be on the page "Form"
        And   I should see "Anfrage-Formular"
        And   the "inquiry" field should contain "Ich habe folgende Fragen zum Artikel Blütenarrangement mit Rattan"

        When  I submit the inquiry form with:
            | field    | value            |
            | email    | info@example.com |
            | vorname  | Max              |
            | nachname | Mustermann       |
        Then  I should see "Bitte füllen Sie alle rot markierten Felder aus."

        When  I submit the inquiry form with:
            | field  | value |
            | anrede | Herr  |
        Then  I should see "Ihre Anfrage wurde versendet!"

    @captchaInactive
    Scenario: I can raise a request regarding a quotation
        Given the cart contains the following products:
            | number  | name                                  | quantity |
            | SW10206 | Staffelung, Mindest- / Maximalabnahme | 3        |
        And   I follow "Angebot anfordern"
        Then  I should be on the page "Form"
        And   I should see "Anfrage-Formular"
        And   the "inquiry" field should contain:
        """
        Bitte unterbreiten Sie mir ein Angebot über die nachfolgenden Positionen
        3 x Staffelung, Mindest- / Maximalabnahme (SW10206) - 200,00 EUR
        """

        When  I submit the inquiry form with:
            | field   | value            |
            | anrede  | Frau             |
            | email   | info@example.com |
            | vorname | Erika Musterfrau |
        Then  I should see "Bitte füllen Sie alle rot markierten Felder aus."

        When  I submit the inquiry form with:
            | field    | value      |
            | vorname  | Erika      |
            | nachname | Musterfrau |
        Then  I should see "Ihre Anfrage wurde versendet!"

    @javascript
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
