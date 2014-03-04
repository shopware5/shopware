@emotion
Feature: Emotion

	Scenario: Check all standard emotion elements
		Given I am on the frontpage
		 Then I should see a banner slider:
			|          image          |
			| beach1503f8532d4648.jpg |
			| beach2503f8535275aa.jpg |
			| beach3503f853820fa7.jpg |

		  And I should see a banner "beach_teaser5038874e87338.jpg" with mapping:
		    |                    mapping                     |
			|  /sommerwelten/beachwear/178/strandtuch-ibiza  |
		    | /sommerwelten/accessoires/170/sonnenbrille-red |
		    |     /sommerwelten/beachwear/213/surfbrett      |

		  And I should see a banner "deli_teaser503886c2336e3.jpg" to "/Campaign/index/emotionId/6"
		  And I should see a banner "flip_teaser503886e4dd480.jpg"
		  And I should see a banner "bienen_teaser.jpg"

		  And I should see a manufacturer slider:
			|       image      |	         link               |             name     	        |
			|  deligarage.png  |       /the-deli-garage         |       The Deli Garage         |
			| stopthewater.png | /stop-the-water-while-using-me | stop the water while using me |
			|  blaueshaus.png  |        /das-blaue-haus         |        Das blaue Haus         |
			|      tea.png     |         /teapavilion           |          Teapavilion          |

		  And I should see some blog articles:
		    |                title              |                        image                             |                             link                                |                                                 text                                                  |
			|        Der Sommer wird bunt       |            Blog-bunte-Kleidung_720x600.jpg               |               /trends-news/der-sommer-wird-bunt                 | Diesen Sommer heißt es „Mut zur Farbe“. Denn knallbunte Kleidungsstücke sind der absolute Renner bei  |
			| Sonnenschutz - so gehören Sie zur | Blog-Sonnencreme-Sonne-Schulter5037264a3173e_720x600.jpg | /trends-news/sonnenschutz-so-gehoeren-sie-zur-creme-de-la-creme | Sonnencreme richtig auftragen – Darauf müssen Sie achten Strand , Meer und Sonne - Genießen Sie auch  |
			|      Ich packe meinen Koffer      |          Blog-Koffer503736edaded3_720x600.jpg            |             /trends-news/ich-packe-meinen-koffer                | Der Urlaub ist die schönste Zeit im Jahr. Ob Sonne, Strand und Meer oder wandern im Gebirge - Es soll |

		When I follow "Genusswelten"

		Then I should see a banner "genuss_top_banner.jpg"

		 And I should see an article slider:
			|                 image                 |	                            link                             |                name               |  price  |
			|        Lagerkorn_XO_285x255.jpg       |  /genusswelten/edelbraende/9/special-finish-lagerkorn-x.o.-32  | Special Finish Lagerkorn X.O. 32% | 24,99 |
			| Muensterlaender_Lagerkorn_285x255.jpg |          /genusswelten/2/muensterlaender-lagerkorn-32          |    Münsterländer Lagerkorn 32%    | 19,99 |
			|       Cigar_Special_285x255.jpg       |          /genusswelten/edelbraende/6/cigar-special-40          |         Cigar Special 40%         | 35,95 |
			| Tee-weiss-Pai-Mu-Tan_285x255.jpg      | /genusswelten/tees-und-zubeh/13/pai-mu-tan-tee-weiss           | Pai Mu Tan Tee weiss              | 2,50  |
			| Tee-weiss-Silver-Yin-Zhen_285x255.jpg | /genusswelten/tees-und-zubeh/tees/14/silver-yin-zhen-tee-weiss | Silver Yin Zhen Tee weiss         | 3,80   |
			|Tee-gruen-Lung-Ching_285x255.jpg       | /genusswelten/tees-und-zubeh/tees/15/lung-ching-gruener-tee    | LUNG CHING grüner Tee             | 2,40   |

		When go to "http://hd.test.shopware.in/mink/campaign/index/emotionId/5"

		Then I should see a banner "teaserbanner_stopthewater_landing.png"

		 And I should see a YouTube-Video "RVz71XsJIEA"

		 And I should see "Aliquam erat volutpat. Nulla sollicitudin tincidunt lacus eget lobortis. Nam cursus mattis arcu, eget ornare sapien elementum ut. Vestibulum egestas urna sed quam adipiscing vel tempor erat cursus. Morbi imperdiet, nibh et hendrerit mollis, nisl leo vulputate sapien, non accumsan tortor magna in nisi. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Vivamus tempor suscipit sem, quis blandit velit faucibus quis. Sed sagittis nisi id elit commodo sed vulputate tortor consectetur. Aliquam tristique egestas justo."