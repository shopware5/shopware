UPDATE `s_core_menu`
SET `name` = 'Einkaufswelten', `onclick` = ''
WHERE `controller` LIKE 'Emotion'
AND `class` LIKE 'sprite-pin';

-- //@UNDO

UPDATE `s_core_menu`
SET `name` = 'Einkaufswelten*', `onclick` = "loadSkeleton('promotion')"
WHERE `controller` LIKE 'Emotion'
AND `class` LIKE 'sprite-pin';