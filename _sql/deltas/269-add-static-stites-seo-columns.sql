-- //

    ALTER TABLE `s_cms_static`
    ADD `page_title` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
    ADD `meta_keywords` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
    ADD `meta_description` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ;


-- //@UNDO

    ALTER TABLE `s_cms_static`
    DROP `page_title`,
    DROP `meta_keywords`,
    DROP `meta_description`;

-- //

