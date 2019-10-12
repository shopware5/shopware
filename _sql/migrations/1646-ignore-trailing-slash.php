<?php
class Migrations_Migration1646 Extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = <<<'EOD'
SET @parent = (SELECT id FROM s_core_config_forms WHERE name = 'Frontend100');

INSERT IGNORE INTO `s_core_config_elements`
(`form_id`, `name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`, `options`)
VALUES
(@parent, 'ignore_trailing_slash', 'b:1;', 'Ignorieren von nachgestellten Schrägstrichen ', '', 'boolean', 1, 0, 0, NULL);

SET @elementId = (SELECT id FROM `s_core_config_elements` WHERE `name` = 'ignore_trailing_slash' LIMIT 1);
INSERT IGNORE INTO `s_core_config_element_translations` (`element_id`, `locale_id`, `label`, `description`)
VALUES (@elementId, '2', 'Ignore trailing slashes', '' );
EOD;

        $this->addSql($sql);
    }
}
