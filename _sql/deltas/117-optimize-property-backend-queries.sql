

ALTER TABLE `s_filter_relations` ADD INDEX  `get_set_assigns_query` (  `groupID`, `position` );
ALTER TABLE `s_filter` ADD INDEX  `get_sets_query` (  `position` );
ALTER TABLE `s_filter_options` ADD INDEX  `get_options_query` (  `name` );