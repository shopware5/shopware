@emotion @javascript
Feature: Emotion

    Background:
        Given I am on the homepage

    @slider @banner @blog
    Scenario: Check the frontpage emotions
        When  the emotion world has loaded
        Then  I should see a banner slider:
            | image               |
            | beach1503f8532d4648 |
            | beach2503f8535275aa |
            | beach3503f853820fa7 |

        And   I should see a banner on position 1 with image "beach_teaser5038874e87338" and mapping:
            | mapping                                        |
            | /sommerwelten/beachwear/213/surfbrett          |
            | /sommerwelten/accessoires/170/sonnenbrille-red |
            | /sommerwelten/beachwear/178/strandtuch-ibiza   |

        And   I should see a banner on position 2 with image "deli_teaser503886c2336e3" to "/Campaign/index/emotionId/6"
        And   I should see a banner on position 3 with image "flip_teaser503886e4dd480"
        And   I should see a banner on position 4 with image "bienen_teaser"

        And   I should see a manufacturer slider:
            | image                    | link                           | name                          |
            | deligarage_200x200.png   | /the-deli-garage               | The Deli Garage               |
            | stopthewater_200x200.png | /stop-the-water-while-using-me | stop the water while using me |
            | blaueshaus_200x200.png   | /das-blaue-haus                | Das blaue Haus                |
            | tea_200x200.png          | /teapavilion                   | Teapavilion                   |
            | sasse_200x200.png        | /feinbrennerei-sasse           | Feinbrennerei Sasse           |
            | vintage_200x200.png      | /vintage-driver                | Vintage Driver                |
            | accessoires_200x200.png  | /access-oires-sisters          | Access Oires Sisters          |
            | beachdreams_200x200.png  | /beachdreams-clothes           | Beachdreams Clothes           |
            | sonnenschirm_200x200.png | /sonnenschirm-versand          | Sonnenschirm Versand          |
            | sunsmile_200x200.png     | /sun-smile-and-protect         | Sun Smile and Protect         |

        And   I should see some blog articles:
            | title                             | link                                                            | text                                                                                                  |
            | Der Sommer wird bunt              | /trends-news/der-sommer-wird-bunt                               | Diesen Sommer heißt es „Mut zur Farbe“. Denn knallbunte Kleidungsstücke sind der absolute Renner bei  |
            | Sonnenschutz - so gehören Sie zur | /trends-news/sonnenschutz-so-gehoeren-sie-zur-creme-de-la-creme | Sonnencreme richtig auftragen – Darauf müssen Sie achten Strand , Meer und Sonne - Genießen Sie auch  |
            | Ich packe meinen Koffer           | /trends-news/ich-packe-meinen-koffer                            | Der Urlaub ist die schönste Zeit im Jahr. Ob Sonne, Strand und Meer oder wandern im Gebirge - Es soll |

    @banner @category-teaser @slider
    Scenario: Check emotions on category "Genusswelten"
        When  I follow "Genusswelten"
        And   the emotion world has loaded

        Then  I should see a banner with image "genuss_top_banner"

        And   the category teaser on position 1 for "Tees und Zubehör" should have the image "genuss_tees_banner" and link to "/genusswelten/tees-und-zubehoer/"
        And   the category teaser on position 2 for "Edelbrände" should have the image "genuss_wein_banner" and link to "/genusswelten/edelbraende/"
        And   the category teaser on position 3 for "Köstlichkeiten" should have the image "genuss_deli_banner" and link to "/genusswelten/koestlichkeiten/"

        And   I should see an article slider:
            | link                                                           | name                              | price   |
            | /genusswelten/edelbraende/9/special-finish-lagerkorn-x.o.-32   | Special Finish Lagerkorn X.O. 32% | 24,99 € |
            | /genusswelten/2/muensterlaender-lagerkorn-32                   | Münsterländer Lagerkorn 32%       | 19,99 € |
            | /genusswelten/edelbraende/6/cigar-special-40                   | Cigar Special 40%                 | 35,95 € |
            | /genusswelten/tees-und-zubeh/13/pai-mu-tan-tee-weiss           | Pai Mu Tan Tee weiss              | 2,50 €  |
            | /genusswelten/tees-und-zubeh/tees/14/silver-yin-zhen-tee-weiss | Silver Yin Zhen Tee weiss         | 3,80 €  |
            | /genusswelten/tees-und-zubeh/tees/15/lung-ching-gruener-tee    | LUNG CHING grüner Tee             | 2,40 €  |

    @banner @youtube @article @html
    Scenario: Check landing page "Stop The Water While Using Me"
        When  I follow "Sommerwelten"
        And   the emotion world has loaded
        And   I follow the link of the element "Banner" on position 3
        And   the emotion world has loaded

        Then  I should see a banner with image "teaserbanner_stopthewater_landing.png"

        And   I should see a YouTube-Video "RVz71XsJIEA"

        And   the product box on position 1 should have the following properties:
            | property | value                                                                |
            | name     | All Natural - Sesame Sage Bodylotion                                 |
            | price    | 21,40 €                                                              |
            | link     | /sommerwelten/beauty-und-care/216/all-natural-sesame-sage-bodylotion |

        And   the product box on position 2 should have the following properties:
            | property | value                                                                     |
            | name     | All Natural - Rosemary Grapefruit Shampoo                                 |
            | price    | 12,80 €                                                                   |
            | link     | /sommerwelten/beauty-und-care/215/all-natural-rosemary-grapefruit-shampoo |

        And   the product box on position 3 should have the following properties:
            | property | value                                                          |
            | name     | All Natural - Lemon Honey Soap                                 |
            | price    | 11,40 €                                                        |
            | link     | /sommerwelten/beauty-und-care/218/all-natural-lemon-honey-soap |

        And   the product box on position 4 should have the following properties:
            | property | value                                                                      |
            | name     | All Natural - Orange Wild Herbs Shower Gel                                 |
            | price    | 12,80 €                                                                    |
            | link     | /sommerwelten/beauty-und-care/217/all-natural-orange-wild-herbs-shower-gel |

        And   I should see "Aliquam erat volutpat. Nulla sollicitudin tincidunt lacus eget lobortis. Nam cursus mattis arcu, eget ornare sapien elementum ut. Vestibulum egestas urna sed quam adipiscing vel tempor erat cursus. Morbi imperdiet, nibh et hendrerit mollis, nisl leo vulputate sapien, non accumsan tortor magna in nisi. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Vivamus tempor suscipit sem, quis blandit velit faucibus quis. Sed sagittis nisi id elit commodo sed vulputate tortor consectetur. Aliquam tristique egestas justo."
