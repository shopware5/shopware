-- //

INSERT INTO `s_library_component` (`id`, `name`, `x_type`, `convert_function`, `description`, `template`, `cls`, `pluginID`) VALUES (NULL, 'Artikel-Slider', 'emotion-components-article-slider', 'getArticleSlider', '', 'component_article_slider', 'article-slider-element', NULL);

SET @parent = (SELECT id FROM `s_library_component` WHERE `template`='component_article_slider');
INSERT INTO  `s_library_component_field`
(`id` ,`componentID` ,`name` ,`x_type` ,`value_type` ,`field_label` ,`support_text` ,`help_title` ,`help_text` ,`store` ,`display_field` ,`value_field`)
VALUES
(NULL, @parent, 'article_slider_type', 'emotion-components-fields-article-slider-type', '', '', '', '', '', '', '', ''),
(NULL, @parent, 'selected_articles', 'hidden', 'json', '', '', '', '', '', '', ''),
(NULL ,  @parent,  'article_slider_max_number',  'numberfield',  '',  'max. Anzahl',  '',  '',  '',  '',  '',  ''),
(NULL ,  @parent,  'article_slider_title',  'textfield',  '',  'Überschrift',  '',  '',  '',  '',  '',  ''),
(NULL ,  @parent,  'article_slider_navigation',  'checkbox',  '',  'Navigation anzeigen',  '',  '',  '',  '',  '',  ''),
(NULL ,  @parent,  'article_slider_arrows',  'checkbox',  '',  'Pfeile anzeigen',  '',  '',  '',  '',  '',  ''),
(NULL ,  @parent,  'article_slider_numbers',  'checkbox',  '',  'Nummern ausgeben',  '',  '',  '',  '',  '',  ''),
(NULL ,  @parent,  'article_slider_scrollspeed',  'numberfield',  '',  'Scroll-Geschwindigkeit',  '',  '',  '',  '',  '',  ''),
(NULL ,  @parent,  'article_slider_rotation',  'checkbox',  '',  'Automatisch rotieren',  '',  '',  '',  '',  '',  ''),
(NULL ,  @parent,  'article_slider_scrollspeed',  'numberfield',  '',  'Rotations Geschwindigkeit',  '',  '',  '',  '',  '',  ''),
(NULL ,  @parent,  'article_slider_select',  'emotion-components-fields-category-slider-select',  '',  'Slider Typ',  '',  '',  '',  '',  '',  '');

-- //@UNDO

SET @parent = (SELECT id FROM `s_library_component` WHERE `template`='component_article_slider');
DELETE FROM `s_library_component_field` WHERE `componentID` = @parent;
DELETE FROM `s_library_component` WHERE `x_type` = 'emotion-components-article-slider';

--