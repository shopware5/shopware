-- //

DROP TABLE `s_core_config`;

DROP TABLE s_articles_groups;
DROP TABLE s_articles_groups_accessories_option;
DROP TABLE s_articles_groups_accessories;
DROP TABLE s_articles_groups_option;
DROP TABLE s_articles_groups_prices;
DROP TABLE s_articles_groups_settings;
DROP TABLE s_articles_groups_templates;
DROP TABLE s_articles_groups_value;

ALTER TABLE `s_articles`
  DROP `shippingfree`,
  DROP `releasedate`,
  DROP `minpurchase`,
  DROP `purchasesteps`,
  DROP `maxpurchase`,
  DROP `purchaseunit`,
  DROP `referenceunit`,
  DROP `packunit`,
  DROP `unitID`;

-- //@UNDO

--
