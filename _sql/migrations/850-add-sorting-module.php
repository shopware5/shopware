<?php

use Shopware\Components\Migrations\AbstractMigration;

class Migrations_Migration850 extends AbstractMigration
{
    /**
     * @inheritdoc
     */
    public function up($modus)
    {
        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS `s_search_custom_sorting` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `label` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `active` int(1) unsigned NOT NULL,
  `display_in_categories` int(1) unsigned NOT NULL,
  `position` int(11) NOT NULL,
  `sortings` LONGTEXT NOT NULL,
  `shops` LONGTEXT NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SQL;
        $this->addSql($sql);

        $sql = <<<SQL
SET @formId = (SELECT id FROM s_core_config_forms WHERE name = 'Frontend' LIMIT 1);

INSERT INTO `s_core_config_forms` (`parent_id`, `name`, `label`, `description`, `position`, `plugin_id`) VALUES
(@formId, 'CustomSearch', 'Sortierung / Filter', NULL, 0, NULL);
SQL;

        $this->addSql($sql);

        $sql = <<<SQL
SET @formId = (SELECT id FROM s_core_config_forms WHERE name = 'CustomSearch');

INSERT INTO `s_core_config_form_translations` (`form_id`, `locale_id`, `label`, `description`)
VALUES (@formId, '2', 'Sortings / Filter', NULL);
SQL;
        $this->addSql($sql);
    }
}
