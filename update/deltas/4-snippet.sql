REPLACE INTO s_core_snippets
SELECT s.*
FROM backup_s_core_snippets s
LEFT JOIN backup_s_core_snippets o
ON o.namespace = s.namespace
AND o.name = s.name
AND o.shopID = 1
AND o.localeID = 1
WHERE (s.namespace LIKE 'frontend/%'
OR s.namespace LIKE 'documents/%'
OR s.namespace LIKE 'newsletter/%')
AND s.updated > '2011-05-25 00:00:00'
AND s.value != ''
AND (o.value IS NULL OR s.id = o.id OR s.value != o.value)
AND s.value NOT LIKE '%$this->%';