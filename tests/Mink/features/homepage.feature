Feature: General functionality

    @currency @noResponsive @knownFailing
    Scenario Outline: I can put articles into my basket in different currencies
        Given I am on the listing page:
            | parameter | value |
            | sPerPage  | 24    |
            | sSort     | 5     |

        When  I change the currency to "<currency>"
        Then  the article on position 11 should have this properties:
            | property | value          |
            | price    | <price_config> |
        And   the article on position 13 should have this properties:
            | property | value          |
            | price    | <price_normal> |
        And   the article on position 15 should have this properties:
            | property | value        |
            | price    | <price_base> |

        When  I follow the link "details" of the element "ArticleBox" on position 11
        Then  I should see "<price_config_detail>"

        When  I put the article into the basket
        And   I go to the listing page:
            | parameter | value |
            | sPerPage  | 24    |
            | sSort     | 5     |
        And   I follow the link "details" of the element "ArticleBox" on position 13
        Then  I should see "<price_normal>"

        When  I put the article into the basket
        And   I go to the listing page:
            | parameter | value |
            | sPerPage  | 24    |
            | sSort     | 5     |
        And   I follow the link "details" of the element "ArticleBox" on position 15
        Then  I should see "<price_base>"

        When  I put the article into the basket
        Then  the total sum should be "<sum>" when shipping costs are "<shipping>" and VAT is:
            | percent | value |
            | 19 %    | <vat> |

    Examples:
        | currency | price_config | price_normal | price_base | price_config_detail | sum         | shipping | vat        |
        | USD      | 162,14 $     | 13.625,00 $  | 68,13 $    | 243,21 $            | 13.938,92 $ | 5,31 $   | 2.225,55 $ |
        | EUR      | 119,00 €     | 10.000,00 €  | 50,00 €    | 178,50 €            | 10.230,40 € | 3,90 €   | 1.633,42 € |


    @currency @noEmotion @javascript
    Scenario Outline: I can put articles into my basket in different currencies
        Given I am on the listing page:
            | parameter | value |
            | sPerPage  | 24    |
            | sSort     | 5     |

        When  I change the currency to "<currency>"
        Then  the article on position 11 should have this properties:
            | property | value          |
            | price    | <price_config> |
        And   the article on position 13 should have this properties:
            | property | value          |
            | price    | <price_normal> |
        And   the article on position 15 should have this properties:
            | property | value        |
            | price    | <price_base> |

        When  I follow the link "details" of the element "ArticleBox" on position 11
        Then  I should see "<price_config_detail>"

        When  I put the article into the basket
        And   I go to the listing page:
            | parameter | value |
            | sPerPage  | 24    |
            | sSort     | 5     |
        And   I follow the link "details" of the element "ArticleBox" on position 13
        Then  I should see "<price_normal>"

        When  I put the article into the basket
        And   I go to the listing page:
            | parameter | value |
            | sPerPage  | 24    |
            | sSort     | 5     |
        And   I follow the link "details" of the element "ArticleBox" on position 15
        Then  I should see "<price_base>"

        When  I put the article into the basket
        And   I follow "Warenkorb öffnen"

        Then  the total sum should be "<sum>" when shipping costs are "<shipping>" and VAT is:
            | percent | value |
            | 19 %    | <vat> |

        When I remove the article on position 1
        When I remove the article on position 1
        When I remove the article on position 1
        Then I should see "Sie haben keine Artikel im Warenkorb"

    Examples:
        | currency | price_config | price_normal | price_base | price_config_detail | sum         | shipping | vat        |
        | USD      | 162,14 $     | 13.625,00 $  | 68,13 $    | 243,21 $            | 13.938,92 $ | 5,31 $   | 2.225,55 $ |
        | EUR      | 119,00 €     | 10.000,00 €  | 50,00 €    | 178,50 €            | 10.230,40 € | 3,90 €   | 1.633,42 € |
