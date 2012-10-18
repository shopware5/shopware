-- Prepares
SET FOREIGN_KEY_CHECKS = 0;

-- 2-fix-delta-table.sql
INSERT IGNORE INTO `s_media_album` (`id`, `name`, `parentID`, `position`) VALUES
(-12, 'Hersteller', NULL, 12);

-- 3-rename-navigation-snippets-detail.sql
UPDATE  `s_core_snippets` SET  `value` =  'Zur Übersicht' WHERE  `name` = 'DetailNavIndex' AND `localeID` = 1;
UPDATE  `s_core_snippets` SET  `value` =  'Back to overview' WHERE  `name` = 'DetailNavIndex' AND `localeID` = 2;

-- 4-insert-supplier-album.sql
INSERT IGNORE INTO  `s_media_album_settings` (
    `id` ,
    `albumID` ,
    `create_thumbnails` ,
    `thumbnail_size` ,
    `icon`
)
VALUES (
    NULL ,  '-12',  '0',  '',  'sprite-blue-folder'
);

-- 5-fix-last-article-thumb-size.sql
UPDATE `s_core_config_elements` SET `value` = 'i:2;',
`type` = 'number' WHERE `name` = 'thumb';

-- 6-fix-customer_state_id.sql
ALTER TABLE `s_user_billingaddress` CHANGE `stateID` `stateID` INT( 11 ) NULL DEFAULT NULL;
UPDATE `s_user_billingaddress` SET `stateID` = Null WHERE `stateID` = 0;
ALTER TABLE `s_user_shippingaddress` CHANGE `stateID` `stateID` INT( 11 ) NULL DEFAULT NULL;
UPDATE `s_user_shippingaddress` SET `stateID` = Null WHERE `stateID` = 0;

-- 7-add-default-merchant-group.sql
INSERT IGNORE INTO `s_core_customergroups` (`id`, `groupkey`, `description`, `tax`, `taxinput`, `mode`, `discount`, `minimumorder`, `minimumordersurcharge`) VALUES
(2, 'H', 'Händler', 1, 0, 0, 0, 0, 0);

-- 9-add-inquiry-basket-snippet.sql
INSERT IGNORE INTO `s_core_snippets` (`namespace`, `shopID`, `localeID`, `name`, `value`, `created`, `updated`) VALUES ('frontend/detail/comment', '1', '1', 'InquiryTextBasket', 'Bitte unterbreiten Sie mir ein Angebot über die nachfolgenden Positionen', NOW(), NOW());

-- 8-fix-partner-form-template.sql
UPDATE `s_cms_support` SET `email_template` = 'Partneranfrage - {$sShopname}
{sVars.firma} moechte Partner Ihres Shops werden!

Firma: {sVars.firma}
Ansprechpartner: {sVars.ansprechpartner}
Straße/Hausnr.: {sVars.strasse}
PLZ / Ort: {sVars.plz} {sVars.ort}
eMail: {sVars.email}
Telefon: {sVars.tel}
Fax: {sVars.fax}
Webseite: {sVars.website}

Kommentar:
{sVars.kommentar}

Profil:
{sVars.profil}' WHERE name = 'Partnerformular' AND MD5(s_cms_support.email_template) = 'b24502c9de57c8777a638190d52c18d5';

UPDATE `s_cms_support` SET `email_template` = 'Partner inquiry - {$sShopname}
{sVars.firma} want to become your partner!

Company: {sVars.firma}
Contact person: {sVars.ansprechpartner}
Street / No.: {sVars.strasse}
Postal Code / City: {sVars.plz} {sVars.ort}
eMail: {sVars.email}
Phone: {sVars.tel}
Fax: {sVars.fax}
Website: {sVars.website}

Comment:
{sVars.kommentar}

Profile:
{sVars.profil}' WHERE name = 'Partner form' AND MD5(s_cms_support.email_template) = 'a179ec3e50b3135baab41f9badbd259a';

-- 10-add-support-for-iron-browser.sql
UPDATE `s_core_config_elements` SET `value` = 's:2773:"antibot;appie;architext;bjaaland;digout4u;echo;fast-webcrawler;ferret;googlebot;gulliver;harvest;htdig;ia_archiver;jeeves;jennybot;linkwalker;lycos;mercator;moget;muscatferret;myweb;netcraft;nomad;petersnews;scooter;slurp;unlost_web_crawler;voila;voyager;webbase;weblayers;wget;wisenutbot;acme.spider;ahoythehomepagefinder;alkaline;arachnophilia;aretha;ariadne;arks;aspider;atn.txt;atomz;auresys;backrub;bigbrother;blackwidow;blindekuh;bloodhound;brightnet;bspider;cactvschemistryspider;cassandra;cgireader;checkbot;churl;cmc;collective;combine;conceptbot;coolbot;core;cosmos;cruiser;cusco;cyberspyder;deweb;dienstspider;digger;diibot;directhit;dnabot;download_express;dragonbot;dwcp;e-collector;ebiness;eit;elfinbot;emacs;emcspider;esther;evliyacelebi;nzexplorer;fdse;felix;fetchrover;fido;finnish;fireball;fouineur;francoroute;freecrawl;funnelweb;gama;gazz;gcreep;getbot;geturl;golem;grapnel;griffon;gromit;hambot;havindex;hometown;htmlgobble;hyperdecontextualizer;iajabot;ibm;iconoclast;ilse;imagelock;incywincy;informant;infoseek;infoseeksidewinder;infospider;inspectorwww;intelliagent;irobot;israelisearch;javabee;jbot;jcrawler;jobo;jobot;joebot;jubii;jumpstation;katipo;kdd;kilroy;ko_yappo_robot;labelgrabber.txt;larbin;legs;linkidator;linkscan;lockon;logo_gif;macworm;magpie;marvin;mattie;mediafox;merzscope;meshexplorer;mindcrawler;momspider;monster;motor;mwdsearch;netcarta;netmechanic;netscoop;newscan-online;nhse;northstar;occam;octopus;openfind;orb_search;packrat;pageboy;parasite;patric;pegasus;perignator;perlcrawler;phantom;piltdownman;pimptrain;pioneer;pitkow;pjspider;pka;plumtreewebaccessor;poppi;portalb;puu;python;raven;rbse;resumerobot;rhcs;roadrunner;robbie;robi;robofox;robozilla;roverbot;rules;safetynetrobot;search_au;searchprocess;senrigan;sgscout;shaggy;shaihulud;sift;simbot;site-valet;sitegrabber;sitetech;slcrawler;smartspider;snooper;solbot;spanner;speedy;spider_monkey;spiderbot;spiderline;spiderman;spiderview;spry;ssearcher;suke;suntek;sven;tach_bw;tarantula;tarspider;techbot;templeton;teoma_agent1;titin;titan;tkwww;tlspider;ucsd;udmsearch;urlck;valkyrie;victoria;visionsearch;vwbot;w3index;w3m2;wallpaper;wanderer;wapspider;webbandit;webcatcher;webcopy;webfetcher;webfoot;weblinker;webmirror;webmoose;webquest;webreader;webreaper;websnarf;webspider;webvac;webwalk;webwalker;webwatch;whatuseek;whowhere;wired-digital;wmir;wolp;wombat;worm;wwwc;wz101;xget;awbot;bobby;boris;bumblebee;cscrawler;daviesbot;ezresult;gigabot;gnodspider;internetseer;justview;linkbot;linkchecker;nederland.zoek;perman;pompos;pooodle;redalert;shoutcast;slysearch;ultraseek;webcompass;yandex;robot;yahoo;bot;psbot;crawl;RSS;larbin;ichiro;Slurp;msnbot;bot;Googlebot;ShopWiki;Bot;WebAlta;;abachobot;architext;ask jeeves;frooglebot;googlebot;lycos;spider;HTTPClient";' WHERE `s_core_config_elements`.`name` = 'botBlackList';

-- 11-add-show-listing-option.sql
DROP TABLE IF EXISTS s_emotion_backup;
CREATE TABLE IF NOT EXISTS `s_emotion_new` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `active` int(1) NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `cols` int(11) DEFAULT NULL,
  `cell_height` int(11) NOT NULL,
  `article_height` int(11) NOT NULL,
  `container_width` int(11) NOT NULL,
  `rows` int(11) NOT NULL,
  `valid_from` datetime DEFAULT NULL,
  `valid_to` datetime DEFAULT NULL,
  `userID` int(11) DEFAULT NULL,
  `show_listing` int(1) NOT NULL,
  `is_landingpage` int(1) NOT NULL,
  `landingpage_block` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `landingpage_teaser` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `seo_keywords` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `seo_description` text COLLATE utf8_unicode_ci NOT NULL,
  `create_date` datetime DEFAULT NULL,
  `template` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;
RENAME TABLE s_emotion TO s_emotion_backup;
INSERT IGNORE INTO s_emotion_new (`id`,`active`, `name`,`cols`,`cell_height`,`article_height`,`container_width`,`rows`,`valid_from`,`valid_to`,`userID`,`is_landingpage`,`landingpage_block`,`landingpage_teaser`,`seo_keywords`,`seo_description`,`create_date`,`template`,`modified`)
SELECT `id`,`active`, `name`,`cols`,`cell_height`,`article_height`,`container_width`,`rows`,`valid_from`,`valid_to`,`userID`,`is_landingpage`,`landingpage_block`,`landingpage_teaser`,`seo_keywords`,`seo_description`,`create_date`,`template`,`modified` FROM s_emotion_backup;
RENAME TABLE s_emotion_new TO s_emotion;
ALTER TABLE  `s_emotion_attributes` DROP FOREIGN KEY  `s_emotion_attributes_ibfk_1` ;
ALTER TABLE  `s_emotion_attributes` ADD FOREIGN KEY (  `emotionID` ) REFERENCES  `s_emotion` (
`id`
) ON DELETE CASCADE ON UPDATE NO ACTION ;
DROP TABLE s_emotion_backup;

-- 12-fix-vat-service-label.sql
UPDATE `s_core_config_elements`
SET `label` = 'Wenn der Service nicht erreichbar ist, nur eine einfache Überprüfung durchführen'
WHERE `name` = 'vatchecknoservice';

-- 13-fix-translation.sql
UPDATE s_core_config_element_translations
SET `label` = REPLACE(label, 'article', 'product')
WHERE locale_id = 2;
UPDATE s_core_config_element_translations
SET `label` = REPLACE(label, 'Article', 'Product')
WHERE locale_id = 2;
UPDATE s_core_config_element_translations
SET `description` = REPLACE(description, 'article', 'product')
WHERE locale_id = 2;
UPDATE s_core_config_element_translations
SET `description` = REPLACE(description, 'Article', 'Product')
WHERE locale_id = 2;
UPDATE s_core_config_form_translations
SET `label` = REPLACE(label, 'article', 'product')
WHERE locale_id = 2;
UPDATE s_core_config_form_translations
SET `label` = REPLACE(label, 'Article', 'Product')
WHERE locale_id = 2;
UPDATE s_core_config_form_translations
SET `description` = REPLACE(description, 'article', 'product')
WHERE locale_id = 2;
UPDATE s_core_config_form_translations
SET `description` = REPLACE(description, 'Article', 'Product')
WHERE locale_id = 2;

-- 14-remove-kind-3.sql
DELETE ad, at, ap
FROM s_articles_details ad
LEFT JOIN s_articles_attributes at
ON ad.id=at.articledetailsID
LEFT JOIN s_articles_prices ap
ON ad.id=ap.articledetailsID
WHERE ad.kind=3;

-- 15-fix-frontend-translation.sql
REPLACE INTO `s_core_snippets` (`namespace`, `shopID`, `localeID`, `name`, `value`, `created`, `updated`) VALUES
('frontend/index/checkout_actions', 1, 2, 'IndexInfoArticles', 'Product', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/listing/listing_actions', 1, 2, 'ListingSortName', 'Product description', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/listing/listing_actions', 1, 2, 'ListingLabelItemsPerPage', 'Products per page', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/listing/box_article', 1, 2, 'ListingBoxLinkDetails', 'Go to product', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/detail/actions', 1, 2, 'DetailLinkVoucher', 'Recommend product', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/detail/actions', 1, 2, 'DetailLinkContact', 'Do you have any questions concerning this product?', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/detail/description', 1, 2, 'DetailDescriptionHeader', 'Product information', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/detail/similar', 1, 2, 'DetailSimilarHeader', 'Similar products', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/checkout/ajax_add_article', 1, 2, 'AjaxAddHeader', 'The product has been added to the shopping cart successfully', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/checkout/ajax_add_article', 1, 2, 'AjaxAddHeaderCrossSelling', 'You may also like these products', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/checkout/ajax_amount', 1, 2, 'AjaxAmountInfoCountArticles', 'Product', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/widgets/compare/index', 1, 2, 'DetailActionLinkCompare', 'Compare products', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/blog/detail', 1, 2, 'BlogHeaderCrossSelling', 'Related products', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/account/downloads', 1, 2, 'DownloadsColumnName', 'Product', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/note/index', 1, 2, 'NoteText2', 'Simply add a desired product to the wish list and {$sShopname} will save it for you. Thus you are able to call up your selected products the next time you visit the online shop. ', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/note/index', 1, 2, 'NoteColumnName', 'Product', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/compare/index', 1, 2, 'CompareInfoCount', 'Compare product', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/detail/comment', 1, 2, 'DetailCommentInfoSuccess', 'Thank you for evaluating our product! The product will be activated after verification.', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/detail/comment', 1, 2, 'DetailCommentInfoRating', 'from {$sArticle.sVoteAverange.count} customer evaluations', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/checkout/ajax_add_article', 1, 2, 'AjaxAddErrorHeader', 'The product could not be added to the shopping cart. ', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/detail/related', 1, 2, 'DetailRelatedHeader', 'Complementary products', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/note/item', 1, 2, 'NoteLinkDetails', 'View product ', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/compare/added', 1, 2, 'CompareHeaderTitle', 'Compare products', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/checkout/cart_header', 1, 2, 'CartColumnName', 'Product', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/checkout/cart_footer_left', 1, 2, 'CheckoutFooterLabelAddArticle', 'Add product', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/checkout/cart', 1, 2, 'CartInfoEmpty', 'Your shopping cart does not contain any products', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/account/order_item', 1, 2, 'OrderItemColumnName', 'Product', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/checkout/cart_item', 1, 2, 'CartItemInfoPremium', 'As a small token of our thanks, you receive this product for free.', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/detail/bundle/box_related', 1, 2, 'BundleHeader', 'Buy this product bundled with ', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/detail/buy', 1, 2, 'DetailBuyInfoNotAvailable', 'This product is currently not available.', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/detail/description', 1, 2, 'DetailDescriptionLinkInformation', 'Further products by {$information.description}', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/plugins/notification/index', 1, 2, 'DetailNotifyHeader', 'Please inform me as soon as the product is available again.', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/plugins/notification/index', 1, 2, 'DetailNotifyInfoSuccess', 'Please confirm the link contained in the e-mail that you have just received. We will inform you as soon as the product is available again. ', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/search/supplier', 1, 2, 'SearchArticlesFound', 'Products found!', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/plugins/index/delivery_informations', 1, 2, 'DetailDataInfoShipping', 'This product will be released at', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/checkout/added', 1, 2, 'CheckoutAddArticleInfoAdded', '{$sArticleName} has been added to shopping cart!', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/detail/error', 1, 2, 'DetailRelatedHeader', 'Unfortunately, this product is no longer available', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/detail/error', 1, 2, 'DetailRelatedHeaderSimilarArticles', 'Similar products:', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/search/fuzzy', 1, 2, 'SearchHeadline', 'The following products have been found matching your search "{$sRequests.sSearch}":  {$sSearchResults.sArticlesCount} ', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/listing/listing_actions', 1, 2, 'ListingActionsOffersLink', 'Further products in this category:', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/plugins/recommendation/blocks_index', 1, 2, 'IndexSimilaryArticlesSlider', 'Products similar to those you have recently viewed:', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend', 1, 2, 'CheckoutArticleLessStock', 'Unfortunately, the requested product is not deliverable in the desired quantities. (#0 of #1 deliverable).', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend', 1, 2, 'CheckoutSelectVariant', 'Please select a variant to add desired product to the shopping cart', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend', 1, 2, 'CheckoutArticleNoStock', 'Unfortunately, the requested product is no longer available in the desired quantities.', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/checkout/confirm', 1, 2, 'ConfirmErrorStock', 'One of your desired products is not available. Please remove this item from shopping cart!', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/plugins/recommendation/blocks_listing', 1, 2, 'IndexSimilaryArticlesSlider', 'Products similar to those you have recently viewed', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/compare/add_article', 1, 2, 'CompareHeaderTitle', 'Compare products', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/compare/add_article', 1, 2, 'CompareInfoMaxReached', 'You can only compare a maximum of {config name=maxComparisons} products in a single step', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/plugins/compare/index', 1, 2, 'DetailActionLinkCompare', 'Compare products', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/detail/comment', 1, 2, 'InquiryTextArticle', 'I have the following questions on the product', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/plugins/recommendation/blocks_detail', 1, 2, 'DetailBoughtArticlesSlider', 'Customers also bought:', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend', 1, 2, 'CheckoutArticleNotFound', 'Product could not be found', '2012-08-22 15:57:47', '2012-08-22 15:57:47');

-- 16-fix-frontend-translation_see_detail.sql
REPLACE INTO `s_core_snippets` (`namespace`, `shopID`, `localeID`, `name`, `value`, `created`, `updated`) VALUES
('frontend/listing/box_article', 1, 2, 'ListingBoxLinkDetails', 'See details', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/listing/box_similar', 1, 2, 'SimilarBoxLinkDetails', 'See details', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/listing/box_similar', 1, 2, 'SimilarBoxMore', 'See details', '2012-08-22 15:57:47', '2012-08-22 15:57:47');

-- 17-refactor_cache_module.sql
DELETE FROM `s_core_menu` WHERE `name` = 'Proxy/Model-Cache';
DELETE FROM `s_core_menu` WHERE `name` = 'Konfiguration';
UPDATE `s_core_menu` SET `name` = 'Konfiguration + Template', `action` = 'Config', `shortcut` = 'STRG + ALT + X'  WHERE `name` = 'Textbausteine + Template';
UPDATE `s_core_menu` SET `action` = 'Frontend', `shortcut` = 'STRG + ALT + F' WHERE `name` = 'Artikel + Kategorien';

-- 1-fix-some-table-layouts.sql
DROP TABLE IF EXISTS `s_core_plugin_configs`, `s_core_plugin_elements`, `s_core_engine_queries`, `s_core_licences`, `s_plugin_benchmark_log`;
ALTER TABLE `s_filter_articles` ENGINE=InnoDB, CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE `s_order_history` ENGINE=InnoDB, CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE `s_plugin_widgets_notes` ENGINE=InnoDB, CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;

-- 2-fix-broken-unicode-strings.sql
UPDATE `s_core_config_elements` SET `value` = 's:375:"ab,die,der,und,in,zu,den,das,nicht,von,sie,ist,des,sich,mit,dem,dass,er,es,ein,ich,auf,so,eine,auch,als,an,nach,wie,im,für,einen,um,werden,mehr,zum,aus,ihrem,style,oder,neue,spieler,können,wird,sind,ihre,einem,of,du,sind,einer,über,alle,neuen,bei,durch,kann,hat,nur,noch,zur,gegen,bis,aber,haben,vor,seine,ihren,jetzt,ihr,dir,etc,bzw,nach,deine,the,warum,machen,0,sowie,am";' WHERE `name` LIKE 'badwords';

-- 3-increase-mail-context-size.sql
ALTER TABLE `s_core_config_mails` CHANGE `context` `context` LONGTEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;

-- 4-add-index-to-search-statistics.sql
DROP TABLE IF EXISTS s_statistics_search_backup;
CREATE TABLE IF NOT EXISTS `s_statistics_search_new` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `datum` datetime NOT NULL,
  `searchterm` varchar(255) CHARACTER SET latin1 NOT NULL,
  `results` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `searchterm` (`searchterm`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
RENAME TABLE s_statistics_search TO s_statistics_search_backup;
INSERT INTO s_statistics_search_new
(SELECT * FROM s_statistics_search_backup);
RENAME TABLE s_statistics_search_new TO s_statistics_search;
DROP TABLE s_statistics_search_backup;

-- 5-update-plugin-description.sql
UPDATE `s_core_plugins` set description = REPLACE(description, 'als einziger BaFin-zertifizierter', 'als BaFin-zertifizierter') WHERE name LIKE "HeidelPayment" OR name LIKE "HeidelActions";

-- 6-fix-configurator-table-layout.sql
CREATE TABLE IF NOT EXISTS `new_s_article_configurator_options` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `group_id` int(11) unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `position` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `group_id` (`group_id`,`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
INSERT IGNORE INTO `new_s_article_configurator_options` (`id`, `group_id`, `name`, `position`)
SELECT `id`, `group_id`, `name`, `position` FROM `s_article_configurator_options`;
DROP TABLE IF EXISTS `s_article_configurator_options`;
RENAME TABLE `new_s_article_configurator_options` TO `s_article_configurator_options`;

CREATE TABLE IF NOT EXISTS `new_s_article_configurator_set_group_relations` (
  `set_id` int(11) unsigned NOT NULL DEFAULT '0',
  `group_id` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`set_id`,`group_id`)
) ENGINE=InnoDB DEFAULT COLLATE=utf8_unicode_ci;
INSERT IGNORE INTO `new_s_article_configurator_set_group_relations` (`set_id`, `group_id`)
SELECT `set_id`, `group_id` FROM `s_article_configurator_set_group_relations`;
DROP TABLE IF EXISTS `s_article_configurator_set_group_relations`;
RENAME TABLE `new_s_article_configurator_set_group_relations` TO `s_article_configurator_set_group_relations`;

CREATE TABLE IF NOT EXISTS `new_s_article_configurator_set_option_relations` (
  `set_id` int(11) unsigned NOT NULL DEFAULT '0',
  `option_id` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`set_id`,`option_id`)
) ENGINE=InnoDB DEFAULT COLLATE=utf8_unicode_ci;
INSERT IGNORE INTO `new_s_article_configurator_set_option_relations` (`set_id`, `option_id`)
SELECT `set_id`, `option_id` FROM `s_article_configurator_set_option_relations`;
DROP TABLE IF EXISTS `s_article_configurator_set_option_relations`;
RENAME TABLE `new_s_article_configurator_set_option_relations` TO `s_article_configurator_set_option_relations`;

-- 7-remove-unused-config-elements.sql
DELETE FROM `s_core_config_elements`
WHERE `name` IN ('revision', 'version');

UPDATE `s_core_config_elements` SET value = 'i:8;', `type` = 'number' WHERE name = 'chartrange';
UPDATE `s_core_config_elements` SET value = 's:8:"51,51,51";' WHERE name = 'captchaColor';
UPDATE `s_core_config_elements` SET value = 's:15:"Shopware 4 Demo";' WHERE name = 'shopName';
DELETE FROM `s_core_config_values` WHERE id < 56;

-- 8-change-decimal-precision-of-purchaseunit.sql
ALTER TABLE `s_articles_details` CHANGE `purchaseunit` `purchaseunit` DECIMAL( 11, 4 ) UNSIGNED NULL DEFAULT NULL;

-- 9-add-snippets-for-frontend-order-item.sql
INSERT IGNORE INTO `s_core_snippets` (`namespace`,`shopID`,`localeID`,`name`,`value`) VALUES('frontend/account/order_item', 1, 1, 'OrderItemInfoCompleted', 'Komplett abgeschlossen');
INSERT IGNORE INTO `s_core_snippets` (`namespace`,`shopID`,`localeID`,`name`,`value`) VALUES('frontend/account/order_item', 1, 1, 'OrderItemInfoPartiallyCompleted', 'Teilweise abgeschlossen');
INSERT IGNORE INTO `s_core_snippets` (`namespace`,`shopID`,`localeID`,`name`,`value`) VALUES('frontend/account/order_item', 1, 1, 'OrderItemInfoClarificationNeeded', 'Klärung notwendig');
INSERT IGNORE INTO `s_core_snippets` (`namespace`,`shopID`,`localeID`,`name`,`value`) VALUES('frontend/account/order_item', 1, 1, 'OrderItemInfoReadyForShipping', 'Zur Lieferung bereit');

INSERT IGNORE INTO `s_core_snippets` (`namespace`,`shopID`,`localeID`,`name`,`value`) VALUES('frontend/account/order_item', 1, 2, 'OrderItemInfoCompleted', 'Completed');
INSERT IGNORE INTO `s_core_snippets` (`namespace`,`shopID`,`localeID`,`name`,`value`) VALUES('frontend/account/order_item', 1, 2, 'OrderItemInfoPartiallyCompleted', 'Partially completed');
INSERT IGNORE INTO `s_core_snippets` (`namespace`,`shopID`,`localeID`,`name`,`value`) VALUES('frontend/account/order_item', 1, 2, 'OrderItemInfoClarificationNeeded', 'Clarification needed');
INSERT IGNORE INTO `s_core_snippets` (`namespace`,`shopID`,`localeID`,`name`,`value`) VALUES('frontend/account/order_item', 1, 2, 'OrderItemInfoReadyForShipping', 'Ready for shipping');
