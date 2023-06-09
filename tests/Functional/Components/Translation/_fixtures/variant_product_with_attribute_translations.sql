INSERT INTO `s_articles` (`id`, `supplierID`, `name`, `description`, `description_long`, `shippingtime`, `datum`,
                          `active`, `taxID`, `pseudosales`, `topseller`, `metaTitle`, `keywords`, `changetime`,
                          `pricegroupID`, `pricegroupActive`, `filtergroupID`, `laststock`, `crossbundlelook`,
                          `notification`, `template`, `mode`, `main_detail_id`, `available_from`, `available_to`,
                          `configurator_set_id`)
VALUES (:productId, 1, 'Translation test product', '',
        '<p>Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.</p>',
        NULL, '2023-06-06', 1, 1, 0, 0, '', '', '2023-06-06 08:21:23', NULL, 0, NULL, 0, 0, 0, '', 0, :mainVariantId, NULL, NULL,
        36);

INSERT INTO `s_articles_details` (`id`, `articleID`, `ordernumber`, `suppliernumber`, `kind`, `additionaltext`, `sales`, `active`, `instock`, `stockmin`, `laststock`, `weight`, `position`, `width`, `height`, `length`, `ean`, `unitID`, `purchasesteps`, `maxpurchase`, `minpurchase`, `purchaseunit`, `referenceunit`, `packunit`, `releasedate`, `shippingfree`, `shippingtime`, `purchaseprice`) VALUES
    (:mainVariantId, :productId, 'SW19856321', '', 1, '', 0, 1, 0, 0, 0, NULL, 0, NULL, NULL, NULL, '', NULL, NULL, NULL, 1, NULL, NULL, '', NULL, 0, '', 0),
    (:variantIdOne, :productId, 'SW19856321.4', '', 2, '', 0, 1, 0, 0, 0, NULL, 0, NULL, NULL, NULL, '', NULL, NULL, NULL, 1, NULL, NULL, '', NULL, 0, '', 0),
    (:variantIdTwo, :productId, 'SW19856321.5', '', 2, '', 0, 1, 0, 0, 0, NULL, 0, NULL, NULL, NULL, '', NULL, NULL, NULL, 1, NULL, NULL, '', NULL, 0, '', 0);

INSERT INTO `s_articles_attributes` (`articledetailsID`, `attr1`, `attr2`, `attr3`, `attr4`, `attr5`, `attr6`, `attr7`, `attr8`, `attr9`, `attr10`, `attr11`, `attr12`, `attr13`, `attr14`, `attr15`, `attr16`, `attr17`, `attr18`, `attr19`, `attr20`) VALUES
    (:mainVariantId, '0,2 Liter Freitext - 1', '0,2 Liter Freitext - 2', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
    (:variantIdOne, '0,7 Liter Freitext - 1', '0,7 Liter Freitext - 2', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
    (:variantIdTwo, '1 Liter Freitext - 1', '1 Liter Freitext - 2', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

INSERT INTO `s_articles_categories` (`articleID`, `categoryID`) VALUES
    (:productId, 14),
    (:productId, 50);

INSERT INTO `s_articles_prices` (`pricegroup`, `from`, `to`, `articleID`, `articledetailsID`, `price`, `pseudoprice`, `regulation_price`, `baseprice`, `percent`) VALUES
    ('EK', 1, 'beliebig', :productId, :mainVariantId, 84.025210084034, 0, 0, NULL, 10.00),
    ('EK', 1, 'beliebig', :productId, :variantIdOne, 84.025210084034, 0, 0, NULL, 10.00),
    ('EK', 1, 'beliebig', :productId, :variantIdTwo, 84.025210084034, 0, 0, NULL, 10.00);

INSERT INTO `s_articles_translations` (`articleID`, `languageID`, `name`, `keywords`, `description`, `description_long`, `description_clear`, `shippingtime`, `attr1`, `attr2`, `attr3`, `attr4`, `attr5`) VALUES
    (:productId, 2, '', '', '', '', '', '', '0,2 Litre Freetext - 1', '0,2 Litre Freetext - 2', '', '', '');

INSERT INTO `s_core_translations` (`objecttype`, `objectdata`, `objectkey`, `objectlanguage`, `dirty`) VALUES
    ('article', 'a:2:{s:17:\"__attribute_attr1\";s:22:\"0,2 Litre Freetext - 1\";s:17:\"__attribute_attr2\";s:22:\"0,2 Litre Freetext - 2\";}', :productId, '2', 1),
    ('variant', 'a:2:{s:17:\"__attribute_attr1\";s:22:\"0,7 Litre Freetext - 1\";s:17:\"__attribute_attr2\";s:22:\"0,7 Litre Freetext - 2\";}', :variantIdOne, '2', 1),
    ('variant', 'a:2:{s:17:\"__attribute_attr1\";s:20:\"1 Litre Freetext - 1\";s:17:\"__attribute_attr2\";s:20:\"1 Litre Freetext - 2\";}', :variantIdTwo, '2', 1),
    ('article', 'a:2:{s:17:\"__attribute_attr1\";s:22:\"0,2 Foo Bar - 1\";s:17:\"__attribute_attr2\";s:22:\"0,2 Litre Freetext - 2\";}', :productId, '3', 1),
    ('variant', 'a:2:{s:17:\"__attribute_attr1\";s:22:\"0,7 Foo Bar - 1\";s:17:\"__attribute_attr2\";s:22:\"0,7 Litre Freetext - 2\";}', :variantIdOne, '3', 1),
    ('variant', 'a:2:{s:17:\"__attribute_attr1\";s:20:\"1 Foo Bar - 1\";s:17:\"__attribute_attr2\";s:20:\"1 Litre Freetext - 2\";}', :variantIdTwo, '3', 1);
