-- //

UPDATE `s_library_component` SET  `convert_function` =  'getArticle' WHERE `x_type` = 'emotion-components-article';

-- //@UNDO

UPDATE `s_library_component` SET  `convert_function` =  'getArticleByNumber'  WHERE `x_type` = 'emotion-components-category-teaser';

-- //