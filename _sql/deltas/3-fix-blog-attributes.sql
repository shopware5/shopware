-- //

ALTER TABLE `s_blog_attributes` ADD INDEX ( `blog_id` ) ;

ALTER TABLE `s_blog_attributes` ADD FOREIGN KEY ( `blog_id` ) REFERENCES `s_blog` (
`id`
) ON DELETE CASCADE ON UPDATE NO ACTION ;


-- //@UNDO

-- //

