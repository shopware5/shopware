-- //

UPDATE s_core_menu SET name = CONCAT(name, '', '*') WHERE onclick LIKE 'loadSkeleton%' OR onclick LIKE 'openAction%';

-- //@UNDO
ALTER TABLE s_core_menu DROP INDEX name;
UPDATE s_core_menu SET name = SUBSTR(name, 1,INSTR(name, '*')-1) WHERE INSTR(name, '*')-1) > 0 AND (onclick LIKE 'loadSkeleton%' OR onclick LIKE 'openAction%');
ALTER TABLE `s_core_menu` ADD UNIQUE `name` (`parent` , `name`);
--