INSERT INTO s_library_component (name, x_type, description, template, cls)
                        VALUES ('Artikel',
                                'emotion-components-article',
                                'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam',
                                'component_article',
                                'article-element'
                        );

SET @componentID = (SELECT id FROM s_library_component WHERE x_type='emotion-components-article');
INSERT INTO s_library_component_field  (componentID, name, x_type, field_label, support_text, help_title, help_text)
                                VALUES (@componentID,
                                        'article',
                                        'articleSearchField',
                                        'Artikelsuche',
                                        'Der anzuzeigende Artikel',
                                        'Lorem ipsum dolor',
                                        'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam'
                                );

-- //@UNDO

DELETE FROM s_library_component WHERE x_type='emotion-components-article';
DELETE FROM s_library_component_field WHERE x_type='articleSearchField';