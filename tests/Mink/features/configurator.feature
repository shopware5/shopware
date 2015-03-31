@configurator @detail
Feature: Configurator articles

    Scenario Outline: I can choose a standard configurator article
        Given I am on the detail page for article 202
        Then  I should see "Artikel mit Standardkonfigurator"

        When  I choose the following article configuration:
            | groupId | value   |
            | 6       | <color> |
            | 7       | <size>  |
        And   I put the article "<quantity>" times into the basket
        Then  the aggregations should look like this:
            | label | value   |
            | total | <total> |
        And   the element "CartPosition" should have the content:
            | position | content         |
            | name     | <configuration> |
            | number   | <articlenumber> |

    Examples:
        | color | size | quantity | total   | configuration | articlenumber |
        | rot   | 40   | 1        | 22,89 € | rot / 40      | SW10201.12    |
        | pink  | 37   | 2        | 43,88 € | pink / 37     | SW10201.16    |
        | blau  | 39   | 3        | 64,87 € | blau / 39     | SW10201.4     |

    Scenario Outline: I can choose a surcharge configurator article
        Given I am on the detail page for article 205
        Then  I should see "Artikel mit Aufpreiskonfigurator"

        When  I choose the following article configuration:
            | groupId | value      |
            | 12      | <spares>   |
            | 13      | <warranty> |
        And   I put the article "<quantity>" times into the basket
        Then  the aggregations should look like this:
            | label | value   |
            | total | <total> |
        And   the element "CartPosition" should have the content:
            | position | content         |
            | name     | <configuration> |
            | number   | <articlenumber> |

    Examples:
        | spares                                | warranty  | quantity | total    | configuration        | articlenumber |
        | ohne                                  | 24 Monate | 1        | 180,40 € | ohne / 24            | SW10204.1     |
        | mit Figuren                           | 36 Monate | 1        | 269,65 € | Figuren / 36         | SW10204.6     |
        | mit Figuren und Ball-Set              | 24 Monate | 1        | 222,05 € | Figuren und Ball-Set | SW10204.3     |
        | mit Figuren, Ball-Set und Service Box | 36 Monate | 1        | 293,45 € | Figuren, Ball-Set    | SW10204.8     |

    Scenario Outline: I can choose a step-by-step configurator article
        Given I am on the detail page for article 203
        Then  I should see "Artikel mit Auswahlkonfigurator"

        When  I choose the following article configuration:
            | groupId | value   |
            | 6       | <color> |
            | 7       | <size>  |
        And   I put the article "<quantity>" times into the basket
        Then  the aggregations should look like this:
            | label | value   |
            | total | <total> |
        And   the element "CartPosition" should have the content:
            | position | content         |
            | name     | <configuration> |
            | number   | <articlenumber> |

    Examples:
        | color | size  | quantity | total    | configuration | articlenumber |
        | blau  | 39/40 | 1        | 90,90 €  | blau / 39/40  | SW10202.1     |
        | grün  | 48/49 | 2        | 179,90 € | grün / 48/49  | SW10202.13    |


    Scenario Outline: I can't choose a configurator articles out of stock
        Given I am on the detail page for article <article>
        Then  I should see <name>

        When  I select <color> from "group[6]"
        And   I press "recalc"
        When  I select <size> from "group[7]"
        And   I press "recalc"
        Then  I should see "nicht zur Verfügung!"

    Examples:
        | article | name                               | color  | size |
        | 202     | "Artikel mit Standardkonfigurator" | "blau" | "36" |


    Scenario Outline: I can't select a disabled configurator variant
        Given I am on the detail page for article <article>
        Then  I should see <name>

        When  I select <color> from "group[6]"
        And   I press "recalc"
        Then  I can not select <size> from "group[7]"

    Examples:
        | article | name                              | color  | size    |
        | 203     | "Artikel mit Auswahlkonfigurator" | "blau" | "41/42" |
