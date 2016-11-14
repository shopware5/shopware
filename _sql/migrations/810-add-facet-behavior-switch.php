<?php

use Shopware\Components\Migrations\AbstractMigration;

class Migrations_Migration810 extends AbstractMigration
{
    /**
     * @inheritdoc
     */
    public function up($modus)
    {
        $sql = <<<EOD
INSERT IGNORE INTO `s_core_config_elements` (`id`, `form_id`, `name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`) VALUES
(NULL, 0, 'generatePartialFacets','i:0;', '', '', '', 1, 0, 0);
EOD;
        $this->addSql($sql);

        $sql = <<<EOD
INSERT IGNORE INTO `s_core_config_elements` (`id`, `form_id`, `name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`) VALUES
(NULL, 0, 'categoryFilterDepth', 'i:2;', '', '', '', 1, 0, 0);
EOD;
        $this->addSql($sql);
    }
}
