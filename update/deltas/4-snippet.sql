REPLACE INTO s_core_snippets (`namespace` , `shopID` , `localeID` , `name` , `value` , `created` , `updated`)
SELECT s.namespace, s.shopID, s.localeID, s.name, s.value, s.created, NOW() as updated, o.value, o2.value
FROM backup_s_core_snippets s

LEFT JOIN s_core_snippets o
ON o.namespace = s.namespace
AND o.name = s.name
AND o.shopID = 1
AND o.localeID = 1

LEFT JOIN s_core_snippets o2
ON o2.namespace = s.namespace
AND o2.name = s.name
AND o2.shopID = 1
AND o2.localeID = 2

WHERE (s.namespace LIKE 'frontend/%'
OR s.namespace LIKE 'documents/%'
OR s.namespace LIKE 'newsletter/%')
AND s.value != ''
AND o.id IS NOT NULL
AND (o.value IS NULL OR s.value != o.value)
AND (o2.value IS NULL OR s.value != o2.value)
AND s.value NOT LIKE '%$this->%'
AND s.value NOT LIKE '%+I120n%'
AND (s.created != '' OR s.updated > '2010-10-18 00:00:0')
AND s.value NOT LIKE '%201% shopware%'
AND (o.localeID != 1 OR s.namespace NOT IN ('frontend/search/fuzzy_left', 'frontend/register/steps'));
