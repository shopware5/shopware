TRUNCATE TABLE `s_core_config_values`;
-- SELECT e.name, e.value, CONCAT('s:', LENGTH(c.value), ':"', c.value, '";')
INSERT INTO `s_core_config_values` (element_id, shop_id, value)
SELECT e.id, 1, CONCAT('s:', LENGTH(c.value), ':"', c.value, '";')
FROM backup_s_core_config c, s_core_config_elements e
WHERE LOWER(SUBSTR(c.name, 2)) = e.name
AND CONCAT('i:', c.value, ';') != e.value
AND CONCAT('s:', LENGTH(c.value), ':"', c.value, '";') != e.value
AND (e.value != 'b:0;' OR c.value != 0)
AND (e.value != 'b:1;' OR c.value != 1)
AND e.name NOT IN ('detailtemplates', 'fuzzysearchlastupdate', 'seostaticurls', 'seoqueryalias', 'botBlackList');
