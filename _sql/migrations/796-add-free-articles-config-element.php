<?php

class Migrations_Migration796 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = <<<SQL
INSERT INTO `s_core_config_elements` (`id`, `form_id`, `name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`)
VALUES (NULL,259,'allowFreeArticles','b:1;','Allow free articles','Allows users to store prices with the amount "0"','boolean',0,0,0)
SQL;
        $this->addSql($sql);
    }
}
