<?php

class Migrations_Migration762 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        // s_article_configurator_templates
        $sql = <<<'EOD'
            ALTER TABLE `s_article_configurator_templates`
            MODIFY COLUMN `order_number` varchar(255) NOT NULL;
EOD;
        $this->addSql($sql);
    }
}
