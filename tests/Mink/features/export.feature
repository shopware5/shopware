@feature
Feature: Export products into custom feeds

  @productFeeds
  Scenario Outline: I can see items in the exports
    Given I export the feed in "<name>" format
    Then I should see the feed "<name>" with format "<format>" in the "shop" export:
      | id        | title                        | url                                                     | image                                                                  | price |
      | SW10002.3 | Münsterländer Lagerkorn 32%  | /genusswelten/2/muensterlaender-lagerkorn-32            | /media/image/79/08/c1/Muensterlaender_Lagerkorn_1280x1280.jpg          | 19,99 |
      | SW10003   | Münsterländer Aperitif 16%   | /genusswelten/edelbraende/3/muensterlaender-aperitif-16 | /media/image/cd/0b/a1/Muensterlaender_Aperitif_Flasche_1280x1280.jpg   | 14,95 |
      | SW10004   | Latte Macchiato 17%          | /genusswelten/edelbraende/4/latte-macchiato-17          | /media/image/d3/2f/30/LatteMacchiato502bc1efd65b6_1280x1280.jpg        | 7,99  |
      | SW10005.1 | Emmelkamp Holunder Likör 18% | /genusswelten/5/emmelkamp-holunder-likoer-18            | /media/image/e9/fc/45/Emmelkamper_Holunderlikoer_200ml-1_1280x1280.jpg | 10,95 |
      | SW10006   | Cigar Special 40%            | /genusswelten/edelbraende/6/cigar-special-40            | /media/image/cb/c5/15/Cigar_Special_1280x1280.jpg                      | 35,95 |
    Examples:
      | name     | format |
      | xml      | xml    |
      | csv      | csv    |
      | txt tab  | csv    |
      | txt pipe | csv    |

  @withSubshop @productFeeds
  Scenario Outline: I can see items in the exports for subshops
    Given I export the feed in "<name>" format
    Then I should see the feed "<name>" with format "<format>" in the "subshop" export:
      | id        | title                        | url                      | image                                                                  | price |
      | SW10002.3 | Münsterländer Lagerkorn 32%  | /detail/index/sArticle/2 | /media/image/79/08/c1/Muensterlaender_Lagerkorn_1280x1280.jpg          | 19,99 |
      | SW10003   | Münsterländer Aperitif 16%   | /detail/index/sArticle/3 | /media/image/cd/0b/a1/Muensterlaender_Aperitif_Flasche_1280x1280.jpg   | 14,95 |
      | SW10004   | Latte Macchiato 17%          | /detail/index/sArticle/4 | /media/image/d3/2f/30/LatteMacchiato502bc1efd65b6_1280x1280.jpg        | 7,99  |
      | SW10005.1 | Emmelkamp Holunder Likör 18% | /detail/index/sArticle/5 | /media/image/e9/fc/45/Emmelkamper_Holunderlikoer_200ml-1_1280x1280.jpg | 10,95 |
      | SW10006   | Cigar Special 40%            | /detail/index/sArticle/6 | /media/image/cb/c5/15/Cigar_Special_1280x1280.jpg                      | 35,95 |
    Examples:
      | name     | format |
      | xml      | xml    |
      | csv      | csv    |
      | txt tab  | csv    |
      | txt pipe | csv    |