@checkout
Feature: Checkout esd article

    @esd @account @payment @search @listing @noResponsive
    Scenario: I can buy an esd article and download it from my account
        Given I am on the page "Account"
        And   I log in successful as "Max Mustermann" with email "test@example.com" and password "shopware"

        When  I follow the link "changeButton" of the element "AccountPayment"
        And   I submit the form "paymentForm" on page "Account" with:
            | field   | register |
            | payment | 4        |

        Then  the element "AccountPayment" should have the content:
            | position      | content  |
            | currentMethod | Rechnung |

        When  I submit the form "searchForm" on page "Homepage" with:
            | field   | value |
            | sSearch | ESD   |
        Then  I should see "Zu \"ESD\" wurden 1 Artikel gefunden!"

        When  I follow the link "order" of the element "ArticleBox" on position 1
        Then  the cart should contain 1 articles with a value of "37,99 €"
        And   I should see "ESD Download Artikel"
        And   I should see "Zuschlag für Zahlungsart"
        And   the total sum should be "37,99 €" when shipping costs are "0,00 €" and VAT is:
            | percent | value  |
            | 19 %    | 6,07 € |

        When  I proceed to checkout
        Then  I should see "Vielen Dank für Ihre Bestellung bei Shopware Demo!"
        And   my finished order should look like this:
            | product                  | quantity | price   | sum     | esd |
            | ESD Download Artikel     | 1        | 34,99 € | 34,99 € | x   |
            | Warenkorbrabatt          | 1        | -2,00 € | -2,00 € |     |
            | Zuschlag für Zahlungsart | 1        | 5,00 €  | 5,00 €  |     |

    @esd @account @payment @search @listing @noEmotion
    Scenario: I can buy an esd article and download it from my account
        Given I am on the page "Account"
        And   I log in successful as "Max Mustermann" with email "test@example.com" and password "shopware"

        When  I follow the link "changeButton" of the element "AccountPayment"
        And   I submit the form "paymentForm" on page "Account" with:
            | field   | register |
            | payment | 4        |

        Then  the element "AccountPayment" should have the content:
            | position      | content  |
            | currentMethod | Rechnung |

        When  I submit the form "searchForm" on page "Homepage" with:
            | field   | value |
            | sSearch | ESD   |
        Then  I should see "Zu \"ESD\" wurden 1 Artikel gefunden!"

        When  I follow "ESD Download Artikel"
        And   I put the article into the basket
        Then  the cart should contain 1 articles with a value of "37,99 €"
        And   I should see "ESD Download Artikel"
        And   I should see "Zuschlag für Zahlungsart"
        And   the total sum should be "37,99 €" when shipping costs are "0,00 €" and VAT is:
            | percent | value  |
            | 19 %    | 6,07 € |

        When  I follow the link "checkout" of the page "CheckoutCart"
        And   I proceed to checkout
        Then  I should see "Vielen Dank für Ihre Bestellung bei Shopware Demo!"
        And   my finished order should look like this:
            | product                  | quantity | price   | sum     | esd |
            | ESD Download Artikel     | 1        | 34,99 € | 34,99 € | x   |
            | Warenkorbrabatt          | 1        | -2,00 € | -2,00 € |     |
            | Zuschlag für Zahlungsart | 1        | 5,00 €  | 5,00 €  |     |
