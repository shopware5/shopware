ALTER TABLE s_library_component ADD convert_function VARCHAR( 255 ) NULL DEFAULT NULL AFTER x_type;

UPDATE s_library_component
    SET x_type = 'emotion-components-article',
        convert_function = 'getArticleByNumber'
WHERE name = 'Artikel';


UPDATE s_library_component_field
    SET x_type = 'emotion-components-fields-article',
        name = 'article'
WHERE componentID = 4;

-- //@UNDO


ALTER TABLE s_library_component DROP convert_function;

