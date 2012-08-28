-- //

UPDATE `s_core_config_forms`
SET `label`='Versandkosten-Modul'
WHERE `label`='Versandkosten-Module';

DELETE FROM `s_core_config_elements`
WHERE `name`='premiumshippiung' AND `label`='Modul aktivieren';

-- //@UNDO

-- //