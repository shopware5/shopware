-- //

DELETE FROM s_core_config_elements WHERE name LIKE 'fuzzysearch%';
SET @parent = (SELECT id FROM s_core_config_forms WHERE label='Suche');
INSERT INTO `s_core_config_elements` (`form_id`, `name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`, `filters`, `validators`, `options`) VALUES
(@parent, 'fuzzysearchdistance', 'i:20;', 'Maximal-Distanz für Unscharfe Suche in Prozent', NULL, 'number', 1, 0, 1, NULL, NULL, NULL),
(@parent, 'fuzzysearchexactmatchfactor', 'i:100;', 'Faktor für genaue Treffer', NULL, 'number', 1, 0, 1, NULL, NULL, NULL),
(@parent, 'fuzzysearchlastupdate', 's:19:"2010-01-01 00:00:00";', 'Datum des letzten Updates', NULL, 'datetime', 0, 0, 0, NULL, NULL, NULL),
(@parent, 'fuzzysearchmatchfactor', 'i:5;', 'Faktor für unscharfe Treffer', NULL, 'number', 1, 0, 1, NULL, NULL, NULL),
(@parent, 'fuzzysearchmindistancentop', 'i:20;', 'Minimale Relevanz zum Topartikel in Prozent', NULL, 'number', 1, 0, 1, NULL, NULL, NULL),
(@parent, 'fuzzysearchpartnamedistancen', 'i:25;', 'Maximal-Distanz für Teilnamen in Prozent', NULL, 'number', 1, 0, 1, NULL, NULL, NULL),
(@parent, 'fuzzysearchpatternmatchfactor', 'i:50;', 'Faktor für Teiltreffer', NULL, 'number', 1, 0, 1, NULL, NULL, NULL),
(@parent, 'fuzzysearchpricefilter', 's:47:"5|10|20|50|100|300|600|1000|1500|2500|3500|5000";', 'Auswahl Preisfilter', NULL, 'text', 1, 0, 1, NULL, NULL, NULL),
(@parent, 'fuzzysearchresultsperpage', 'i:12;', 'Ergebnisse pro Seite', NULL, 'number', 1, 0, 1, NULL, NULL, NULL),
(@parent, 'fuzzysearchselectperpage', 's:11:"12|24|36|48";', 'Auswahl Ergebnisse pro Seite', NULL, 'text', 1, 0, 1, NULL, NULL, NULL);

-- //@UNDO

-- //
