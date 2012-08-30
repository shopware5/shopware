-- //

CREATE TABLE IF NOT EXISTS `s_blog_assigned_articles` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `blog_id` int(11) unsigned NOT NULL,
  `article_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `blog_id` (`blog_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- //@UNDO

DROP TABLE IF EXISTS `s_blog_tags`;

-- //
