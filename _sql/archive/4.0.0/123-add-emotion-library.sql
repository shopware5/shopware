CREATE TABLE IF NOT EXISTS s_emotion (
  id int(11) NOT NULL AUTO_INCREMENT,
  name varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  gridID int(11) DEFAULT NULL,
  categoryID int(11) DEFAULT NULL,
  valid_from datetime NOT NULL,
  valid_to datetime NOT NULL,
  userID int(11) DEFAULT NULL,
  create_date datetime NOT NULL,
  template varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  modified datetime DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

INSERT INTO s_emotion (id, name, gridID, categoryID, valid_from, valid_to, userID, create_date, template, modified) VALUES
(1, 'first-emotion', 1, 1118, '2012-05-06 00:00:00', '2012-05-31 00:00:00', 48, '2012-05-21 00:00:00', 'promotion_article.tpl', '2012-05-21 00:00:00');


CREATE TABLE IF NOT EXISTS s_emotion_element (
  id int(11) NOT NULL AUTO_INCREMENT,
  emotionID int(11) NOT NULL,
  componentID int(11) NOT NULL,
  start_row int(11) NOT NULL,
  start_col int(11) NOT NULL,
  end_row int(11) NOT NULL,
  end_col int(11) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

INSERT INTO s_emotion_element (id, emotionID, componentID, start_row, start_col, end_row, end_col) VALUES
(1, 1, 1, 2, 1, 4, 2),
(2, 1, 2, 1, 3, 4, 4),
(3, 1, 3, 1, 1, 1, 2);


CREATE TABLE IF NOT EXISTS s_emotion_element_value (
  id int(11) NOT NULL AUTO_INCREMENT,
  elementID int(11) NOT NULL,
  componentID int(11) NOT NULL,
  fieldID int(11) NOT NULL,
  value text COLLATE utf8_unicode_ci,
  PRIMARY KEY (id),
  KEY emotionID (elementID),
  KEY fieldID (fieldID)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;


INSERT INTO s_emotion_element_value (id, elementID, componentID, fieldID, value) VALUES
(1, 1, 1, 1, 'NEU-KUNDEN-GUTSCHEIN'),
(2, 1, 1, 2, 'Holen Sie sich den Neu kunden gutschein!');


CREATE TABLE IF NOT EXISTS s_emotion_grid (
  id int(11) NOT NULL AUTO_INCREMENT,
  name varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  cols int(11) NOT NULL,
  rows int(11) NOT NULL,
  width int(11) NOT NULL,
  height int(11) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;


INSERT INTO s_emotion_grid (id, name, cols, rows, width, height) VALUES
(1, 'first-grid', 4, 10, 150, 150);


CREATE TABLE IF NOT EXISTS s_library_component (
  id int(11) NOT NULL AUTO_INCREMENT,
  name varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  description text COLLATE utf8_unicode_ci NOT NULL,
  template varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  cls varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  pluginID int(11) DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;


INSERT INTO s_library_component (id, name, description, template, cls, pluginID) VALUES
(1, 'Gutschein', 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam', 'component_voucher', 'voucher-element', NULL),
(2, 'HTML-Element', 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam', 'component_html', 'html-text-element', NULL),
(3, 'Banner', 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam', 'component_banner', 'banner-element', NULL);


CREATE TABLE IF NOT EXISTS s_library_component_field (
  id int(11) NOT NULL AUTO_INCREMENT,
  componentID int(11) NOT NULL,
  name varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  x_type varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  field_label varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  support_text varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  help_title varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  help_text text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;


INSERT INTO s_library_component_field (id, componentID, name, x_type, field_label, support_text, help_title, help_text) VALUES
(1, 1, 'code', 'textfield', 'Gutschein Code', 'Geben sie hier den Gutschein Code ein', 'Lorem ipsum dolor', 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam'),
(4, 2, 'text', 'tinymce', 'Text', 'Anzuzeigender Text', 'HTML-Text', 'Geben Sie hier den Text ein der im Element angezeigt werden soll.'),
(3, 3, 'file', 'mediaselectionfield', 'Bild', '', '', ''),
(2, 1, 'title', 'textfield', 'Der Titel', 'Titel der oben angezeigt wird', 'Lorem ipsum dolor', 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam');

-- //@UNDO

DROP TABLE s_emotion;
DROP TABLE s_emotion_element;
DROP TABLE s_emotion_element_value;
DROP TABLE s_library_component;
DROP TABLE s_library_component_field;