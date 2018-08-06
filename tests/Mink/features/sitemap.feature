@sitemap
Feature: View all categories on sitemap

    @categories
    Scenario: I can see all categories
        Given I am on the page "Sitemap"
        Then  I should see the group "Genusswelten" with link "/genusswelten/":
            | value            | link                                          | level |
            | Tees und Zubehör | /genusswelten/tees-und-zubehoer/              | 1     |
            | Tees             | /genusswelten/tees-und-zubehoer/tees/         | 2     |
            | Tee-Zubehör      | /genusswelten/tees-und-zubehoer/tee-zubehoer/ | 2     |
            | Edelbrände       | /genusswelten/edelbraende/                    | 1     |
            | Köstlichkeiten   | /genusswelten/koestlichkeiten/                | 1     |
        And   I should see the group "Freizeitwelten" with link "/freizeitwelten/":
            | value         | link                           |
            | Vintage       | /freizeitwelten/vintage/       |
            | Entertainment | /freizeitwelten/entertainment/ |
        And   I should see the group "Wohnwelten" with link "/wohnwelten/":
            | value         | link                         |
            | Dekoration    | /wohnwelten/dekoration/      |
            | Möbel         | /wohnwelten/moebel/          |
            | Küchenzubehör | /wohnwelten/kuechenzubehoer/ |
        And   I should see the group "Sommerwelten" with link "/sommerwelten/":
            | value         | link                           |
            | Beachwear     | /sommerwelten/beachwear/       |
            | Beauty & Care | /sommerwelten/beauty-care/ |
            | On World Tour | /sommerwelten/on-world-tour/   |
            | Accessoires   | /sommerwelten/accessoires/     |
        And   I should see the group "Beispiele" with link "/beispiele/":
            | value                      | link                                     |
            | In Kürze verfügbar         | /beispiele/in-kuerze-verfuegbar/         |
            | Konfiguratorartikel        | /beispiele/konfiguratorartikel/          |
            | Kundenbindung              | /beispiele/kundenbindung/                |
            | Produktvergleiche & Filter | /beispiele/produktvergleiche-filter/ |
            | Darstellung                | /beispiele/darstellung/                  |
            | Crossselling               | /beispiele/crossselling/                 |
            | Kundengruppen / B2B        | /beispiele/kundengruppen-b2b/            |
            | Zahlungsarten              | /beispiele/zahlungsarten/                |
            | Versandkosten              | /beispiele/versandkosten/                |
            | Preisgestaltung            | /beispiele/preisgestaltung/              |
        And   I should see the group "Trends + News" with link "/trends-news/"

    @staticPages
    Scenario: I can see all active custom pages
        Given I am on the page "Sitemap"
        Then  I should see "Statische Seiten"

    @suppliers
    Scenario: I can see all suppliers
        Given I am on the page "Sitemap"
        Then  I should see the group "Herstellerseiten":
            | value                         | link                            |
            | Feinbrennerei Sasse           | /feinbrennerei-sasse/           |
            | Teapavilion                   | /teapavilion/                   |
            | The Deli Garage               | /the-deli-garage/               |
            | Access Oires Sisters          | /access-oires-sisters/          |
            | Vintage Driver                | /vintage-driver/                |
            | Das blaue Haus                | /das-blaue-haus/                |
            | Heiku                         | /heiku/                         |
            | Sonnenschirm Versand          | /sonnenschirm-versand/          |
            | Beachdreams Clothes           | /beachdreams-clothes/           |
            | Sun Smile and Protect         | /sun-smile-and-protect/         |
            | Example                       | /example/                       |
            | stop the water while using me | /stop-the-water-while-using-me/ |

    @landingpages
    Scenario: I can see all active landingpages
        Given I am on the page "Sitemap"
        Then  I should see the group "Landingpages":
            | value                          | link                                          |
            | Stop The Water While Using Me  | /stop-the-water-while-using-me   |
            | Passend für Ihren Sommerurlaub | /passend-fuer-ihren-sommerurlaub |

    @xml
    Scenario: I can see sitemap redirects me to sitemap_index.xml
        Given I am on the sitemap.xml
        Then I should be on "/sitemap_index.xml"

    @xml
    Scenario: I see the sitemap files listed on sitemap_index.xml
        Given I am on the sitemap_index.xml
        Then I should see the sitemap files:
            | name                          |
            | sitemap-1.xml.gz              |