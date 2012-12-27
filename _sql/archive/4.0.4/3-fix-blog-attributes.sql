-- //

DROP TABLE IF EXISTS s_blog_attributes_new;
DROP TABLE IF EXISTS s_blog_attributes_backup;

-- Copy structure and data to new table, this does _not_ copy the foreign keys, that's exacly what we want
CREATE TABLE s_blog_attributes_new LIKE s_blog_attributes;
INSERT INTO s_blog_attributes_new SELECT * FROM s_blog_attributes;

RENAME TABLE s_blog_attributes TO s_blog_attributes_backup, s_blog_attributes_new TO s_blog_attributes;

-- Add missing foreign key
ALTER TABLE `s_blog_attributes` ADD FOREIGN KEY ( `blog_id` ) REFERENCES `s_blog` (
        `id`
) ON DELETE CASCADE ON UPDATE NO ACTION ;

DROP TABLE s_blog_attributes_backup;

-- //@UNDO

-- //

