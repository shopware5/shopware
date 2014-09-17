@emotion
Feature: Emotion

    Background:
        Given I am on the homepage

    @slider @banner @blog
    Scenario: Check the frontpage emotions

        Then the element "BannerSlider" should have the content:
            | image                   |
            | beach1503f8532d4648.jpg |
            | beach2503f8535275aa.jpg |
            | beach3503f853820fa7.jpg |

        And  the element "Banner" on position 1 should have the content:
            | position | content                       |
            | image    | beach_teaser5038874e87338.jpg |

        And  the element "Banner" on position 1 should have the content:
            | mapping                                        |
            | /sommerwelten/beachwear/213/surfbrett          |
            | /sommerwelten/accessoires/170/sonnenbrille-red |
            | /sommerwelten/beachwear/178/strandtuch-ibiza   |

        And  the element "Banner" on position 2 should have the content:
            | position | content                      |
            | image    | deli_teaser503886c2336e3.jpg |
            | link     | /Campaign/index/emotionId/6  |

        And  the element "Banner" on position 3 should have the content:
            | position | content                      |
            | image    | flip_teaser503886e4dd480.jpg |

        And  the element "Banner" on position 4 should have the content:
            | position | content           |
            | image    | bienen_teaser.jpg |

        And  the element "ManufacturerSlider" should have the content:
            | image            | link                           | name                          |
            | deligarage.png   | /the-deli-garage               | The Deli Garage               |
            | stopthewater.png | /stop-the-water-while-using-me | stop the water while using me |
            | blaueshaus.png   | /das-blaue-haus                | Das blaue Haus                |
            | tea.png          | /teapavilion                   | Teapavilion                   |

        And  the element "BlogArticle" should have the content:
            | title                             | image                                                    | link                                                            | text                                                                                                  |
            | Der Sommer wird bunt              | Blog-bunte-Kleidung_720x600.jpg                          | /trends-news/der-sommer-wird-bunt                               | Diesen Sommer heißt es „Mut zur Farbe“. Denn knallbunte Kleidungsstücke sind der absolute Renner bei  |
            | Sonnenschutz - so gehören Sie zur | Blog-Sonnencreme-Sonne-Schulter5037264a3173e_720x600.jpg | /trends-news/sonnenschutz-so-gehoeren-sie-zur-creme-de-la-creme | Sonnencreme richtig auftragen – Darauf müssen Sie achten Strand , Meer und Sonne - Genießen Sie auch  |
            | Ich packe meinen Koffer           | Blog-Koffer503736edaded3_720x600.jpg                     | /trends-news/ich-packe-meinen-koffer                            | Der Urlaub ist die schönste Zeit im Jahr. Ob Sonne, Strand und Meer oder wandern im Gebirge - Es soll |

    @banner @category-teaser @slider @noResponsive
    Scenario: Check emotions on category "Genusswelten"
        When I follow "Genusswelten"

        Then the element "Banner" should have the content:
            | position | content               |
            | image    | genuss_top_banner.jpg |

        Then the element "CategoryTeaser" on position 1 should have the content:
            | position | content                          |
            | name     | Tees und Zubehör                 |
            | image    | genuss_tees_banner.jpg           |
            | link     | /genusswelten/tees-und-zubehoer/ |

        Then the element "CategoryTeaser" on position 2 should have the content:
            | position | content                    |
            | name     | Edelbrände                 |
            | image    | genuss_wein_banner.jpg     |
            | link     | /genusswelten/edelbraende/ |

        Then the element "CategoryTeaser" on position 3 should have the content:
            | position | content                        |
            | name     | Köstlichkeiten                 |
            | image    | genuss_deli_banner.jpg         |
            | link     | /genusswelten/koestlichkeiten/ |

        And the element "ArticleSlider" should have the content:
            | image                                 | link                                                           | name                              | price   |
            | Lagerkorn_XO_285x255.jpg              | /genusswelten/edelbraende/9/special-finish-lagerkorn-x.o.-32   | Special Finish Lagerkorn X.O. 32% | 24,99 € |
            | Muensterlaender_Lagerkorn_285x255.jpg | /genusswelten/2/muensterlaender-lagerkorn-32                   | Münsterländer Lagerkorn 32%       | 19,99 € |
            | Cigar_Special_285x255.jpg             | /genusswelten/edelbraende/6/cigar-special-40                   | Cigar Special 40%                 | 35,95 € |
            | Tee-weiss-Pai-Mu-Tan_285x255.jpg      | /genusswelten/tees-und-zubeh/13/pai-mu-tan-tee-weiss           | Pai Mu Tan Tee weiss              | 2,50 €  |
            | Tee-weiss-Silver-Yin-Zhen_285x255.jpg | /genusswelten/tees-und-zubeh/tees/14/silver-yin-zhen-tee-weiss | Silver Yin Zhen Tee weiss         | 3,80 €  |
            | Tee-gruen-Lung-Ching_285x255.jpg      | /genusswelten/tees-und-zubeh/tees/15/lung-ching-gruener-tee    | LUNG CHING grüner Tee             | 2,40 €  |

    @banner @youtube @article @html @noResponsive
    Scenario: Check landing page "Stop The Water While Using Me"
        When follow "Sommerwelten"
        And  I follow the link of the element "Banner" on position 3

        Then the element "Banner" should have the content:
            | position | content                               |
            | image    | teaserbanner_stopthewater_landing.png |

        And the element "YouTube" should have the content:
            | position | content     |
            | code     | RVz71XsJIEA |

        And the element "Article" on position 1 should have the content:
            | position | content                                                                                                          |
            | name     | All Natural - Sesame Sage Bodylotion                                                                             |
            | image    | All-Natural-Sesame-Sage-Body-Lotion_140x140.jpg                                                                  |
            | link     | /sommerwelten/beauty-und-care/216/all-natural-sesame-sage-bodylotion                                             |
            | text     | subringor voco ara recolo, labia boo volutarie avus expio ergo via Daci, in for nec fortis, se primoris. Frux eo |
            | price    | 21,40 €                                                                                                          |

        And the element "Article" on position 2 should have the content:
            | position | content                                                                                                     |
            | name     | All Natural - Rosemary Grapefruit Shampoo                                                                   |
            | image    | All-Natural-Rosemary-Grapefruit-Shampoo_140x140.jpg                                                         |
            | link     | /sommerwelten/beauty-und-care/215/all-natural-rosemary-grapefruit-shampoo                                   |
            | text     | Dicatio grate. Quia sal loco Pareo in Jacio capulatio si inhalo laus aut faveo Obscoena Sublime quartus pax |
            | price    | 12,80 €                                                                                                     |

        And the element "Article" on position 3 should have the content:
            | position | content                                                                                                        |
            | name     | All Natural - Lemon Honey Soap                                                                                 |
            | image    | All-Natural-Lemon-Honey-Soap_140x140.jpg                                                                       |
            | link     | /sommerwelten/beauty-und-care/218/all-natural-lemon-honey-soap                                                 |
            | text     | Ichilominus Fultus ordior, ora Sterilis qua Se sum cum Conspicio sed Eo at ver oportet, filia cedo comprehendo |
            | price    | 11,40 €                                                                                                        |

        And the element "Article" on position 4 should have the content:
            | position | content                                                                                                            |
            | name     | All Natural - Orange Wild Herbs Shower Gel                                                                         |
            | image    | All-Natural-Orange-Wild-Herbs-Shower-Gel_140x140.jpg                                                               |
            | link     | /sommerwelten/beauty-und-care/217/all-natural-orange-wild-herbs-shower-gel                                         |
            | text     | Ilis ala comitatus oro labia, tergus aro saeta ius nomen. Vox Tractare nos premo quinquagesimus recedo excello tot |
            | price    | 12,80 €                                                                                                            |

        And I should see "Aliquam erat volutpat. Nulla sollicitudin tincidunt lacus eget lobortis. Nam cursus mattis arcu, eget ornare sapien elementum ut. Vestibulum egestas urna sed quam adipiscing vel tempor erat cursus. Morbi imperdiet, nibh et hendrerit mollis, nisl leo vulputate sapien, non accumsan tortor magna in nisi. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Vivamus tempor suscipit sem, quis blandit velit faucibus quis. Sed sagittis nisi id elit commodo sed vulputate tortor consectetur. Aliquam tristique egestas justo."