INSERT IGNORE INTO `s_core_countries_areas` (`name`, `active`)
SELECT DISTINCT countryarea, 1
FROM backup_s_core_countries;

UPDATE s_core_countries c, backup_s_core_countries b
LEFT JOIN s_core_countries_areas a
ON a.name LIKE b.countryarea
SET c.active = b.active, c.areaID = a.id
WHERE c.countryiso = b.countryiso;
