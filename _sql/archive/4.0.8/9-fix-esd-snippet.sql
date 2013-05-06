-- //
UPDATE `s_core_snippets`
SET `value` = 'Dieser Download steht Ihnen nicht zur Verfügung!'
WHERE `name` LIKE 'DownloadsInfoAccessDenied'
AND `value` LIKE 'Dieser Download stehen Ihnen nicht zur Verfügung!';

-- //@UNDO

-- //


