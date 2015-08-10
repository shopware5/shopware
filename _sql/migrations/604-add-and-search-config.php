<?php

class Migrations_Migration604 Extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = <<<SQL
SET @formId = (SELECT id FROM `s_core_config_forms` WHERE `name` = 'Search' LIMIT 1);

INSERT INTO `s_core_config_elements` (`id`, `form_id`, `name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`, `filters`, `validators`)
VALUES (NULL, @formId, 'enableAndSearchLogic', 'b:0;', '"Und" Suchlogik verwenden', 'Die Suche zeigt nur noch Treffer an, in denen alle Suchbegriffe vorkommen.', 'checkbox', '0', '0', '1', NULL, NULL);
SQL;
        $this->addSql($sql);

        $sql = <<<SQL
SET @elementId = (SELECT id FROM `s_core_config_elements` WHERE `name` = 'enableAndSearchLogic' LIMIT 1);

INSERT INTO `s_core_config_element_translations` (`id`, `element_id`, `locale_id`, `label`, `description`)
VALUES (NULL, @elementId, '2', 'Use "and" search logic', 'The search only shows hits , which contains all search terms.');
SQL;
        $this->addSql($sql);

    }
}
