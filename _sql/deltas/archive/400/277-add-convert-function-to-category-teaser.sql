-- //

UPDATE `s_library_component` SET  `convert_function` =  'getCategoryTeaser' WHERE `x_type` = 'emotion-components-category-teaser';

-- //@UNDO

UPDATE `s_library_component` SET  `convert_function` =  NULL  WHERE `x_type` = 'emotion-components-category-teaser';

-- //