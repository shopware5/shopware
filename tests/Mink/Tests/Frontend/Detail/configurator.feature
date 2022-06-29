@configurator @detail
Feature: Configurator articles

    Scenario Outline: I can choose a standard configurator article
        Given I am on the detail page for article 202
        And   I choose the following article configuration:
            | groupId | value   |
            | 6       | <color> |
            | 7       | <size>  |
        And   I put the article "<quantity>" times into the basket
        Then  the aggregations should look like this:
            | label | value   |
            | total | <total> |
        And   the cart should contain the following products:
            | number          | name                                              |
            | <articlenumber> | Artikel mit Standardkonfigurator <color> / <size> |

        Examples:
            | color | size | quantity | total   | articlenumber |
            | rot   | 40   | 1        | 22,89 € | SW10201.12    |
            | pink  | 37   | 2        | 43,88 € | SW10201.16    |
            | blau  | 39   | 3        | 64,87 € | SW10201.4     |

    Scenario Outline: I can choose a surcharge configurator article
        Given I am on the detail page for article 205
        And   I choose the following article configuration:
            | groupId | value      |
            | 12      | <spares>   |
            | 13      | <warranty> |
        And   I put the article into the basket
        Then  the aggregations should look like this:
            | label | value   |
            | total | <total> |
        And   the cart should contain the following products:
            | number          | name                                                   |
            | <articlenumber> | Artikel mit Aufpreiskonfigurator <spares> / <warranty> |

        Examples:
            | spares                                | warranty  | total    | articlenumber |
            | ohne                                  | 24 Monate | 180,40 € | SW10204.1     |
            | mit Figuren                           | 36 Monate | 269,65 € | SW10204.6     |
            | mit Figuren und Ball-Set              | 24 Monate | 222,05 € | SW10204.3     |
            | mit Figuren, Ball-Set und Service Box | 36 Monate | 293,45 € | SW10204.8     |

    Scenario Outline: I can choose a step-by-step configurator article
        Given I am on the detail page for article 203
        And   I choose the following article configuration:
            | groupId | value   |
            | 6       | <color> |
            | 7       | <size>  |
        And   I put the article "<quantity>" times into the basket
        Then  the aggregations should look like this:
            | label | value   |
            | total | <total> |
        And   the cart should contain the following products:
            | number          | name                                             |
            | <articlenumber> | Artikel mit Auswahlkonfigurator <color> / <size> |

        Examples:
            | color | size  | quantity | total    | articlenumber |
            | blau  | 39/40 | 1        | 90,90 €  | SW10202.1     |
            | grün  | 48/49 | 2        | 179,90 € | SW10202.13    |


    Scenario: I can't choose a configurator articles out of stock
        Given I am on the detail page for article 202
        Then  I should see "Artikel mit Standardkonfigurator"

        When  I choose the following article configuration:
            | groupId | value |
            | 6       | blau  |
            | 7       | 36    |
        Then  I should see "nicht zur Verfügung!"

    Scenario: I can't select a disabled configurator variant
        Given I am on the detail page for article 203
        Then  I should see "Artikel mit Auswahlkonfigurator"

        When  I choose the following article configuration:
            | groupId | value |
            | 6       | grün  |
            | 7       | 41/42 |
        Then  I can not select "blau" from "group[6]"

        When  I choose the following article configuration:
            | groupId | value |
            | 7       | 43/44 |
            | 6       | blau  |
        Then  I can not select "41/42" from "group[7]"
