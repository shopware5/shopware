INSERT INTO s_articles (id, supplierID, name, description, description_long, shippingtime, datum, active, taxID, pseudosales, topseller, metaTitle, keywords, changetime, pricegroupID, pricegroupActive, filtergroupID, laststock, crossbundlelook, notification, template, mode, main_detail_id, available_from, available_to, configurator_set_id) VALUES
    (444444, 2, 'FOO', '', '', null, '2012-08-15', 1, 1, 20, 0, null, '', '2012-08-30 16:57:00', 1, 0, 1, 0, 0, 0, '', 0, null, null, null, 8);

INSERT INTO s_articles_details (id, articleID, ordernumber, suppliernumber, kind, additionaltext, sales, active, instock, stockmin, laststock, weight, position, width, height, length, ean, unitID, purchasesteps, maxpurchase, minpurchase, purchaseunit, referenceunit, packunit, releasedate, shippingfree, shippingtime, purchaseprice) VALUES
(444444, 444444, 'SWFOO', '', 1, '', 0, 1, 25, 0, 0, 0.000, 0, null, null, null, null, 1, null, null, 1, 0.7000, 1.000, 'Flasche(n)', '2012-06-13', 0, '', 0),
(444445, 444444, 'SWFOO.1', '', 1, '', 0, 1, 25, 0, 0, 0.000, 0, null, null, null, null, 1, null, null, 1, 0.7000, 1.000, 'Flasche(n)', '2012-06-13', 0, '', 0),
(444446, 444444, 'SWFOO.2', '', 1, '', 0, 1, 25, 0, 1, 0.000, 0, null, null, null, null, 1, null, null, 26, 0.7000, 1.000, 'Flasche(n)', '2012-06-13', 0, '', 0)
;

INSERT INTO s_article_configurator_option_relations (article_id, option_id) VALUES
(444444,  10)
,(444444, 20)
,(444444, 30)
,(444444, 40)

,(444445, 11)
,(444445, 20)
,(444445, 30)
,(444445, 40)

,(444446, 10)
,(444446, 21)
,(444446, 30)
,(444446, 40)

;
