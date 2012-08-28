-- //

UPDATE  `s_library_component` SET  `template` =  'component_blog',`cls` =  'blog-element' WHERE  `x_type` = 'emotion-components-blog';

-- //@UNDO

UPDATE  `s_library_component` SET  `template` =  'component_category_blog',`cls` = 'category-teaser-blog' WHERE  `x_type` = 'emotion-components-blog';

-- //