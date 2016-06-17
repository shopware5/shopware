<?php

class Migrations_Migration775 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $this->createUniqueIndex();

        $sql = <<<SQL
INSERT INTO `s_core_config_elements` (`id`, `form_id`, `name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`)
VALUES (NULL, '0', 'assetTimestamp', 'i:0;', '', 'Cache invalidation timestamp for assets', '', '0', '0', '1')
SQL;
        $this->addSql($sql);
    }

    private function createUniqueIndex()
    {
        $sql = <<<SQL
ALTER IGNORE TABLE `s_core_config_values`
ADD UNIQUE `element_id_shop_id` (`element_id`, `shop_id`);
SQL;
        $this->addSql($sql);
    }
}
