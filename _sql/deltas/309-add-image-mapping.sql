-- //
ALTER TABLE  `s_articles_img` ADD `parent_id` INT UNSIGNED NULL DEFAULT NULL ,
						      ADD `article_detail_id` INT UNSIGNED NULL DEFAULT NULL;

ALTER TABLE  `s_articles_img` ADD INDEX (  `article_detail_id` );
ALTER TABLE  `s_articles_img` ADD INDEX (  `parent_id` );

ALTER TABLE  `s_articles_img` CHANGE  `img`  `img` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;


CREATE TABLE IF NOT EXISTS `s_article_img_mappings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `image_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `image_id` (`image_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `s_article_img_mapping_rules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mapping_id` int(11) NOT NULL,
  `option_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;



-- //@UNDO


ALTER TABLE `s_articles_img`
  DROP `parent_id`,
  DROP `article_detail_id`;

ALTER TABLE  `s_articles_img` CHANGE  `img`  `img` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;

DROP TABLE s_article_img_mappings;
DROP TABLE s_article_img_mapping_rules;


--