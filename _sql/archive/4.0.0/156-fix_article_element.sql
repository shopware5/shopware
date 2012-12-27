UPDATE s_library_component_field SET x_type='articlesearchfield' WHERE name='article' AND x_type='articleSearchField';

-- //@UNDO

UPDATE s_library_component_field SET x_type='articleSearchField' WHERE name='article' AND x_type='articlesearchfield';