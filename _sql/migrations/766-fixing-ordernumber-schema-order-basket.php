<?php

class Migrations_Migration766 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        // s_order_basket
        $sql = <<<'EOD'
            ALTER TABLE `s_order_basket`
            MODIFY COLUMN `ordernumber` varchar(255) NOT NULL;
EOD;
        $this->addSql($sql);
    }
}
