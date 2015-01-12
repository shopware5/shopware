<?php
class Migrations_Migration437 Extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = <<<'EOD'
SET @parent = (SELECT id FROM `s_core_config_forms` WHERE `name`='Esd');
INSERT INTO `s_core_config_elements`
(`id`, `form_id`, `name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`, `filters`, `validators`, `options`)
VALUES (NULL, @parent, 'showDownloads', 'b:1;', 'Sofortdownloads in Account anzeigen', 'Sofortdownloads können weiterhin über die Bestellübersicht heruntergeladen werden, jedoch wird dort keine Seriennummer angezeigt.', 'boolean', '1', '5', '', NULL, NULL, NULL);

SET @elementId = (SELECT id FROM `s_core_config_elements` WHERE `name` = 'showDownloads' LIMIT 1);
SET @localeID = (SELECT id FROM s_core_locales WHERE locale='en_GB');
INSERT IGNORE INTO s_core_config_element_translations
(element_id, locale_id, label, description)
VALUES (@elementID, @localeID, 'Show instant downloads in account', 'Instant downloads can already be downloaded from the order details page, but the serial number will not be shown there.');
EOD;

        $this->addSql($sql);
    }
}
