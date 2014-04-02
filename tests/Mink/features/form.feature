@form
Feature: forms

  @javascript
  Scenario Outline: Each form has its captcha
    Given I am on form <formId>
    Then I should see "<formTitle>"
    And I should see a captcha

  Examples:
    | formId | formTitle          |
    | 5      | Kontaktformular    |
    | 8      | Partnerformular    |
    | 9      | Defektes Produkt   |
    | 10     | Rückgabe           |
    | 16     | Anfrage-Formular   |
    | 22     | Support beantragen |

  @javascript
  Scenario: Also the customer evaluation form on a detail page has a captcha
    Given I am on the detail page for article 167
    Then  I should see "Sonnenbrille Speed Eyes"
    And I should see a captcha
