INSERT IGNORE INTO s_core_countries (id, countryname, countryiso, countryen, position, notice, shippingfree, taxfree, taxfree_ustid, active, iso3)
SELECT id, countryname, countryiso, countryen, position, notice, shippingfree, taxfree, taxfree_ustid, active, iso3 FROM backup_s_core_countries;

INSERT IGNORE INTO `s_core_countries_areas` (`name`, `active`)
SELECT DISTINCT countryarea, 1
FROM backup_s_core_countries;

UPDATE s_core_countries c, backup_s_core_countries b
LEFT JOIN s_core_countries_areas a
ON a.name LIKE b.countryarea
SET c.active = b.active, c.areaID = a.id,
c.taxfree_ustid = IF(b.taxfree_ustid_checked=1, b.taxfree_ustid_checked, b.taxfree_ustid)
WHERE c.countryiso = b.countryiso;
