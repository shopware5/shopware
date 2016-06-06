<?php

class Migrations_Migration767 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        // s_order_details
        $sql = <<<'EOD'
            ALTER TABLE `s_order_details`
            MODIFY COLUMN `ordernumber` varchar(255) NOT NULL,
            MODIFY COLUMN `articleordernumber` varchar(255) NOT NULL;
EOD;
        $this->addSql($sql);
    }
}
