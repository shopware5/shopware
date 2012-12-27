-- //
REPLACE INTO `s_core_snippets` (
`id` ,
`namespace` ,
`shopID` ,
`localeID` ,
`name` ,
`value` ,
`created` ,
`updated`
)
VALUES (
NULL , 'frontend/blog/comments', '1', '1', 'DetailCommentTextReview', 'Kommentare werden nach Überprüfung freigeschaltet.', '2010-01-01 00:00:00', '2010-09-28 11:54:19'
);

REPLACE INTO `s_core_snippets` (
`id` ,
`namespace` ,
`shopID` ,
`localeID` ,
`name` ,
`value` ,
`created` ,
`updated`
)
VALUES (
NULL , 'frontend/blog/comments', '1', '2', 'DetailCommentTextReview', 'Comments will be released after verification.', '0000-00-00 00:00:00', '2010-09-28 11:54:19'
);


ALTER TABLE `s_blog` CHANGE `seo_keywords` `meta_keywords` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;
ALTER TABLE `s_blog` CHANGE `seo_description` `meta_description` VARCHAR( 150 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;


-- //@UNDO

--