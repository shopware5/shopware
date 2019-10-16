<?php

class Migrations_Migration1703 Extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = <<<'EOD'
SET @parent = (SELECT id FROM s_core_config_forms WHERE name = 'Frontend100');
INSERT IGNORE INTO `s_core_config_elements`
(`form_id`, `name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`, `options`)
VALUES
(@parent, 'ignore_trailing_slash', 'b:1;', 'URLs ohne abschließenden Slash weiterleiten', 'Wenn aktiv, werden URLs, die normalerweise auf einen Slash (“/”) enden und ohne diesen aufgerufen werden, mittels http-code 301 auf die korrekte Seite mit Slash weitergeleitet. Der Canonical zeigt dabei immer auf die korrekte Seite mit Slash', 'boolean', 1, 0, 0, NULL);
SET @elementId = (SELECT id FROM `s_core_config_elements` WHERE `name` = 'ignore_trailing_slash' LIMIT 1);
INSERT IGNORE INTO `s_core_config_element_translations` (`element_id`, `locale_id`, `label`, `description`)
VALUES (@elementId, '2', 'Redirect urls without trailing slash', 'If active, URLs that normally end in a slash ("/") and are called without it are forwarded to the correct page with slash via http-code 301. The Canonical always points to the correct page with slash.' );
EOD;
        $this->addSql($sql);
        if ($modus === self::MODUS_UPDATE) {
            $sql = <<<'EOD'
SET @elementId = (SELECT form_id FROM s_core_config_elements WHERE name = 'ignore_trailing_slash');
Insert Into s_core_config_values (element_id, shop_id,value) SELECT @elementId, s.id, 'b:0' from s_core_shops s;     
EOD;
            $this->addSql($sql);
        }
    }
}
