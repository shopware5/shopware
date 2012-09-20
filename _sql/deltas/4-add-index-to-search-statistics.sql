-- //
  ALTER TABLE s_statistics_search DROP INDEX searchterm;
  ALTER TABLE `s_statistics_search` ADD INDEX ( `searchterm` );
-- //@UNDO

-- //

