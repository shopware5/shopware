<?php
class Migrations_Migration231 Extends Shopware\Components\Migrations\AbstractMigration
{
    public function up()
    {
        $sql = <<<'EOD'
            SET @oldElementId = (SELECT id FROM `s_core_config_elements` WHERE `name` = 'redirectDownload' LIMIT 1);
            UPDATE s_core_config_elements SET form_id = -1 WHERE id = @oldElementId;


            SET @formId = (SELECT id FROM `s_core_config_forms` WHERE `name` LIKE 'Esd');

            INSERT IGNORE INTO `s_core_config_elements`
            (`form_id`, `name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`, `filters`, `validators`, `options`)
            VALUES (@formId, 'esdDownloadStrategy', 'i:1;',
            'Downloadoption für ESD Dateien',
            'Option zum Generieren von Downloadlinks für ESD Dateien. Direkter Dateilink: größere Performance, jedoch möglicherweise unsicher; Generierter Link: Sicherer, benötigt jedoch mehr Arbeitsspeicher, besonders für größere Dateien; Benutze X-Sendfile Modul: Sicher und performant, setzt jedoch das X-Sendfile Apache Modul voraus.',
            'select', '1', '4', '0', NULL, NULL,
            'a:1:{s:5:"store";a:4:{i:0;a:2:{i:0;i:0;i:1;s:16:"Direct file link";}i:1;a:2:{i:0;i:1;i:1;s:14:"Generated link";}i:2;a:2:{i:0;i:2;i:1;s:38:"Using X-Sendfile (only Apache2 server)";}i:3;a:2:{i:0;i:3;i:1;s:33:"Using X-Accel (only Nginx server)";}}}'
            );

            SET @newElementId = (SELECT id FROM `s_core_config_elements` WHERE `name` = 'esdDownloadStrategy' LIMIT 1);
            INSERT IGNORE INTO `s_core_config_element_translations` (`element_id`, `locale_id`, `label`, `description`)
            VALUES (@newElementId, '2',
            'Download strategy for ESD files',
            'Strategy to generate the download links for ESD files. <br> - Direct file link: Better performance, but possibly insecure <br> -  Generated link: More secure, but memory consuming, specially for bigger files <br> -  Using X-Sendfile: Secure and lightweight, but requires X-Sendfile module and Apache2 web server <br> -  Using X-Accel: Equivalent to X-Sendfile, but requires Nginx web server instead' );

            INSERT INTO s_core_config_values (element_id, shop_id, value)
            SELECT @newElementId as element_id, shop_id, IF(STRCMP(value, 'b:0') = 0,'i:0','i:1') as value
            FROM s_core_config_values
            WHERE element_id = @oldElementId;
EOD;
        $this->addSql($sql);
    }
}