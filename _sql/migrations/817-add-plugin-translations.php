<?php

class Migrations_Migration817 extends Shopware\Components\Migrations\AbstractMigration
{
    /**
     * {@inheritdoc}
     */
    public function up($modus)
    {
        $sql = <<<'SQL'
ALTER TABLE `s_core_plugins` ADD `translations` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL AFTER `description`;
SQL;
        $this->addSql($sql);
    }
}
