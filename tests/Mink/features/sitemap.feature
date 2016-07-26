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
        Then  I should not see "Statische Seiten"

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

    @categories @xml
    Scenario: All categories are also in the sitemap.xml
        Given I am on the sitemap.xml
        Then  there should be these links in the XML:
            | link                                                                                   |
            | /                                                                                      |
            | /genusswelten/                                                                         |
            | /genusswelten/tees-und-zubehoer/                                                       |
            | /genusswelten/tees-und-zubehoer/tees/                                                  |
            | /genusswelten/tees-und-zubehoer/tee-zubehoer/                                          |
            | /genusswelten/edelbraende/                                                             |
            | /genusswelten/koestlichkeiten/                                                         |
            | /freizeitwelten/                                                                       |
            | /freizeitwelten/vintage/                                                               |
            | /freizeitwelten/entertainment/                                                         |
            | /wohnwelten/                                                                           |
            | /wohnwelten/dekoration/                                                                |
            | /wohnwelten/moebel/                                                                    |
            | /wohnwelten/kuechenzubehoer/                                                           |
            | /sommerwelten/                                                                         |
            | /sommerwelten/beachwear/                                                               |
            | /sommerwelten/beauty-care/                                                         |
            | /sommerwelten/on-world-tour/                                                           |
            | /sommerwelten/accessoires/                                                             |
            | /beispiele/                                                                            |
            | /beispiele/in-kuerze-verfuegbar/                                                       |
            | /beispiele/konfiguratorartikel/                                                        |
            | /beispiele/kundenbindung/                                                              |
            | /beispiele/produktvergleiche-filter/                                               |
            | /beispiele/darstellung/                                                                |
            | /beispiele/crossselling/                                                               |
            | /beispiele/kundengruppen-b2b/                                                          |
            | /beispiele/zahlungsarten/                                                              |
            | /beispiele/versandkosten/                                                              |
            | /beispiele/preisgestaltung/                                                            |
            | /trends-news/                                                                          |
            | /genusswelten/2/muensterlaender-lagerkorn-32                                           |
            | /genusswelten/edelbraende/3/muensterlaender-aperitif-16                                |
            | /genusswelten/edelbraende/4/latte-macchiato-17                                         |
            | /genusswelten/5/emmelkamp-holunder-likoer-18                                           |
            | /genusswelten/edelbraende/6/cigar-special-40                                           |
            | /genusswelten/7/wacholder-premium-extra-mild-32                                        |
            | /genusswelten/edelbraende/8/t.s.-privat-41-5                                           |
            | /genusswelten/edelbraende/9/special-finish-lagerkorn-x.o.-32                           |
            | /genusswelten/edelbraende/10/aperitif-glas-demi-sec                                    |
            | /genusswelten/edelbraende/11/muensterlaender-aperitif-praesent-box                     |
            | /genusswelten/12/kobra-vodka-37-5                                                      |
            | /genusswelten/tees-und-zubeh/13/pai-mu-tan-tee-weiss                                   |
            | /genusswelten/tees-und-zubeh/tees/14/silver-yin-zhen-tee-weiss                         |
            | /genusswelten/tees-und-zubeh/tees/15/lung-ching-gruener-tee                            |
            | /genusswelten/tees-und-zubeh/tees/17/mao-feng-gruener-tee                              |
            | /genusswelten/tees-und-zubeh/tees/18/pi-lo-chun-gruener-tee                            |
            | /genusswelten/tees-und-zubeh/tees/19/oriental-jasmine-tee                              |
            | /genusswelten/tees-und-zubeh/tees/20/jasmine-phoenix-tee                               |
            | /genusswelten/tees-und-zubeh/tees/21/jasmine-lotus-tee                                 |
            | /genusswelten/tees-und-zubeh/tee-zubehoer/22/glas-teetasse-0-25l                       |
            | /genusswelten/tees-und-zubeh/tee-zubehoer/23/glasbecher                                |
            | /genusswelten/tees-und-zubeh/tee-zubehoer/24/glas-teekaennchen                         |
            | /genusswelten/tees-und-zubeh/tee-zubehoer/25/glas-teekanne                             |
            | /genusswelten/tees-und-zubeh/tee-zubehoer/26/glas-teezubereiter                        |
            | /genusswelten/tees-und-zubeh/tee-zubehoer/27/facil-teezubereiter                       |
            | /genusswelten/tees-und-zubeh/tee-zubehoer/28/deluxe-eisteezubereiter                   |
            | /genusswelten/tees-und-zubeh/tee-zubehoer/29/porzellan-teeservice                      |
            | /genusswelten/tees-und-zubeh/tee-zubehoer/30/porzellan-tee-set-in-geschenkbox          |
            | /genusswelten/tees-und-zubeh/tees/31/gruener-tee-geschenkset                           |
            | /genusswelten/tees-und-zubeh/tees/32/jasmine-tee-geschenk-set                          |
            | /genusswelten/tees-und-zubeh/tees/33/oolong-tee-geschenkset                            |
            | /genusswelten/tees-und-zubeh/tees/34/pu-erh-tee-geschenkset                            |
            | /genusswelten/tees-und-zubeh/tees/35/schwarzer-tee-geschenkset                         |
            | /genusswelten/tees-und-zubehoer/tees/36/weisser-tee-geschenkset                        |
            | /genusswelten/koestlichkeiten/37/esslack                                               |
            | /genusswelten/koestlichkeiten/38/kraftstoff                                            |
            | /genusswelten/koestlichkeiten/39/mehrzwecknudeln                                       |
            | /genusswelten/koestlichkeiten/40/schokoleim                                            |
            | /genusswelten/koestlichkeiten/41/traubenbatterie                                       |
            | /genusswelten/koestlichkeiten/42/tubenhonig                                            |
            | /genusswelten/koestlichkeiten/43/oelwechsel                                            |
            | /genusswelten/koestlichkeiten/44/bienenkleber                                          |
            | /wohnwelten/moebel/63/beistelltisch                                                    |
            | /wohnwelten/moebel/64/briefkasten                                                      |
            | /wohnwelten/moebel/65/fensterspiegel                                                   |
            | /wohnwelten/moebel/66/garderobe                                                        |
            | /wohnwelten/moebel/67/antike-kommode                                                   |
            | /wohnwelten/moebel/68/kommode-shabby-chic                                              |
            | /wohnwelten/moebel/69/wandregal                                                        |
            | /wohnwelten/moebel/70/regal-gross                                                      |
            | /wohnwelten/moebel/71/schluesselkasten                                                 |
            | /wohnwelten/moebel/72/schmuckkaestchen                                                 |
            | /wohnwelten/moebel/73/spiegelregal                                                     |
            | /wohnwelten/moebel/74/stuhl                                                            |
            | /wohnwelten/moebel/75/esstisch                                                         |
            | /wohnwelten/moebel/76/couchtisch-rund-braun                                            |
            | /wohnwelten/moebel/77/couchtisch-eckig-weiss                                           |
            | /wohnwelten/kuechenzubehoer/78/uhr-antik                                               |
            | /wohnwelten/kuechenaccessoires/80/backform-gelb                                        |
            | /wohnwelten/kuechenzubehoer/81/backform-pink                                           |
            | /wohnwelten/kuechenaccessoires/82/pralinen-backform                                    |
            | /wohnwelten/kuechenaccessoires/83/back-set-3-teilig                                    |
            | /wohnwelten/kuechenaccessoires/84/loeffel-set                                          |
            | /wohnwelten/kuechenaccessoires/85/schneebesen                                          |
            | /wohnwelten/kuechenaccessoires/86/schuessel-style-voegel                               |
            | /wohnwelten/kuechenaccessoires/87/schuessel-style-blumen                               |
            | /wohnwelten/kuechenaccessoires/88/schuessel-style-ringe-rot-und-weiss                  |
            | /wohnwelten/kuechenaccessoires/89/teigschaber                                          |
            | /wohnwelten/kuechenaccessoires/90/teller-style-huehner                                 |
            | /wohnwelten/kuechenaccessoires/91/teller-style-voegel-und-blumen                       |
            | /wohnwelten/kuechenaccessoires/92/teller-style-rot-und-pink                            |
            | /wohnwelten/kuechenaccessoires/93/blumenarrangement-mit-keramiktopf                    |
            | /wohnwelten/kuechenaccessoires/94/bluetenarrangement-mit-rattan                        |
            | /wohnwelten/kuechenaccessoires/95/bluetenarrangement-mit-rattan                        |
            | /sommerwelten/96/strandbag                                                             |
            | /wohnwelten/kuechenzubehoer/97/eisfoermchen                                            |
            | /wohnwelten/kuechenaccessoires/98/fliegenklatsche-gruen                                |
            | /wohnwelten/kuechenaccessoires/100/fliegenklatsche-lila                                |
            | /wohnwelten/kuechenaccessoires/101/fliegenklatsche-gelb                                |
            | /sommerwelten/102/ipadtasche-mit-stiftmappe                                            |
            | /sommerwelten/103/reisekoffer-gross                                                    |
            | /sommerwelten/104/reisekoffer-klein                                                    |
            | /sommerwelten/105/kosmetiktasche                                                       |
            | /sommerwelten/106/kosmetiktasche                                                       |
            | /sommerwelten/107/kosmetiktasche                                                       |
            | /sommerwelten/108/strandbag                                                            |
            | /sommerwelten/109/strandbag-fantasy                                                    |
            | /sommerwelten/110/tasche-feed-me                                                       |
            | /sommerwelten/112/tasche-remember-me                                                   |
            | /freizeitwelten/unterhaltung-entertainment/113/kicker-champ-elite                      |
            | /freizeitwelten/unterhaltung-entertainment/114/kicker-champ-elite-pro-in-vielen-farben |
            | /freizeitwelten/unterhaltung-entertainment/115/kicker-champ-family-platzsparend        |
            | /freizeitwelten/unterhaltung-entertainment/116/kicker-champ-individuell-gestaltbar     |
            | /freizeitwelten/unterhaltung-entertainment/117/kicker-figuren-set-grosse-auswahl       |
            | /freizeitwelten/unterhaltung-entertainment/118/kicker-torzaehler                       |
            | /freizeitwelten/unterhaltung-entertainment/119/kickerball-standard                     |
            | /freizeitwelten/unterhaltung-entertainment/120/kickerball-kork                         |
            | /freizeitwelten/unterhaltung-entertainment/121/kickerball-probierset                   |
            | /genusswelten/edelbraende/122/sasse-korn-32                                            |
            | /freizeitwelten/unterhaltung-entertainment/123/kicker-xxl-champ-elite-pro              |
            | /sommerwelten/128/sonnenschirm-alu-style-270cm                                         |
            | /sommerwelten/129/sonnenschirm-fortino-270cm                                           |
            | /sommerwelten/130/sonnenschirm-pendalex-v-280cm                                        |
            | /sommerwelten/131/sonnenschirm-sombrano-350cm                                          |
            | /sommerwelten/132/sonnenschirm-sunwing-c-260cm                                         |
            | /freizeitwelten/vintage/134/balmoral-flatcap-tweed                                     |
            | /freizeitwelten/vintage/135/damenhandschuh-halbfinger-aus-peccary-leder                |
            | /freizeitwelten/vintage/136/damenhandschuh-aus-peccary-leder-zweifarbig                |
            | /freizeitwelten/vintage/137/fahrerbrille-chronos                                       |
            | /freizeitwelten/vintage/141/fahrerhandschuh-aus-peccary-leder                          |
            | /freizeitwelten/vintage/142/herrenhandschuh-halbfinger-aus-peccary-leder               |
            | /freizeitwelten/vintage/143/navigator-lederhaube-braun                                 |
            | /freizeitwelten/vintage/144/navigator-lederhaube-schwarz                               |
            | /freizeitwelten/vintage/145/muetze-vintage-driver                                      |
            | /freizeitwelten/vintage/148/reisetasche-gladstone-wildleder                            |
            | /sommerwelten/153/flip-flops-in-mehreren-farben-verfuegbar                             |
            | /sommerwelten/beachwear/155/flip-flop-sunshine-yellow                                  |
            | /sommerwelten/156/flip-flops-deep-red                                                  |
            | /sommerwelten/157/panama-hut-mit-uv-schutz                                             |
            | /sommerwelten/158/strohhut-mit-uv-schutz                                               |
            | /sommerwelten/159/strohhut-women-mit-uv-schutz                                         |
            | /sommerwelten/160/sommer-sandale-ocean-blue                                            |
            | /sommerwelten/161/sommer-sandale-leder-rot-braun                                       |
            | /sommerwelten/162/sommer-sandale-pink                                                  |
            | /sommerwelten/163/sommerschal-flower-power                                             |
            | /sommerwelten/164/sommerschal-fresh-green                                              |
            | /sommerwelten/165/sommerschal-light-red-aus-seide                                      |
            | /sommerwelten/166/sonnenbrille-big-eyes                                                |
            | /sommerwelten/167/sonnenbrille-speed-eyes                                              |
            | /sommerwelten/168/pilotenbrille-sunset-red                                             |
            | /sommerwelten/169/pilotenbrille-silver-sky                                             |
            | /sommerwelten/accessoires/170/sonnenbrille-red                                         |
            | /sommerwelten/171/sonnencreme-ab-lsf-10                                                |
            | /sommerwelten/172/sonnencreme-sunblocker-lsf-50                                        |
            | /sommerwelten/173/strandkleid-flower-power                                             |
            | /sommerwelten/174/strandkleid-short-red                                                |
            | /sommerwelten/175/strandtuch-sunny                                                     |
            | /sommerwelten/176/strandtuch-gras-green                                                |
            | /sommerwelten/177/strandtuch-stripes-fuer-kinder                                       |
            | /sommerwelten/beachwear/178/strandtuch-ibiza                                           |
            | /sommerwelten/179/strandtuch-in-mehreren-farben                                        |
            | /sommerwelten/180/reisekoffer-in-mehreren-farben                                       |
            | /sommerwelten/181/reisekoffer-set                                                      |
            | /beispiele/crossselling/194/artikel-mit-zubehoer                                       |
            | /freizeitwelten/unterhaltung-entertainment/195/kicker-service-box                      |
            | /beispiele/crossselling/196/artikel-mit-aehnlichen-produkten                           |
            | /beispiele/darstellung/197/esd-download-artikel                                        |
            | /beispiele/darstellung/198/artikel-mit-bewertung                                       |
            | /beispiele/darstellung/199/artikel-mit-abverkauf                                       |
            | /beispiele/darstellung/200/artikelkennzeichnung-neu                                    |
            | /beispiele/darstellung/201/hervorgehobene-darstellung                                  |
            | /beispiele/konfiguratorartikel/202/artikel-mit-standardkonfigurator                    |
            | /beispiele/konfiguratorartikel/203/artikel-mit-auswahlkonfigurator                     |
            | /beispiele/konfiguratorartikel/204/artikel-mit-tabellenkonfigurator                    |
            | /beispiele/konfiguratorartikel/205/artikel-mit-aufpreiskonfigurator                    |
            | /beispiele/preisgestaltung/206/artikel-mit-grundpreisberechnung                        |
            | /beispiele/preisgestaltung/207/staffelung-mindest-/-maximalabnahme                     |
            | /beispiele/preisgestaltung/208/pseudopreis                                             |
            | /beispiele/preisgestaltung/209/staffelpreise                                           |
            | /beispiele/kundenbindung/210/praemienartikel-ab-50-euro-warenkorb-wert                 |
            | /beispiele/kundenbindung/211/praemienartikel-ab-250-euro-warenkorb-wert                |
            | /sommerwelten/beachwear/213/surfbrett                                                  |
            | /sommerwelten/beauty-und-care/214/all-natural-rosemary-grapefruit-conditioner          |
            | /sommerwelten/beauty-und-care/215/all-natural-rosemary-grapefruit-shampoo              |
            | /sommerwelten/beauty-und-care/216/all-natural-sesame-sage-bodylotion                   |
            | /sommerwelten/beauty-und-care/217/all-natural-orange-wild-herbs-shower-gel             |
            | /sommerwelten/beauty-und-care/218/all-natural-lemon-honey-soap                         |
            | /beispiele/versandkosten/219/express-versand                                           |
            | /beispiele/versandkosten/220/versandkostenfreier-artikel                               |
            | /beispiele/versandkosten/221/versandkosten-optionen                                    |
            | /wohnwelten/dekoration/222/dekokissen-blume                                            |
            | /beispiele/versandkosten/223/ausweichversandkosten                                     |
            | /wohnwelten/dekoration/224/dekokissen-bus                                              |
            | /wohnwelten/dekoration/225/dekokissen-vogel                                            |
            | /wohnwelten/dekoration/226/magnete-abc                                                 |
            | /beispiele/zahlungsarten/227/aufschlag-bei-zahlungsarten                               |
            | /beispiele/zahlungsarten/228/zahlungsarten-und-riskmanagement                          |
            | /wohnwelten/dekoration/229/magnete-london                                              |
            | /beispiele/zahlungsarten/230/abschlag-bei-zahlungsarten                                |
            | /wohnwelten/dekoration/231/notizbuch-new-york                                          |
            | /wohnwelten/dekoration/233/notizbuch-london                                            |
            | /wohnwelten/dekoration/234/notizbuch-paris                                             |
            | /wohnwelten/dekoration/235/deko-topf                                                   |
            | /wohnwelten/dekoration/236/servietten                                                  |
            | /wohnwelten/dekoration/237/stoffherz-kariert                                           |
            | /sommerwelten/beachwear/238/strandbag-sailor                                           |
            | /freizeitwelten/entertainment/239/dart-automat-standgeraet                             |
            | /freizeitwelten/entertainment/240/dartscheibe-circle                                   |
            | /freizeitwelten/entertainment/241/dartpfeil-steel-atomic                               |
            | /freizeitwelten/entertainment/242/dartpfeil-steel-smiley-745                           |
            | /beispiele/darstellung/243/artikel-mit-emailbenachrichtigung                           |
            | /beispiele/kundengruppen-b2b/244/kundengruppen-brutto-/-nettopreise                    |
            | /freizeitwelten/entertainment/245/bumerang                                             |
            | /freizeitwelten/entertainment/246/dart-set                                             |
            | /beispiele/kundenbindung/247/praemienartikel-ab-100-euro-warenkorbwert                 |
            | /wohnwelten/moebel/248/versandkosten-nach-gewicht                                      |
            | /genusswelten/koestlichkeiten/272/spachtelmasse                                        |
            | /trends-news/der-sommer-wird-bunt                                                      |
            | /trends-news/sonnenschutz-so-gehoeren-sie-zur-creme-de-la-creme                        |
            | /trends-news/ich-packe-meinen-koffer                                                   |
            | /feinbrennerei-sasse/                                                                  |
            | /teapavilion/                                                                          |
            | /the-deli-garage/                                                                      |
            | /access-oires-sisters/                                                                 |
            | /vintage-driver/                                                                       |
            | /das-blaue-haus/                                                                       |
            | /heiku/                                                                                |
            | /sonnenschirm-versand/                                                                 |
            | /beachdreams-clothes/                                                                  |
            | /sun-smile-and-protect/                                                                |
            | /example/                                                                              |
            | /stop-the-water-while-using-me/                                                        |
            | /stop-the-water-while-using-me                                            |
            | /passend-fuer-ihren-sommerurlaub                                          |
